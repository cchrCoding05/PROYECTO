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
    public function getProfessional(string $id): JsonResponse
    {
        try {
            $professionalId = (int) $id;
            if ($professionalId <= 0) {
                return $this->json([
                    'success' => false,
                    'message' => 'ID de profesional inválido'
                ], Response::HTTP_BAD_REQUEST);
            }

            $professional = $this->usuarioRepository->find($professionalId);
            
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
        } catch (\Exception $e) {
            $this->logger->error('Error al obtener profesional', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return $this->json([
                'success' => false,
                'message' => 'Error al obtener el profesional',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
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

    #[Route('/{id}/chat', name: 'get_professional_chat', methods: ['GET'])]
    public function getChat(int $id): JsonResponse
    {
        try {
            $this->logger->info('Iniciando getChat', ['id' => $id]);
            
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

            // Obtener mensajes del chat
            $qb = $this->em->createQueryBuilder();
            $qb->select('n')
               ->from('App\Entity\NegociacionPrecio', 'n')
               ->where('(n.comprador = :userId AND n.vendedor = :professionalId) OR (n.comprador = :professionalId AND n.vendedor = :userId)')
               ->setParameter('userId', $user->getId_usuario())
               ->setParameter('professionalId', $profesional->getId_usuario())
               ->orderBy('n.fecha_creacion', 'ASC');

            $negociaciones = $qb->getQuery()->getResult();
            $this->logger->info('Negociaciones encontradas', ['count' => count($negociaciones)]);

            $messages = [];
            foreach ($negociaciones as $neg) {
                try {
                    $messages[] = [
                        'id' => $neg->getId_negociacion(),
                        'message' => $neg->getMensaje(),
                        'user_id' => $neg->getComprador()->getId_usuario(),
                        'user_name' => $neg->getComprador()->getNombreUsuario(),
                        'created_at' => $neg->getFechaCreacion()->format('Y-m-d H:i:s'),
                        'isBuyer' => $neg->getComprador()->getId_usuario() === $user->getId_usuario(),
                        'precioPropuesto' => $neg->getPrecioPropuesto(),
                        'accepted' => $neg->isAceptado(),
                        'rejected' => $neg->getEstado() === 'rechazada',
                        'isActive' => !$neg->isAceptado() && $neg->getEstado() !== 'rechazada'
                    ];
                } catch (\Exception $e) {
                    $this->logger->error('Error procesando mensaje', [
                        'negociacion_id' => $neg->getId_negociacion(),
                        'error' => $e->getMessage()
                    ]);
                    continue;
                }
            }

            $this->logger->info('Mensajes procesados', ['count' => count($messages)]);

            return $this->json([
                'success' => true,
                'data' => [
                    'professional' => [
                        'id' => $profesional->getId_usuario(),
                        'name' => $profesional->getNombreUsuario(),
                        'profession' => $profesional->getProfesion(),
                        'profile_image' => $profesional->getFotoPerfil()
                    ],
                    'messages' => $messages
                ]
            ]);

        } catch (\Exception $e) {
            $this->logger->error('Error al obtener chat', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return $this->json([
                'success' => false,
                'message' => 'Error al obtener el chat',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/{id}/chat/start', name: 'chat_start', methods: ['POST'])]
    public function startProfessionalChat(Request $request, int $id): JsonResponse
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

            $data = json_decode($request->getContent(), true);
            if (!isset($data['message'])) {
                return $this->json([
                    'success' => false,
                    'message' => 'El mensaje es requerido'
                ], Response::HTTP_BAD_REQUEST);
            }

            // Crear nueva negociación con mensaje
            $negociacion = new NegociacionPrecio();
            $negociacion->setComprador($this->getUser());
            $negociacion->setVendedor($profesional);
            $negociacion->setMensaje($data['message']);
            $negociacion->setPrecioPropuesto(0);
            $negociacion->setAceptado(false);
            $negociacion->setFechaCreacion(new \DateTimeImmutable());

            $this->em->persist($negociacion);
            $this->em->flush();

            return $this->json([
                'success' => true,
                'message' => 'Mensaje enviado exitosamente',
                'data' => [
                    'id' => $negociacion->getId_negociacion(),
                    'message' => $negociacion->getMensaje(),
                    'created_at' => $negociacion->getFechaCreacion()->format('Y-m-d H:i:s')
                ]
            ]);

        } catch (\Exception $e) {
            $this->logger->error('Error al enviar mensaje', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return $this->json(['error' => 'Error al enviar el mensaje'], 500);
        }
    }

    #[Route('/{id}/chat/accept-proposal/{proposalId}', name: 'chat_accept_proposal', methods: ['POST'])]
    public function acceptProposal(int $id, int $proposalId): JsonResponse
    {
        try {
            $this->logger->info('Iniciando aceptación de propuesta', [
                'proposalId' => $proposalId,
                'userId' => $this->getUser()->getId_usuario()
            ]);

            if (!$this->getUser()) {
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

            $propuesta = $this->em->getRepository(NegociacionPrecio::class)->find($proposalId);
            if (!$propuesta) {
                $this->logger->error('Propuesta no encontrada', ['proposalId' => $proposalId]);
                return $this->json([
                    'success' => false,
                    'message' => 'Propuesta no encontrada'
                ], Response::HTTP_NOT_FOUND);
            }

            $this->logger->info('Propuesta encontrada', [
                'propuestaId' => $propuesta->getId_negociacion(),
                'estadoActual' => $propuesta->getEstado(),
                'aceptadoComprador' => $propuesta->isAceptadoComprador(),
                'aceptadoVendedor' => $propuesta->isAceptadoVendedor()
            ]);

            // Verificar que el usuario es el vendedor o comprador
            if ($this->getUser()->getId_usuario() !== $propuesta->getVendedor()->getId_usuario() && 
                $this->getUser()->getId_usuario() !== $propuesta->getComprador()->getId_usuario()) {
                $this->logger->warning('Usuario no autorizado', [
                    'userId' => $this->getUser()->getId_usuario(),
                    'vendedorId' => $propuesta->getVendedor()->getId_usuario(),
                    'compradorId' => $propuesta->getComprador()->getId_usuario()
                ]);
                return $this->json([
                    'success' => false,
                    'message' => 'No tienes permiso para aceptar esta propuesta'
                ], Response::HTTP_FORBIDDEN);
            }

            // Verificar si la propuesta ya está finalizada o rechazada
            if ($propuesta->getEstado() === 'finalizada' || $propuesta->getEstado() === 'rechazada') {
                $this->logger->warning('Propuesta ya procesada', ['estado' => $propuesta->getEstado()]);
                return $this->json([
                    'success' => false,
                    'message' => 'Esta propuesta ya ha sido procesada'
                ], Response::HTTP_BAD_REQUEST);
            }

            // Si el comprador acepta
            if ($this->getUser()->getId_usuario() === $propuesta->getComprador()->getId_usuario()) {
                $this->logger->info('Comprador acepta la propuesta');
                $propuesta->setAceptadoComprador(true);
            }
            // Si el vendedor acepta
            else {
                $this->logger->info('Vendedor acepta la propuesta');
                $propuesta->setAceptadoVendedor(true);
            }

            // Si ambos han aceptado, finalizar la negociación
            if ($propuesta->isAceptadoComprador() && $propuesta->isAceptadoVendedor()) {
                $this->logger->info('Ambos han aceptado, finalizando negociación');
                $propuesta->setAceptado(true);
                $propuesta->setEstado('finalizada');
                
                // Realizar el traspaso de puntos
                $comprador = $propuesta->getComprador();
                $vendedor = $propuesta->getVendedor();
                $precio = $propuesta->getPrecioPropuesto();

                $this->logger->info('Verificando créditos', [
                    'compradorId' => $comprador->getId_usuario(),
                    'creditosComprador' => $comprador->getCreditos(),
                    'precio' => $precio
                ]);

                if ($comprador->getCreditos() >= $precio) {
                    $comprador->setCreditos($comprador->getCreditos() - $precio);
                    $vendedor->setCreditos($vendedor->getCreditos() + $precio);
                    $this->logger->info('Traspaso de créditos realizado', [
                        'creditosCompradorDespues' => $comprador->getCreditos(),
                        'creditosVendedorDespues' => $vendedor->getCreditos()
                    ]);
                } else {
                    $this->logger->warning('Créditos insuficientes', [
                        'creditosComprador' => $comprador->getCreditos(),
                        'precio' => $precio
                    ]);
                    return $this->json([
                        'success' => false,
                        'message' => 'El comprador no tiene suficientes créditos'
                    ], Response::HTTP_BAD_REQUEST);
                }
            } else {
                // Si solo uno ha aceptado, marcar como aceptada
                $this->logger->info('Aceptación parcial, marcando como aceptada');
                $propuesta->setEstado('aceptada');
            }

            $this->logger->info('Guardando cambios en la base de datos');
            $this->em->flush();
            $this->logger->info('Cambios guardados exitosamente');

            $this->logger->info('Propuesta aceptada exitosamente', [
                'proposalId' => $proposalId,
                'estado' => $propuesta->getEstado(),
                'aceptado_comprador' => $propuesta->isAceptadoComprador(),
                'aceptado_vendedor' => $propuesta->isAceptadoVendedor()
            ]);

            return $this->json([
                'success' => true,
                'message' => 'Propuesta aceptada exitosamente',
                'data' => [
                    'id' => $propuesta->getId_negociacion(),
                    'estado' => $propuesta->getEstado(),
                    'aceptado_comprador' => $propuesta->isAceptadoComprador(),
                    'aceptado_vendedor' => $propuesta->isAceptadoVendedor()
                ]
            ]);

        } catch (\Exception $e) {
            $this->logger->error('Error al aceptar propuesta', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return $this->json([
                'success' => false,
                'message' => 'Error al aceptar la propuesta',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/{id}/chat/reject-proposal/{proposalId}', name: 'chat_reject_proposal', methods: ['POST'])]
    public function rejectProposal(int $id, int $proposalId): JsonResponse
    {
        try {
            if (!$this->getUser()) {
                return $this->json([
                    'success' => false,
                    'message' => 'Usuario no autenticado'
                ], Response::HTTP_UNAUTHORIZED);
            }

            $profesional = $this->usuarioRepository->find($id);
            if (!$profesional) {
                return $this->json([
                    'success' => false,
                    'message' => 'Profesional no encontrado'
                ], Response::HTTP_NOT_FOUND);
            }

            $propuesta = $this->em->getRepository(NegociacionPrecio::class)->find($proposalId);
            if (!$propuesta) {
                return $this->json([
                    'success' => false,
                    'message' => 'Propuesta no encontrada'
                ], Response::HTTP_NOT_FOUND);
            }

            // Verificar que el usuario es el vendedor o comprador
            if ($this->getUser()->getId_usuario() !== $propuesta->getVendedor()->getId_usuario() && 
                $this->getUser()->getId_usuario() !== $propuesta->getComprador()->getId_usuario()) {
                return $this->json([
                    'success' => false,
                    'message' => 'No tienes permiso para rechazar esta propuesta'
                ], Response::HTTP_FORBIDDEN);
            }

            $propuesta->setEstado('rechazada');
            $this->em->flush();

            return $this->json([
                'success' => true,
                'message' => 'Propuesta rechazada exitosamente',
                'data' => [
                    'id' => $propuesta->getId_negociacion(),
                    'estado' => $propuesta->getEstado()
                ]
            ]);

        } catch (\Exception $e) {
            $this->logger->error('Error al rechazar propuesta', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return $this->json([
                'success' => false,
                'message' => 'Error al rechazar la propuesta',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/{id}/chat/propose-price', name: 'chat_propose_price', methods: ['POST'])]
    public function proposePrice(Request $request, int $id): JsonResponse
    {
        try {
            $this->logger->info('Iniciando proposePrice', ['id' => $id]);
            
            if (!$this->getUser()) {
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

            $data = json_decode($request->getContent(), true);
            if (!isset($data['price']) || !is_numeric($data['price']) || $data['price'] < 1) {
                return $this->json([
                    'success' => false,
                    'message' => 'El precio debe ser un número mayor a 0'
                ], Response::HTTP_BAD_REQUEST);
            }

            // Verificar si el usuario tiene suficientes créditos
            if ($this->getUser()->getCreditos() < $data['price']) {
                return $this->json([
                    'success' => false,
                    'message' => 'No tienes suficientes créditos para realizar esta propuesta'
                ], Response::HTTP_BAD_REQUEST);
            }

            // Crear nueva negociación
            $negociacion = new NegociacionPrecio();
            $negociacion->setComprador($this->getUser());
            $negociacion->setVendedor($profesional);
            $negociacion->setPrecioPropuesto($data['price']);
            $negociacion->setAceptado(false);
            $negociacion->setFechaCreacion(new \DateTimeImmutable());

            $this->em->persist($negociacion);
            $this->em->flush();

            $this->logger->info('Propuesta de precio creada exitosamente', [
                'id' => $negociacion->getId_negociacion(),
                'comprador' => $this->getUser()->getId_usuario(),
                'vendedor' => $profesional->getId_usuario(),
                'precio' => $data['price']
            ]);

            return $this->json([
                'success' => true,
                'message' => 'Propuesta de precio enviada exitosamente',
                'data' => [
                    'id' => $negociacion->getId_negociacion(),
                    'price' => $negociacion->getPrecioPropuesto(),
                    'created_at' => $negociacion->getFechaCreacion()->format('Y-m-d H:i:s')
                ]
            ]);

        } catch (\Exception $e) {
            $this->logger->error('Error al proponer precio', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return $this->json([
                'success' => false,
                'message' => 'Error al proponer precio',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/{id}/chat/price-proposals', name: 'chat_price_proposals', methods: ['GET'])]
    public function getPriceProposals(int $id): JsonResponse
    {
        try {
            $this->logger->info('Iniciando getPriceProposals', ['id' => $id]);
            
            if (!$this->getUser()) {
                $this->logger->warning('Usuario no autenticado');
                return new JsonResponse([
                    'success' => false,
                    'message' => 'Usuario no autenticado'
                ], Response::HTTP_UNAUTHORIZED);
            }

            $profesional = $this->usuarioRepository->find($id);
            if (!$profesional) {
                $this->logger->warning('Profesional no encontrado', ['id' => $id]);
                return new JsonResponse([
                    'success' => false,
                    'message' => 'Profesional no encontrado'
                ], Response::HTTP_NOT_FOUND);
            }

            // Obtener todas las propuestas de precio entre el usuario actual y el profesional
            $qb = $this->em->createQueryBuilder();
            $qb->select('n')
               ->from(NegociacionPrecio::class, 'n')
               ->where('(n.comprador = :usuario AND n.vendedor = :profesional) OR (n.comprador = :profesional AND n.vendedor = :usuario)')
               ->setParameter('usuario', $this->getUser())
               ->setParameter('profesional', $profesional)
               ->orderBy('n.fecha_creacion', 'ASC');

            $propuestas = $qb->getQuery()->getResult();
            
            $data = array_map(function($propuesta) {
                $comprador = $propuesta->getComprador();
                $vendedor = $propuesta->getVendedor();
                $isAccepted = $propuesta->isAceptadoComprador() || $propuesta->isAceptadoVendedor();
                $isRejected = $propuesta->getEstado() === 'rechazada';
                $isFinalized = $propuesta->getEstado() === 'finalizada';
                
                return [
                    'id' => $propuesta->getId_negociacion(),
                    'price' => $propuesta->getPrecioPropuesto(),
                    'user_id' => $comprador->getId_usuario(),
                    'user_name' => $comprador->getNombreUsuario(),
                    'created_at' => $propuesta->getFechaCreacion()->format('Y-m-d H:i:s'),
                    'accepted' => $isAccepted,
                    'rejected' => $isRejected,
                    'estado' => $propuesta->getEstado(),
                    'aceptado_comprador' => $propuesta->isAceptadoComprador(),
                    'aceptado_vendedor' => $propuesta->isAceptadoVendedor(),
                    'recipientId' => $vendedor->getId_usuario(),
                    'isActive' => !$isFinalized && !$isRejected
                ];
            }, $propuestas);

            $this->logger->info('Propuestas obtenidas', ['count' => count($data)]);

            return new JsonResponse([
                'success' => true,
                'data' => $data
            ]);

        } catch (\Exception $e) {
            $this->logger->error('Error al obtener propuestas de precio', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return new JsonResponse([
                'success' => false,
                'message' => 'Error al obtener propuestas de precio',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/chats', name: 'chats', methods: ['GET'])]
    public function getUserChats(): JsonResponse
    {
        try {
            $user = $this->getUser();
            if (!$user) {
                $this->logger->warning('Intento de acceso a chats sin autenticación');
                return $this->json([
                    'success' => false,
                    'message' => 'Se requiere autenticación para acceder a este recurso'
                ], Response::HTTP_UNAUTHORIZED);
            }

            $this->logger->info('Obteniendo chats del usuario', [
                'user_id' => $user->getId_usuario(),
                'username' => $user->getNombreUsuario()
            ]);

            // Obtener todas las negociaciones del usuario
            $qb = $this->em->createQueryBuilder();
            $qb->select('n')
               ->from(NegociacionPrecio::class, 'n')
               ->where('n.comprador = :usuario OR n.vendedor = :usuario')
               ->setParameter('usuario', $user)
               ->orderBy('n.fecha_creacion', 'DESC');

            $negociaciones = $qb->getQuery()->getResult();
            $this->logger->info('Negociaciones encontradas', [
                'count' => count($negociaciones)
            ]);

            // Agrupar las negociaciones por profesional
            $chats = [];
            foreach ($negociaciones as $negociacion) {
                try {
                    // Determinar quién es el profesional (el que no es el usuario actual)
                    $profesional = $negociacion->getComprador()->getId_usuario() === $user->getId_usuario() 
                        ? $negociacion->getVendedor() 
                        : $negociacion->getComprador();

                    if (!$profesional) {
                        $this->logger->warning('Profesional no encontrado en negociación', [
                            'negociacion_id' => $negociacion->getId_negociacion()
                        ]);
                        continue;
                    }

                    $profesionalId = $profesional->getId_usuario();
                    
                    if (!isset($chats[$profesionalId])) {
                        $chats[$profesionalId] = [
                            'id' => $profesionalId,
                            'professional' => [
                                'id' => $profesional->getId_usuario(),
                                'name' => $profesional->getNombreUsuario(),
                                'profession' => $profesional->getProfesion(),
                                'photo' => $profesional->getFotoPerfil()
                            ],
                            'lastMessage' => [
                                'id' => $negociacion->getId_negociacion(),
                                'price' => $negociacion->getPrecioPropuesto(),
                                'created_at' => $negociacion->getFechaCreacion()->format('Y-m-d H:i:s'),
                                'estado' => $negociacion->getEstado(),
                                'aceptado' => $negociacion->isAceptado(),
                                'aceptado_vendedor' => $negociacion->isAceptadoVendedor(),
                                'aceptado_comprador' => $negociacion->isAceptadoComprador()
                            ]
                        ];
                    
                        // Actualizar el último mensaje si esta negociación es más reciente
                        $fechaActual = new \DateTime($chats[$profesionalId]['lastMessage']['created_at']);
                        $fechaNueva = $negociacion->getFechaCreacion();
                        
                        if ($fechaNueva > $fechaActual) {
                            $chats[$profesionalId]['lastMessage'] = [
                                'id' => $negociacion->getId_negociacion(),
                                'price' => $negociacion->getPrecioPropuesto(),
                                'created_at' => $negociacion->getFechaCreacion()->format('Y-m-d H:i:s'),
                                'estado' => $negociacion->getEstado(),
                                'aceptado' => $negociacion->isAceptado(),
                                'aceptado_vendedor' => $negociacion->isAceptadoVendedor(),
                                'aceptado_comprador' => $negociacion->isAceptadoComprador()
                            ];
                        }
                    }
                } catch (\Exception $e) {
                    $this->logger->error('Error procesando negociación', [
                        'negociacion_id' => $negociacion->getId_negociacion(),
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                    continue;
                }
            }

            $this->logger->info('Chats procesados exitosamente', [
                'chats_count' => count($chats)
            ]);

            return $this->json([
                'success' => true,
                'data' => array_values($chats)
            ]);

        } catch (\Exception $e) {
            $this->logger->error('Error al obtener chats del usuario', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return $this->json([
                'success' => false,
                'message' => 'Error al obtener chats',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/accept-proposal/{proposalId}', name: 'accept_proposal', methods: ['POST'])]
    public function acceptProposalSimple(int $proposalId): JsonResponse
    {
        try {
            $this->logger->info('Iniciando aceptación de propuesta', ['proposalId' => $proposalId]);

            if (!$this->getUser()) {
                $this->logger->warning('Usuario no autenticado');
                return $this->json([
                    'success' => false,
                    'message' => 'Usuario no autenticado'
                ], Response::HTTP_UNAUTHORIZED);
            }

            $this->logger->info('Usuario autenticado', ['userId' => $this->getUser()->getId_usuario()]);

            $propuesta = $this->em->getRepository(NegociacionPrecio::class)->find($proposalId);
            if (!$propuesta) {
                $this->logger->error('Propuesta no encontrada', ['proposalId' => $proposalId]);
                return $this->json([
                    'success' => false,
                    'message' => 'Propuesta no encontrada'
                ], Response::HTTP_NOT_FOUND);
            }

            $this->logger->info('Propuesta encontrada', [
                'propuestaId' => $propuesta->getId_negociacion(),
                'estadoActual' => $propuesta->getEstado(),
                'aceptadoComprador' => $propuesta->isAceptadoComprador(),
                'aceptadoVendedor' => $propuesta->isAceptadoVendedor()
            ]);

            // Verificar que el usuario es el vendedor o comprador
            if ($this->getUser()->getId_usuario() !== $propuesta->getVendedor()->getId_usuario() && 
                $this->getUser()->getId_usuario() !== $propuesta->getComprador()->getId_usuario()) {
                $this->logger->warning('Usuario no autorizado', [
                    'userId' => $this->getUser()->getId_usuario(),
                    'vendedorId' => $propuesta->getVendedor()->getId_usuario(),
                    'compradorId' => $propuesta->getComprador()->getId_usuario()
                ]);
                return $this->json([
                    'success' => false,
                    'message' => 'No tienes permiso para aceptar esta propuesta'
                ], Response::HTTP_FORBIDDEN);
            }

            // Verificar si la propuesta ya está aceptada o rechazada
            if ($propuesta->getEstado() === 'aceptada' || $propuesta->getEstado() === 'rechazada') {
                $this->logger->warning('Propuesta ya procesada', ['estado' => $propuesta->getEstado()]);
                return $this->json([
                    'success' => false,
                    'message' => 'Esta propuesta ya ha sido procesada'
                ], Response::HTTP_BAD_REQUEST);
            }

            // Si el comprador acepta
            if ($this->getUser()->getId_usuario() === $propuesta->getComprador()->getId_usuario()) {
                $this->logger->info('Comprador acepta la propuesta');
                $propuesta->setAceptadoComprador(true);
            }
            // Si el vendedor acepta
            else {
                $this->logger->info('Vendedor acepta la propuesta');
                $propuesta->setAceptadoVendedor(true);
            }

            // Si ambos han aceptado, finalizar la negociación
            if ($propuesta->isAceptadoComprador() && $propuesta->isAceptadoVendedor()) {
                $this->logger->info('Ambos han aceptado, finalizando negociación');
                $propuesta->setAceptado(true);
                $propuesta->setEstado('finalizada');
                
                // Realizar el traspaso de puntos
                $comprador = $propuesta->getComprador();
                $vendedor = $propuesta->getVendedor();
                $precio = $propuesta->getPrecioPropuesto();

                $this->logger->info('Verificando créditos', [
                    'compradorId' => $comprador->getId_usuario(),
                    'creditosComprador' => $comprador->getCreditos(),
                    'precio' => $precio
                ]);

                if ($comprador->getCreditos() >= $precio) {
                    $comprador->setCreditos($comprador->getCreditos() - $precio);
                    $vendedor->setCreditos($vendedor->getCreditos() + $precio);
                    $this->logger->info('Traspaso de créditos realizado', [
                        'creditosCompradorDespues' => $comprador->getCreditos(),
                        'creditosVendedorDespues' => $vendedor->getCreditos()
                    ]);
                } else {
                    $this->logger->warning('Créditos insuficientes', [
                        'creditosComprador' => $comprador->getCreditos(),
                        'precio' => $precio
                    ]);
                    return $this->json([
                        'success' => false,
                        'message' => 'El comprador no tiene suficientes créditos'
                    ], Response::HTTP_BAD_REQUEST);
                }
            } else {
                // Si solo uno ha aceptado, marcar como aceptada parcialmente
                $this->logger->info('Aceptación parcial, marcando como aceptada');
                $propuesta->setEstado('aceptada');
            }

            $this->logger->info('Guardando cambios en la base de datos');
            $this->em->flush();
            $this->logger->info('Cambios guardados exitosamente');

            $this->logger->info('Propuesta aceptada exitosamente', [
                'proposalId' => $proposalId,
                'estado' => $propuesta->getEstado(),
                'aceptado_comprador' => $propuesta->isAceptadoComprador(),
                'aceptado_vendedor' => $propuesta->isAceptadoVendedor()
            ]);

            return $this->json([
                'success' => true,
                'message' => 'Propuesta aceptada exitosamente',
                'data' => [
                    'id' => $propuesta->getId_negociacion(),
                    'estado' => $propuesta->getEstado(),
                    'aceptado_comprador' => $propuesta->isAceptadoComprador(),
                    'aceptado_vendedor' => $propuesta->isAceptadoVendedor()
                ]
            ]);

        } catch (\Exception $e) {
            $this->logger->error('Error al aceptar propuesta', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return $this->json([
                'success' => false,
                'message' => 'Error al aceptar la propuesta',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    private function normalizeText(string $text): string
    {
        return strtolower(trim($text));
    }
}