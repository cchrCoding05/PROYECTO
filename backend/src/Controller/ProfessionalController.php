<?php

namespace App\Controller;

use App\Entity\Usuario;
use App\Entity\Valoracion;
use App\Entity\NegociacionPrecio;
use App\Repository\UsuarioRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Psr\Log\LoggerInterface;

#[Route('/api/professionals', name: 'api_professionals_')]
class ProfessionalController extends AbstractController
{
    public function __construct(
        private UsuarioRepository $usuarioRepository,
        private EntityManagerInterface $em,
        private LoggerInterface $logger
    ) {}

    #[Route('/search', name: 'search', methods: ['GET'])]
    public function searchProfessionals(Request $request): JsonResponse
    {
        try {
            $query = $request->query->get('query', '');
            $this->logger->info('Buscando profesionales', ['query' => $query]);

            $currentUser = $this->getUser();
            $currentUserId = $currentUser ? $currentUser->getId_usuario() : null;

            $qb = $this->em->createQueryBuilder();
            $qb->select('u')
               ->from(Usuario::class, 'u')
               ->where('u.profesion IS NOT NULL')
               ->andWhere('u.nombre_usuario != :admin')
               ->setParameter('admin', 'ADMIN');

            if ($currentUserId) {
                $qb->andWhere('u.id_usuario != :currentUserId')
                   ->setParameter('currentUserId', $currentUserId);
            }

            if (!empty($query)) {
                $normalizedQuery = $this->normalizeText($query);
                $qb->andWhere(
                    $qb->expr()->orX(
                        $qb->expr()->like('LOWER(u.nombre_usuario)', ':query'),
                        $qb->expr()->like('LOWER(u.profesion)', ':query')
                    )
                )
                ->setParameter('query', '%' . $normalizedQuery . '%');
            }

            $professionals = $qb->getQuery()->getResult();
            
            $result = [];
            foreach ($professionals as $professional) {
                // Obtener valoraciones del profesional
                $valoraciones = $this->em->getRepository(Valoracion::class)->findBy(['profesional' => $professional]);
                $sumaPuntuaciones = 0;
                foreach ($valoraciones as $valoracion) {
                    $sumaPuntuaciones += $valoracion->getPuntuacion();
                }
                $mediaValoraciones = count($valoraciones) > 0 ? $sumaPuntuaciones / count($valoraciones) : 0;

                $result[] = [
                    'id' => $professional->getId_usuario(),
                    'name' => $professional->getNombreUsuario(),
                    'profession' => $professional->getProfesion(),
                    'email' => $professional->getCorreo(),
                    'description' => $professional->getDescripcion(),
                    'photo' => $professional->getFotoPerfil(),
                    'rating' => $mediaValoraciones,
                    'reviews_count' => count($valoraciones)
                ];
            }

            return $this->json([
                'success' => true,
                'data' => $result
            ]);

        } catch (\Exception $e) {
            $this->logger->error('Error en la búsqueda de profesionales', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return $this->json([
                'success' => false,
                'message' => 'Error al buscar profesionales',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/{id}', name: 'get', methods: ['GET'])]
    public function getProfessional(int $id): JsonResponse
    {
        $professional = $this->usuarioRepository->find($id);
        
        if (!$professional) {
            return $this->json([
                'success' => false,
                'message' => 'Profesional no encontrado'
            ], Response::HTTP_NOT_FOUND);
        }

        return $this->json([
            'success' => true,
            'data' => [
                'id' => $professional->getId_usuario(),
                'name' => $professional->getNombreUsuario(),
                'profession' => $professional->getProfesion(),
                'rating' => $professional->getValoracionPromedio(),
                'reviews_count' => $professional->getValoraciones()->count(),
                'description' => $professional->getDescripcion(),
                'photo' => $professional->getFotoPerfil()
            ]
        ]);
    }

    #[Route('/{id}/ratings', name: 'ratings', methods: ['GET'])]
    public function getProfessionalRatings(int $id): JsonResponse
    {
        $professional = $this->usuarioRepository->find($id);
        
        if (!$professional) {
            return $this->json([
                'success' => false,
                'message' => 'Profesional no encontrado'
            ], Response::HTTP_NOT_FOUND);
        }

        $ratings = $professional->getValoraciones();
        
        $data = array_map(function($valoracion) {
            return [
                'id' => $valoracion->getId_valoracion(),
                'user' => [
                    'id' => $valoracion->getUsuario()->getId_usuario(),
                    'name' => $valoracion->getUsuario()->getNombreUsuario()
                ],
                'rating' => $valoracion->getPuntuacion(),
                'comment' => $valoracion->getComentario(),
                'created_at' => $valoracion->getFechaCreacion()->format('c')
            ];
        }, $ratings->toArray());

        return $this->json([
            'success' => true,
            'data' => $data
        ]);
    }

    #[Route('/{id}/rate', name: 'rate', methods: ['POST'])]
    public function rateProfessional(Request $request, int $id): JsonResponse
    {
        try {
            $this->logger->info('Iniciando rateProfessional', ['id' => $id]);
            
            $user = $this->getUser();
            if (!$user) {
                $this->logger->warning('Usuario no autenticado');
                return $this->json([
                    'success' => false,
                    'message' => 'Usuario no autenticado'
                ], Response::HTTP_UNAUTHORIZED);
            }

            $profesional = $this->usuarioRepository->find($id);
            if (!$profesional) {
                $this->logger->warning('Profesional no encontrado', ['id' => $id]);
                return $this->json([
                    'success' => false,
                    'message' => 'Profesional no encontrado'
                ], Response::HTTP_NOT_FOUND);
            }

            // Verificar si el usuario está intentando valorarse a sí mismo
            if ($user->getId_usuario() === $profesional->getId_usuario()) {
                return $this->json([
                    'success' => false,
                    'message' => 'No puedes valorarte a ti mismo'
                ], Response::HTTP_BAD_REQUEST);
            }

            // Verificar si ya existe una valoración del usuario al profesional
            $valoracionExistente = $this->em->getRepository(Valoracion::class)->findOneBy([
                'usuario' => $user,
                'profesional' => $profesional
            ]);

            if ($valoracionExistente) {
                $this->logger->warning('Ya existe una valoración del usuario al profesional');
                return $this->json([
                    'success' => false,
                    'message' => 'Ya has valorado a este profesional'
                ], Response::HTTP_BAD_REQUEST);
            }

            $data = json_decode($request->getContent(), true);
            $this->logger->info('Datos recibidos', ['data' => $data]);

            if (!isset($data['puntuacion']) || !isset($data['comentario'])) {
                $this->logger->warning('Datos incompletos', ['data' => $data]);
                return $this->json([
                    'success' => false,
                    'message' => 'Se requiere puntuación y comentario'
                ], Response::HTTP_BAD_REQUEST);
            }

            // Validar que la puntuación esté entre 1 y 5
            if (!is_numeric($data['puntuacion']) || $data['puntuacion'] < 1 || $data['puntuacion'] > 5) {
                $this->logger->warning('Puntuación inválida', ['puntuacion' => $data['puntuacion']]);
                return $this->json([
                    'success' => false,
                    'message' => 'La puntuación debe estar entre 1 y 5'
                ], Response::HTTP_BAD_REQUEST);
            }

            // Validar que el comentario no esté vacío
            if (empty(trim($data['comentario']))) {
                $this->logger->warning('Comentario vacío');
                return $this->json([
                    'success' => false,
                    'message' => 'El comentario no puede estar vacío'
                ], Response::HTTP_BAD_REQUEST);
            }

            $valoracion = new Valoracion();
            $valoracion->setUsuario($user);
            $valoracion->setProfesional($profesional);
            $valoracion->setPuntuacion((int)$data['puntuacion']);
            $valoracion->setComentario(trim($data['comentario']));
            $valoracion->setFechaCreacion(new \DateTimeImmutable());

            $this->em->persist($valoracion);
            $this->em->flush();

            // Actualizar la valoración promedio del profesional
            $profesional->actualizarValoracionPromedio();
            $this->em->flush();

            $this->logger->info('Valoración creada exitosamente', [
                'id' => $valoracion->getId_valoracion(),
                'usuario' => $user->getId_usuario(),
                'profesional' => $profesional->getId_usuario()
            ]);

            return $this->json([
                'success' => true,
                'message' => 'Valoración creada exitosamente',
                'data' => [
                    'id' => $valoracion->getId_valoracion(),
                    'puntuacion' => $valoracion->getPuntuacion(),
                    'comentario' => $valoracion->getComentario(),
                    'fecha' => $valoracion->getFechaCreacion()->format('Y-m-d H:i:s')
                ]
            ]);

        } catch (\Exception $e) {
            $this->logger->error('Error al crear valoración', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return $this->json([
                'success' => false,
                'message' => 'Error al crear la valoración',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('', name: 'list', methods: ['GET'])]
    public function getProfessionals(Request $request): JsonResponse
    {
        try {
            $this->logger->info('Iniciando getProfessionals');
            
            $professionals = $this->usuarioRepository->findBy(['tipo_usuario' => 'profesional']);
            $result = [];

            foreach ($professionals as $professional) {
                // Obtener todas las valoraciones del profesional
                $valoraciones = $this->em->getRepository(Valoracion::class)->findBy(['profesional' => $professional]);
                $valoracionesArray = [];
                
                foreach ($valoraciones as $valoracion) {
                    $valoracionesArray[] = [
                        'id' => $valoracion->getId_valoracion(),
                        'puntuacion' => $valoracion->getPuntuacion(),
                        'comentario' => $valoracion->getComentario(),
                        'fecha' => $valoracion->getFechaCreacion()->format('Y-m-d H:i:s'),
                        'usuario' => [
                            'id' => $valoracion->getUsuario()->getId_usuario(),
                            'nombre' => $valoracion->getUsuario()->getNombreUsuario()
                        ]
                    ];
                }

                $result[] = [
                    'id' => $professional->getId_usuario(),
                    'name' => $professional->getNombreUsuario(),
                    'profession' => $professional->getProfesion(),
                    'email' => $professional->getCorreo(),
                    'description' => $professional->getDescripcion(),
                    'photo' => $professional->getFotoPerfil(),
                    'rating' => $professional->getValoracionPromedio(),
                    'reviews_count' => count($valoraciones),
                    'valoraciones' => $valoracionesArray
                ];
            }

            $this->logger->info('Profesionales obtenidos exitosamente', ['count' => count($result)]);
            return $this->json([
                'success' => true,
                'data' => $result
            ]);

        } catch (\Exception $e) {
            $this->logger->error('Error al obtener profesionales', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return $this->json([
                'success' => false,
                'message' => 'Error al obtener profesionales',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/top-rated', name: 'top_rated', methods: ['GET'])]
    public function getTopRatedProfessionals(): JsonResponse
    {
        try {
            error_log('Iniciando getTopRatedProfessionals');
            
            $currentUser = $this->getUser();
            $currentUserId = $currentUser ? $currentUser->getId_usuario() : null;
            
            $qb = $this->usuarioRepository->createQueryBuilder('u')
                ->where('u.nombre_usuario != :admin')
                ->setParameter('admin', 'ADMIN');
            
            if ($currentUserId) {
                $qb->andWhere('u.id_usuario != :currentUserId')
                   ->setParameter('currentUserId', $currentUserId);
            }
            
            $qb->orderBy('u.valoracion_promedio', 'DESC')
               ->setMaxResults(10);
            
            $topUsers = $qb->getQuery()->getResult();
            error_log('Usuarios encontrados: ' . count($topUsers));
            
            $usersData = array_map(function($user) {
                // Obtener valoraciones del profesional
                $valoraciones = $this->em->getRepository(Valoracion::class)->findBy(['profesional' => $user]);
                $sumaPuntuaciones = 0;
                foreach ($valoraciones as $valoracion) {
                    $sumaPuntuaciones += $valoracion->getPuntuacion();
                }
                $mediaValoraciones = count($valoraciones) > 0 ? $sumaPuntuaciones / count($valoraciones) : 0;

                return [
                    'id' => $user->getId_usuario(),
                    'name' => $user->getNombreUsuario(),
                    'profession' => $user->getProfesion(),
                    'email' => $user->getCorreo(),
                    'description' => $user->getDescripcion(),
                    'photo' => $user->getFotoPerfil(),
                    'rating' => $mediaValoraciones,
                    'reviews_count' => count($valoraciones)
                ];
            }, $topUsers);

            return $this->json([
                'success' => true,
                'data' => $usersData
            ]);
        } catch (\Exception $e) {
            error_log('Error en getTopRatedProfessionals: ' . $e->getMessage());
            error_log('Stack trace: ' . $e->getTraceAsString());
            return $this->json([
                'success' => false,
                'message' => 'Error al obtener usuarios mejor valorados',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/{id}/chat', name: 'chat', methods: ['GET'])]
    public function getProfessionalChat(int $id): JsonResponse
    {
        try {
            $this->logger->info('Iniciando getProfessionalChat', ['id' => $id]);
            
            $profesional = $this->usuarioRepository->find($id);
            if (!$profesional) {
                $this->logger->warning('Profesional no encontrado', ['id' => $id]);
                return $this->json([
                    'success' => false,
                    'message' => 'Profesional no encontrado'
                ], Response::HTTP_NOT_FOUND);
            }

            // Obtener todas las valoraciones del profesional
            $valoraciones = $this->em->getRepository(Valoracion::class)->findBy(['profesional' => $profesional]);
            $valoracionesArray = [];
            $sumaPuntuaciones = 0;
            
            foreach ($valoraciones as $valoracion) {
                $valoracionesArray[] = [
                    'id' => $valoracion->getId_valoracion(),
                    'puntuacion' => $valoracion->getPuntuacion(),
                    'comentario' => $valoracion->getComentario(),
                    'fecha' => $valoracion->getFechaCreacion()->format('Y-m-d H:i:s'),
                    'usuario' => [
                        'id' => $valoracion->getUsuario()->getId_usuario(),
                        'name' => $valoracion->getUsuario()->getNombreUsuario()
                    ]
                ];
                $sumaPuntuaciones += $valoracion->getPuntuacion();
            }

            $mediaValoraciones = count($valoraciones) > 0 ? $sumaPuntuaciones / count($valoraciones) : 0;

            $result = [
                'success' => true,
                'data' => [
                    'professional' => [
                        'id' => $profesional->getId_usuario(),
                        'name' => $profesional->getNombreUsuario(),
                        'profession' => $profesional->getProfesion(),
                        'email' => $profesional->getCorreo(),
                        'description' => $profesional->getDescripcion(),
                        'photo' => $profesional->getFotoPerfil(),
                        'rating' => $mediaValoraciones,
                        'reviews_count' => count($valoraciones)
                    ],
                    'valoraciones' => $valoracionesArray
                ]
            ];

            $this->logger->info('Información del profesional obtenida exitosamente', ['id' => $id]);
            return $this->json($result);

        } catch (\Exception $e) {
            $this->logger->error('Error al obtener información del profesional', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return $this->json([
                'success' => false,
                'message' => 'Error al obtener información del profesional',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/{id}/chat/start', name: 'chat_start', methods: ['POST'])]
    public function startProfessionalChat(int $id): JsonResponse
    {
        try {
            $this->logger->info('Iniciando startProfessionalChat', ['id' => $id]);
            
            if (!$this->getUser()) {
                $this->logger->warning('Usuario no autenticado');
                return $this->json(['error' => 'Usuario no autenticado'], 401);
            }

            $profesional = $this->usuarioRepository->find($id);
            if (!$profesional) {
                $this->logger->warning('Profesional no encontrado', ['id' => $id]);
                return $this->json(['error' => 'Profesional no encontrado'], 404);
            }

            // Verificar si ya existe una negociación activa
            $qb = $this->em->createQueryBuilder();
            $qb->select('n')
               ->from(NegociacionPrecio::class, 'n')
               ->where('n.comprador = :comprador')
               ->andWhere('n.vendedor = :vendedor')
               ->andWhere('n.aceptado = false')
               ->setParameter('comprador', $this->getUser())
               ->setParameter('vendedor', $profesional);

            $negociacionExistente = $qb->getQuery()->getOneOrNullResult();

            if ($negociacionExistente) {
                $this->logger->info('Ya existe una negociación activa', [
                    'id' => $negociacionExistente->getId_negociacion()
                ]);
                return $this->json([
                    'message' => 'Ya existe una negociación activa',
                    'negociacion_id' => $negociacionExistente->getId_negociacion()
                ]);
            }

            // Crear nueva negociación directa entre usuario y profesional
            $negociacion = new NegociacionPrecio();
            $negociacion->setComprador($this->getUser());
            $negociacion->setVendedor($profesional);
            $negociacion->setPrecioPropuesto(0);
            $negociacion->setAceptado(false);
            $negociacion->setFechaCreacion(new \DateTimeImmutable());

            $this->em->persist($negociacion);
            $this->em->flush();

            $this->logger->info('Negociación creada exitosamente', [
                'id' => $negociacion->getId_negociacion(),
                'comprador' => $this->getUser()->getId_usuario(),
                'vendedor' => $profesional->getId_usuario()
            ]);

            return $this->json([
                'message' => 'Chat iniciado exitosamente',
                'negociacion_id' => $negociacion->getId_negociacion()
            ]);

        } catch (\Exception $e) {
            $this->logger->error('Error al iniciar chat', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return $this->json(['error' => 'Error al iniciar el chat'], 500);
        }
    }

    private function normalizeText(string $text): string
    {
        return strtolower(trim($text));
    }
}