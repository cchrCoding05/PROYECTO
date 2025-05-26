<?php

namespace App\Controller;

use App\Entity\Usuario;
use App\Entity\Valoracion;
use App\Entity\NegociacionPrecio;
use App\Entity\NegociacionServicio;
use App\Entity\Notificacion;
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

    #[Route('/chat/{id}', name: 'get_chat', methods: ['GET'])]
    public function getChat(int $id): JsonResponse
    {
        try {
            $user = $this->getUser();
            if (!$user) {
                return $this->json([
                    'success' => false,
                    'message' => 'Usuario no autenticado'
                ], Response::HTTP_UNAUTHORIZED);
            }

            $profesional = $this->em->getRepository(Profesional::class)->find($id);
            if (!$profesional) {
                return $this->json([
                    'success' => false,
                    'message' => 'Profesional no encontrado'
                ], Response::HTTP_NOT_FOUND);
            }

            // Obtener todas las negociaciones entre el usuario y el profesional
            $qb = $this->em->createQueryBuilder();
            $qb->select('n')
               ->from('App\Entity\NegociacionPrecio', 'n')
               ->where('(n.comprador = :userId AND n.vendedor = :professionalId) OR (n.comprador = :professionalId AND n.vendedor = :userId)')
               ->andWhere('n.tipo = :tipo')
               ->setParameter('userId', $user->getId_usuario())
               ->setParameter('professionalId', $profesional->getId_usuario())
               ->setParameter('tipo', 'servicio')
               ->orderBy('n.fecha_creacion', 'ASC');

            $negotiations = $qb->getQuery()->getResult();

            $formattedNegotiations = [];
            foreach ($negotiations as $negotiation) {
                $formattedNegotiations[] = [
                    'id' => $negotiation->getId_negociacion(),
                    'proposedCredits' => $negotiation->getPrecioPropuesto(),
                    'status' => $negotiation->isAceptado() ? 2 : 1,
                    'date' => $negotiation->getFechaCreacion()->format('Y-m-d H:i:s'),
                    'isSeller' => $negotiation->getVendedor()->getId_usuario() === $user->getId_usuario()
                ];
            }

            return $this->json([
                'success' => true,
                'data' => $formattedNegotiations
            ]);

        } catch (\Exception $e) {
            $this->logger->error('Error al obtener chat: ' . $e->getMessage());
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

            // Crear notificación
            $notificacion = new Notificacion();
            $notificacion->setUsuario($profesional);
            $notificacion->setTipo('mensaje');
            $notificacion->setMensaje($this->getUser()->getNombreUsuario() . ' te ha enviado un mensaje');
            $notificacion->setReferenciaId($negociacion->getId_negociacion());
            $notificacion->setEmisor($this->getUser());
            $notificacion->setLeido(false);
            $notificacion->setFechaCreacion(new \DateTimeImmutable());

            $this->em->persist($notificacion);
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

            // Crear notificación
            $notificacion = new Notificacion();
            $notificacion->setUsuario($profesional);
            $notificacion->setTipo('propuesta_aceptada');
            $notificacion->setMensaje($this->getUser()->getNombreUsuario() . ' ha aceptado tu propuesta');
            $notificacion->setReferenciaId($propuesta->getId_negociacion());
            $notificacion->setEmisor($this->getUser());
            $notificacion->setLeido(false);
            $notificacion->setFechaCreacion(new \DateTimeImmutable());

            $this->em->persist($notificacion);
            $this->em->flush();

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

            // Crear notificación
            $notificacion = new Notificacion();
            $notificacion->setUsuario($profesional);
            $notificacion->setTipo('propuesta_rechazada');
            $notificacion->setMensaje($this->getUser()->getNombreUsuario() . ' ha rechazado tu propuesta');
            $notificacion->setReferenciaId($propuesta->getId_negociacion());
            $notificacion->setEmisor($this->getUser());
            $notificacion->setLeido(false);
            $notificacion->setFechaCreacion(new \DateTimeImmutable());

            $this->em->persist($notificacion);
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
               ->andWhere('n.tipo = :tipo')
               ->setParameter('usuario', $this->getUser())
               ->setParameter('profesional', $profesional)
               ->setParameter('tipo', 'servicio')
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
            $qb->select('DISTINCT n')
               ->from(NegociacionPrecio::class, 'n')
               ->where('n.comprador = :usuario OR n.vendedor = :usuario')
               ->andWhere('n.tipo = :tipo')
               ->setParameter('usuario', $user)
               ->setParameter('tipo', 'servicio')
               ->orderBy('n.fecha_creacion', 'DESC');

            $negociaciones = $qb->getQuery()->getResult();
            $this->logger->info('Negociaciones encontradas', [
                'count' => count($negociaciones)
            ]);

            $chats = [];
            $processedUsers = [];

            foreach ($negociaciones as $negociacion) {
                // Determinar quién es el otro usuario (no el actual)
                $otherUser = $negociacion->getComprador()->getId_usuario() === $user->getId_usuario() 
                    ? $negociacion->getVendedor() 
                    : $negociacion->getComprador();

                // Evitar duplicados
                if (in_array($otherUser->getId_usuario(), $processedUsers)) {
                    continue;
                }
                $processedUsers[] = $otherUser->getId_usuario();

                // Obtener el último mensaje del chat
                $qb = $this->em->createQueryBuilder();
                $qb->select('m')
                   ->from('App\Entity\Mensaje', 'm')
                   ->where('(m.emisor = :userId AND m.receptor = :otherId) OR (m.emisor = :otherId AND m.receptor = :userId)')
                   ->setParameter('userId', $user->getId_usuario())
                   ->setParameter('otherId', $otherUser->getId_usuario())
                   ->orderBy('m.fecha_envio', 'DESC')
                   ->setMaxResults(1);

                $ultimoMensaje = $qb->getQuery()->getOneOrNullResult();

                // Obtener la última propuesta
                $qb = $this->em->createQueryBuilder();
                $qb->select('n')
                   ->from(NegociacionPrecio::class, 'n')
                   ->where('(n.comprador = :userId AND n.vendedor = :otherId) OR (n.comprador = :otherId AND n.vendedor = :userId)')
                   ->andWhere('n.tipo = :tipo')
                   ->setParameter('userId', $user->getId_usuario())
                   ->setParameter('otherId', $otherUser->getId_usuario())
                   ->setParameter('tipo', 'servicio')
                   ->orderBy('n.fecha_creacion', 'DESC')
                   ->setMaxResults(1);

                $ultimaPropuesta = $qb->getQuery()->getOneOrNullResult();

                // Determinar el estado de la negociación
                $status = 'EN_NEGOCIACION';
                $isActive = true;

                if ($ultimaPropuesta) {
                    if ($ultimaPropuesta->getEstado() === 'finalizada') {
                        $status = 'finalizada';
                        $isActive = false;
                    } else if ($ultimaPropuesta->getEstado() === 'aceptada') {
                        $status = 'aceptada';
                        $isActive = true;
                    } else if ($ultimaPropuesta->getEstado() === 'rechazada') {
                        $status = 'rechazada';
                        $isActive = false;
                    }
                }

                $chats[] = [
                    'id' => $otherUser->getId_usuario(),
                    'professional' => [
                        'id' => $otherUser->getId_usuario(),
                        'name' => $otherUser->getNombreUsuario(),
                        'profession' => $otherUser->getProfesion(),
                        'photo' => $otherUser->getFotoPerfil()
                    ],
                    'lastMessage' => $ultimoMensaje ? [
                        'id' => $ultimoMensaje->getId_mensaje(),
                        'content' => $ultimoMensaje->getContenido(),
                        'created_at' => $ultimoMensaje->getFechaEnvio()->format('Y-m-d H:i:s'),
                        'isRead' => $ultimoMensaje->isLeido()
                    ] : null,
                    'lastProposal' => $ultimaPropuesta ? [
                        'id' => $ultimaPropuesta->getId_negociacion(),
                        'price' => $ultimaPropuesta->getPrecioPropuesto(),
                        'created_at' => $ultimaPropuesta->getFechaCreacion()->format('Y-m-d H:i:s'),
                        'estado' => $ultimaPropuesta->getEstado(),
                        'aceptado_comprador' => $ultimaPropuesta->isAceptadoComprador(),
                        'aceptado_vendedor' => $ultimaPropuesta->isAceptadoVendedor()
                    ] : null,
                    'status' => $status,
                    'isActive' => $isActive
                ];
            }

            $this->logger->info('Chats procesados', [
                'count' => count($chats),
                'chats' => $chats
            ]);

            return $this->json([
                'success' => true,
                'data' => $chats
            ]);

        } catch (\Exception $e) {
            $this->logger->error('Error al obtener chats', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return $this->json([
                'success' => false,
                'message' => 'Error al obtener los chats',
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

            // Crear notificación
            $notificacion = new Notificacion();
            $notificacion->setUsuario($propuesta->getVendedor());
            $notificacion->setTipo('propuesta_aceptada');
            $notificacion->setMensaje($this->getUser()->getNombreUsuario() . ' ha aceptado tu propuesta');
            $notificacion->setReferenciaId($propuesta->getId_negociacion());
            $notificacion->setEmisor($this->getUser());
            $notificacion->setLeido(false);
            $notificacion->setFechaCreacion(new \DateTimeImmutable());

            $this->em->persist($notificacion);
            $this->em->flush();

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

    #[Route('/{id}/chat/message', name: 'chat_send_message', methods: ['POST'])]
    public function sendMessage(Request $request, int $id): JsonResponse
    {
        try {
            if (!$this->getUser()) {
                return $this->json([
                    'success' => false,
                    'message' => 'Usuario no autenticado'
                ], Response::HTTP_UNAUTHORIZED);
            }

            $data = json_decode($request->getContent(), true);
            if (!isset($data['message']) || empty($data['message'])) {
                return $this->json([
                    'success' => false,
                    'message' => 'El mensaje no puede estar vacío'
                ], Response::HTTP_BAD_REQUEST);
            }

            $profesional = $this->usuarioRepository->find($id);
            if (!$profesional) {
                return $this->json([
                    'success' => false,
                    'message' => 'Profesional no encontrado'
                ], Response::HTTP_NOT_FOUND);
            }

            // Crear nueva negociación
            $negociacion = new NegociacionServicio();
            $negociacion->setCliente($this->getUser());
            $negociacion->setProfesional($profesional);
            $negociacion->setMensaje($data['message']);
            $negociacion->setEstado(NegociacionServicio::ESTADO_EN_NEGOCIACION);
            $negociacion->setFechaCreacion(new \DateTimeImmutable());

            $this->em->persist($negociacion);
            $this->em->flush();

            // Crear notificación
            $notificacion = new Notificacion();
            $notificacion->setUsuario($profesional);
            $notificacion->setTipo('mensaje');
            $notificacion->setMensaje($this->getUser()->getNombreUsuario() . ' te ha enviado un mensaje');
            $notificacion->setReferenciaId($negociacion->getId_negociacion());
            $notificacion->setEmisor($this->getUser());
            $notificacion->setLeido(false);
            $notificacion->setFechaCreacion(new \DateTimeImmutable());

            $this->em->persist($notificacion);
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

    private function normalizeText(string $text): string
    {
        return strtolower(trim($text));
    }
}