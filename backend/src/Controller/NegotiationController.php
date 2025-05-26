<?php

namespace App\Controller;

use App\Entity\Usuario;
use App\Entity\Objeto;
use App\Entity\NegociacionPrecio;
use App\Entity\IntercambioObjeto;
use App\Entity\Valoracion;
use App\Entity\Mensaje;
use App\Entity\Notificacion;
use App\Repository\UsuarioRepository;
use App\Repository\ObjetoRepository;
use App\Repository\NegociacionPrecioRepository;
use App\Repository\IntercambioObjetoRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Psr\Log\LoggerInterface;

#[Route('/api', name: 'api_')]
class NegotiationController extends AbstractController
{
    public function __construct(
        private UsuarioRepository $usuarioRepository,
        private ObjetoRepository $objetoRepository,
        private EntityManagerInterface $em,
        private NegociacionPrecioRepository $negociacionPrecioRepository,
        private IntercambioObjetoRepository $intercambioObjetoRepository,
        private LoggerInterface $logger
    ) {}

    // Obtener negociaciones de producto
    #[Route('/products/{id}/negotiations', name: 'product_negotiations', methods: ['GET'])]
    public function getNegotiations(int $id): JsonResponse
    {
        try {
            $user = $this->getUser();
            if (!$user) {
                return $this->json([
                    'success' => false,
                    'message' => 'Usuario no autenticado'
                ], Response::HTTP_UNAUTHORIZED);
            }
            
            $product = $this->objetoRepository->find($id);
            if (!$product) {
                return $this->json([
                    'success' => false,
                    'message' => 'Producto no encontrado'
                ], Response::HTTP_NOT_FOUND);
            }
            
            // Buscar las negociaciones relacionadas con el producto específico
            $qb = $this->em->createQueryBuilder();
            $qb->select('n')
               ->from(NegociacionPrecio::class, 'n')
               ->join('n.intercambio', 'i')
               ->where('i.objeto = :objeto')
               ->andWhere('n.tipo = :tipo')
               ->setParameter('objeto', $product)
               ->setParameter('tipo', 'producto')
               ->orderBy('n.fecha_creacion', 'ASC');

            $negociaciones = $qb->getQuery()->getResult();
            
            $data = array_map(function($neg) use ($product) {
                return [
                    'id' => $neg->getId_negociacion(),
                    'user' => [
                        'id' => $neg->getComprador()->getId_usuario(),
                        'username' => $neg->getComprador()->getNombreUsuario(),
                    ],
                    'proposedCredits' => $neg->getPrecioPropuesto(),
                    'accepted' => $neg->isAceptado(),
                    'createdAt' => $neg->getFechaCreacion()->format('c'),
                    'product' => [
                        'id' => $product->getId_objeto(),
                        'name' => $product->getTitulo()
                    ]
                ];
            }, $negociaciones);
            
            return $this->json([
                'success' => true,
                'data' => $data
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Error en getNegotiations: ' . $e->getMessage());
            return $this->json([
                'success' => false,
                'message' => 'Error al obtener las negociaciones',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // Proponer precio
    #[Route('/products/{id}/propose-price', name: 'propose_price', methods: ['POST'])]
    public function proposePrice(Request $request, int $id): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            
            if (!isset($data['price']) || !is_numeric($data['price'])) {
                return $this->json([
                    'success' => false,
                    'message' => 'El precio debe ser un número válido'
                ], Response::HTTP_BAD_REQUEST);
            }

            $producto = $this->objetoRepository->find($id);
            if (!$producto) {
                return $this->json([
                    'success' => false,
                    'message' => 'Producto no encontrado'
                ], Response::HTTP_NOT_FOUND);
            }

            if ($this->getUser()->getCreditos() < $data['price']) {
                return $this->json([
                    'success' => false,
                    'message' => 'No tienes suficientes créditos para realizar esta propuesta'
                ], Response::HTTP_BAD_REQUEST);
            }

            // Crear intercambio
            $intercambio = new IntercambioObjeto();
            $intercambio->setObjeto($producto);
            $intercambio->setVendedor($producto->getUsuario());
            $intercambio->setComprador($this->getUser());
            $intercambio->setPrecioPropuesto($data['price']);
            $intercambio->setFechaSolicitud(new \DateTimeImmutable());

            $this->em->persist($intercambio);

            // Crear nueva negociación
            $negociacion = new NegociacionPrecio();
            $negociacion->setComprador($this->getUser());
            $negociacion->setVendedor($producto->getUsuario());
            $negociacion->setPrecioPropuesto($data['price']);
            $negociacion->setAceptado(false);
            $negociacion->setAceptadoComprador(false);
            $negociacion->setAceptadoVendedor(false);
            $negociacion->setEstado('EN_NEGOCIACION');
            $negociacion->setTipo('producto');
            $negociacion->setFechaCreacion(new \DateTimeImmutable());
            $negociacion->setIntercambio($intercambio);

            $this->em->persist($negociacion);
            $this->em->flush();

            // Crear notificación
            $notificacion = new Notificacion();
            $notificacion->setUsuario($producto->getUsuario());
            $notificacion->setTipo('propuesta_producto');
            $notificacion->setMensaje($this->getUser()->getNombreUsuario() . ' ha propuesto ' . $data['price'] . ' créditos por tu producto ' . $producto->getTitulo());
            $notificacion->setReferenciaId($producto->getId_objeto());
            $notificacion->setEmisor($this->getUser());
            $notificacion->setLeido(false);
            $notificacion->setFechaCreacion(new \DateTimeImmutable());

            $this->em->persist($notificacion);
            $this->em->flush();

            $this->logger->info('Propuesta de precio y notificación creadas exitosamente', [
                'id' => $negociacion->getId_negociacion(),
                'comprador' => $this->getUser()->getId_usuario(),
                'vendedor' => $producto->getUsuario()->getId_usuario(),
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

    // Aceptar negociación
    #[Route('/products/{productId}/negotiations/{negotiationId}/accept', name: 'accept', methods: ['POST'])]
    public function acceptNegotiation(int $productId, int $negotiationId): JsonResponse
    {
        try {
            $user = $this->getUser();
            if (!$user) {
                return $this->json([
                    'success' => false,
                    'message' => 'Usuario no autenticado'
                ], Response::HTTP_UNAUTHORIZED);
            }
            
            $neg = $this->negociacionPrecioRepository->find($negotiationId);
            if (!$neg) {
                return $this->json([
                    'success' => false,
                    'message' => 'Negociación no encontrada'
                ], Response::HTTP_NOT_FOUND);
            }
            
            $intercambio = $neg->getIntercambio();
            $product = $intercambio->getObjeto();

            // Solo vendedor o comprador pueden aceptar
            $isVendedor = $user->getId_usuario() === $intercambio->getVendedor()->getId_usuario();
            $isComprador = $user->getId_usuario() === $intercambio->getComprador()->getId_usuario();
            if (!$isVendedor && !$isComprador) {
                return $this->json([
                    'success' => false,
                    'message' => 'No tienes permiso para aceptar esta negociación'
                ], Response::HTTP_FORBIDDEN);
            }

            // Si ya hay una aceptada, bloquear
            foreach ($intercambio->getNegociaciones() as $n) {
                if ($n->isAceptado()) {
                    return $this->json([
                        'success' => false,
                        'message' => 'Ya hay una negociación aceptada'
                    ], Response::HTTP_BAD_REQUEST);
                }
            }

            // Registrar aceptación
            if ($isVendedor) {
                $neg->setAceptadoVendedor(true);
                $neg->setAceptado(true);
                $neg->setEstado('ACEPTADA');
                // Si el vendedor acepta, cambiar estado del producto a intercambiado
                $product->setEstado(Objeto::ESTADO_INTERCAMBIADO);
            }
            if ($isComprador) {
                $neg->setAceptadoComprador(true);
                $neg->setAceptado(true);
                $neg->setEstado('ACEPTADA');
            }

            // Transferir puntos
            $comprador = $intercambio->getComprador();
            $vendedor = $intercambio->getVendedor();
            $monto = $neg->getPrecioPropuesto();
            
            if ($comprador->getCreditos() >= $monto) {
                $comprador->setCreditos($comprador->getCreditos() - $monto);
                $vendedor->setCreditos($vendedor->getCreditos() + $monto);
                $intercambio->setPrecioPropuesto($neg->getPrecioPropuesto());
                $intercambio->marcarComoCompletado();
            } else {
                return $this->json([
                    'success' => false,
                    'message' => 'El comprador no tiene suficientes créditos'
                ], Response::HTTP_BAD_REQUEST);
            }

            // Forzar la actualización de la entidad
            $this->em->persist($neg);
            $this->em->flush();

            // Log para debug
            $this->logger->info('Negociación aceptada', [
                'id' => $neg->getId_negociacion(),
                'estado' => $neg->getEstado(),
                'aceptado' => $neg->isAceptado(),
                'aceptado_vendedor' => $neg->isAceptadoVendedor(),
                'aceptado_comprador' => $neg->isAceptadoComprador()
            ]);

            // Verificar el estado después de guardar
            $negociacionActualizada = $this->negociacionPrecioRepository->find($negotiationId);
            $this->logger->info('Estado después de guardar', [
                'id' => $negociacionActualizada->getId_negociacion(),
                'estado' => $negociacionActualizada->getEstado(),
                'aceptado' => $negociacionActualizada->isAceptado()
            ]);

            return $this->json([
                'success' => true,
                'message' => 'Negociación aceptada',
                'data' => [
                    'id' => $neg->getId_negociacion(),
                    'estado' => $neg->getEstado(),
                    'aceptado' => $neg->isAceptado(),
                    'aceptado_vendedor' => $neg->isAceptadoVendedor(),
                    'aceptado_comprador' => $neg->isAceptadoComprador(),
                    'transferCompleted' => true
                ]
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Error al aceptar negociación: ' . $e->getMessage());
            return $this->json([
                'success' => false,
                'message' => 'Error al aceptar la negociación',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // Rechazar negociación
    #[Route('/products/{productId}/negotiations/{negotiationId}/reject', name: 'reject', methods: ['POST'])]
    public function rejectNegotiation(int $productId, int $negotiationId): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->json([
                'success' => false,
                'message' => 'Usuario no autenticado'
            ], Response::HTTP_UNAUTHORIZED);
        }
        
        $neg = $this->negociacionPrecioRepository->find($negotiationId);
        if (!$neg) {
            return $this->json([
                'success' => false,
                'message' => 'Negociación no encontrada'
            ], Response::HTTP_NOT_FOUND);
        }
        
        $intercambio = $neg->getIntercambio();
        $product = $intercambio->getObjeto();

        // Solo vendedor o comprador pueden rechazar
        $isVendedor = $user->getId_usuario() === $intercambio->getVendedor()->getId_usuario();
        $isComprador = $user->getId_usuario() === $intercambio->getComprador()->getId_usuario();
        if (!$isVendedor && !$isComprador) {
            return $this->json([
                'success' => false,
                'message' => 'No tienes permiso para rechazar esta negociación'
            ], Response::HTTP_FORBIDDEN);
        }
        
        // Si ya hay una aceptada, bloquear
        foreach ($intercambio->getNegociaciones() as $n) {
            if ($n->isAceptado()) {
                return $this->json([
                    'success' => false,
                    'message' => 'Ya hay una negociación aceptada'
                ], Response::HTTP_BAD_REQUEST);
            }
        }
        
        // Eliminar la negociación rechazada
        $this->em->remove($neg);
        $this->em->flush();
        
        // Si no quedan negociaciones pendientes, volver a disponible
        $negociacionesRestantes = $this->negociacionPrecioRepository->findBy(['intercambio' => $intercambio]);
        if (count($negociacionesRestantes) === 0) {
            $product->setEstado(Objeto::ESTADO_DISPONIBLE);
            $this->em->flush();
        }
        
        return $this->json([
            'success' => true,
            'message' => 'Negociación rechazada'
        ]);
    }

    // Obtener negociaciones del usuario
    #[Route('/negotiations/my-negotiations', name: 'my_negotiations', methods: ['GET'])]
    public function getMyNegotiations(): JsonResponse
    {
        try {
            $user = $this->getUser();
            if (!$user) {
                return $this->json([
                    'success' => false,
                    'message' => 'Usuario no autenticado'
                ], Response::HTTP_UNAUTHORIZED);
            }

            // Obtener todas las negociaciones donde el usuario es comprador o vendedor
            $qb = $this->em->createQueryBuilder();
            $qb->select('n')
               ->from('App\Entity\NegociacionPrecio', 'n')
               ->where('n.comprador = :userId OR n.vendedor = :userId')
               ->andWhere('n.tipo = :tipo')
               ->setParameter('userId', $user->getId_usuario())
               ->setParameter('tipo', 'producto')
               ->orderBy('n.fecha_creacion', 'DESC');

            $negotiations = $qb->getQuery()->getResult();

            $formattedNegotiations = [];
            foreach ($negotiations as $negotiation) {
                $intercambio = $negotiation->getIntercambio();
                $objeto = $intercambio ? $intercambio->getObjeto() : null;
                
                // Debug de los estados
                $this->logger->info('Estado de negociación', [
                    'id' => $negotiation->getId_negociacion(),
                    'estado' => $negotiation->getEstado(),
                    'aceptado' => $negotiation->isAceptado(),
                    'aceptado_vendedor' => $negotiation->isAceptadoVendedor(),
                    'aceptado_comprador' => $negotiation->isAceptadoComprador(),
                    'estado_objeto' => $objeto ? $objeto->getEstado() : null
                ]);

                // Determinar el estado de la negociación
                $status = 1; // Por defecto: activa
                
                // Si la negociación tiene estado explícito, usarlo
                if ($negotiation->getEstado() === 'ACEPTADA') {
                    $status = 2; // Finalizada (aceptada)
                } else if ($negotiation->getEstado() === 'RECHAZADA') {
                    $status = 3; // Finalizada (rechazada)
                } else if ($negotiation->isAceptado()) {
                    $status = 2; // Finalizada (aceptada)
                } else if ($objeto && $objeto->getEstado() === Objeto::ESTADO_INTERCAMBIADO) {
                    $status = 2; // Finalizada (aceptada)
                }

                // Determinar si está activa
                $isActive = $status === 1;
                
                $this->logger->info('Estado final', [
                    'id' => $negotiation->getId_negociacion(),
                    'status' => $status,
                    'isActive' => $isActive,
                    'estado_negociacion' => $negotiation->getEstado()
                ]);
                
                $formattedNegotiations[] = [
                    'id' => $negotiation->getId_negociacion(),
                    'product' => [
                        'id' => $objeto ? $objeto->getId_objeto() : null,
                        'name' => $objeto ? $objeto->getTitulo() : 'Producto no disponible',
                        'image' => $objeto ? $objeto->getImagen() : null,
                        'credits' => $objeto ? $objeto->getCreditos() : 0,
                        'state' => $objeto ? $objeto->getEstado() : null
                    ],
                    'seller' => [
                        'id' => $negotiation->getVendedor()->getId_usuario(),
                        'name' => $negotiation->getVendedor()->getNombreUsuario()
                    ],
                    'buyer' => [
                        'id' => $negotiation->getComprador()->getId_usuario(),
                        'name' => $negotiation->getComprador()->getNombreUsuario()
                    ],
                    'proposedCredits' => $negotiation->getPrecioPropuesto(),
                    'status' => $status,
                    'estado' => $negotiation->getEstado(),
                    'date' => $negotiation->getFechaCreacion()->format('Y-m-d H:i:s'),
                    'isSeller' => $negotiation->getVendedor()->getId_usuario() === $user->getId_usuario(),
                    'isActive' => $isActive
                ];
            }

            return $this->json([
                'success' => true,
                'data' => $formattedNegotiations
            ]);

        } catch (\Exception $e) {
            $this->logger->error('Error al obtener negociaciones: ' . $e->getMessage());
            $this->logger->error('Stack trace: ' . $e->getTraceAsString());
            return $this->json([
                'success' => false,
                'message' => 'Error al obtener las negociaciones',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/chat/{id}', name: 'get_chat', methods: ['GET'])]
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
            $qb->select('m')
               ->from('App\Entity\Mensaje', 'm')
               ->where('(m.emisor = :userId AND m.receptor = :professionalId) OR (m.emisor = :professionalId AND m.receptor = :userId)')
               ->setParameter('userId', $user->getId_usuario())
               ->setParameter('professionalId', $profesional->getId_usuario())
               ->orderBy('m.fecha_envio', 'ASC');

            $mensajes = $qb->getQuery()->getResult();
            $this->logger->info('Mensajes encontrados', ['count' => count($mensajes)]);

            $messages = [];
            foreach ($mensajes as $mensaje) {
                try {
                    $messages[] = [
                        'id' => $mensaje->getId_mensaje(),
                        'message' => $mensaje->getContenido(),
                        'user_id' => $mensaje->getEmisor()->getId_usuario(),
                        'user_name' => $mensaje->getEmisor()->getNombreUsuario(),
                        'created_at' => $mensaje->getFechaEnvio()->format('Y-m-d H:i:s'),
                        'isBuyer' => $mensaje->getEmisor()->getId_usuario() === $user->getId_usuario(),
                        'leido' => $mensaje->isLeido()
                    ];
                } catch (\Exception $e) {
                    $this->logger->error('Error procesando mensaje', [
                        'mensaje_id' => $mensaje->getId_mensaje(),
                        'error' => $e->getMessage()
                    ]);
                    continue;
                }
            }

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

    #[Route('/chat/{id}/accept-proposal/{proposalId}', name: 'chat_accept_proposal', methods: ['POST'])]
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

            // Si el vendedor acepta, finalizar inmediatamente
            if ($this->getUser()->getId_usuario() === $propuesta->getVendedor()->getId_usuario()) {
                $this->logger->info('Vendedor acepta la propuesta - finalizando inmediatamente');
                $propuesta->setAceptadoVendedor(true);
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
            }
            // Si el comprador acepta, solo marcar como aceptado
            else {
                $this->logger->info('Comprador acepta la propuesta - marcando como aceptada');
                $propuesta->setAceptadoComprador(true);
                $propuesta->setEstado('aceptada');
            }

            $this->logger->info('Guardando cambios en la base de datos');
            $this->em->flush();
            $this->logger->info('Cambios guardados exitosamente');

            $this->logger->info('Propuesta procesada exitosamente', [
                'proposalId' => $proposalId,
                'estado' => $propuesta->getEstado(),
                'aceptado_comprador' => $propuesta->isAceptadoComprador(),
                'aceptado_vendedor' => $propuesta->isAceptadoVendedor()
            ]);

            return $this->json([
                'success' => true,
                'message' => 'Propuesta procesada exitosamente',
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

    #[Route('/chat/{id}/reject-proposal/{proposalId}', name: 'chat_reject_proposal', methods: ['POST'])]
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

    #[Route('/chat/{id}/propose-price', name: 'propose_price_for_chat', methods: ['POST'])]
    public function proposePriceForChat(Request $request, int $id): JsonResponse
    {
        try {
            $this->logger->info('Iniciando proposePriceForChat', ['id' => $id]);
            
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

            // Verificar que el usuario no sea el mismo que el profesional
            if ($this->getUser()->getId_usuario() === $profesional->getId_usuario()) {
                $this->logger->warning('Intento de propuesta a sí mismo', [
                    'userId' => $this->getUser()->getId_usuario()
                ]);
                return $this->json([
                    'success' => false,
                    'message' => 'No puedes proponer un precio a ti mismo'
                ], Response::HTTP_BAD_REQUEST);
            }

            $data = json_decode($request->getContent(), true);
            $this->logger->info('Datos recibidos', ['data' => $data]);

            if (!isset($data['price']) || !is_numeric($data['price']) || $data['price'] <= 0) {
                $this->logger->warning('Precio inválido', ['price' => $data['price'] ?? null]);
                return $this->json([
                    'success' => false,
                    'message' => 'El precio propuesto debe ser un número mayor que 0'
                ], Response::HTTP_BAD_REQUEST);
            }

            // Verificar si el usuario tiene suficientes créditos
            if ($this->getUser()->getCreditos() < $data['price']) {
                $this->logger->warning('Créditos insuficientes', [
                    'userId' => $this->getUser()->getId_usuario(),
                    'creditos' => $this->getUser()->getCreditos(),
                    'precio' => $data['price']
                ]);
                return $this->json([
                    'success' => false,
                    'message' => 'No tienes suficientes créditos para realizar esta propuesta'
                ], Response::HTTP_BAD_REQUEST);
            }

            // Verificar si ya existe una propuesta activa
            $qb = $this->em->createQueryBuilder();
            $qb->select('n')
               ->from(NegociacionPrecio::class, 'n')
               ->where('(n.comprador = :usuario AND n.vendedor = :profesional) OR (n.comprador = :profesional AND n.vendedor = :usuario)')
               ->andWhere('n.tipo = :tipo')
               ->andWhere('n.estado IN (:estados)')
               ->setParameter('usuario', $this->getUser())
               ->setParameter('profesional', $profesional)
               ->setParameter('tipo', 'servicio')
               ->setParameter('estados', ['pendiente', 'aceptada']);

            $propuestaExistente = $qb->getQuery()->getOneOrNullResult();
            if ($propuestaExistente) {
                $this->logger->warning('Propuesta activa existente', [
                    'propuestaId' => $propuestaExistente->getId_negociacion()
                ]);
                return $this->json([
                    'success' => false,
                    'message' => 'Ya existe una propuesta activa entre ambos usuarios'
                ], Response::HTTP_BAD_REQUEST);
            }

            try {
                // Crear la negociación
                $negociacion = new NegociacionPrecio();
                $negociacion->setComprador($this->getUser());
                $negociacion->setVendedor($profesional);
                $negociacion->setPrecioPropuesto($data['price']);
                $negociacion->setEstado('pendiente');
                $negociacion->setTipo('servicio');
                $negociacion->setAceptado(false);
                $negociacion->setAceptadoComprador(false);
                $negociacion->setAceptadoVendedor(false);
                $negociacion->setFechaCreacion(new \DateTimeImmutable());

                $this->em->persist($negociacion);
                $this->em->flush(); // Flush para obtener el ID de la negociación

                // Crear notificación para el profesional
                $notificacion = new Notificacion();
                $notificacion->setUsuario($profesional);
                $notificacion->setTipo('propuesta_servicio');
                $notificacion->setMensaje($this->getUser()->getNombreUsuario() . ' te ha propuesto un precio de ' . $data['price'] . ' créditos por tu servicio');
                $notificacion->setLeido(false);
                $notificacion->setFechaCreacion(new \DateTimeImmutable());
                $notificacion->setReferenciaId($negociacion->getId_negociacion());
                $notificacion->setEmisor($this->getUser());

                $this->em->persist($notificacion);
                $this->em->flush();

                $this->logger->info('Propuesta creada exitosamente', [
                    'negociacionId' => $negociacion->getId_negociacion(),
                    'notificacionId' => $notificacion->getId()
                ]);

                return $this->json([
                    'success' => true,
                    'message' => 'Propuesta enviada correctamente',
                    'data' => [
                        'id' => $negociacion->getId_negociacion(),
                        'precio' => $negociacion->getPrecioPropuesto(),
                        'estado' => $negociacion->getEstado()
                    ]
                ]);
            } catch (\Exception $e) {
                $this->logger->error('Error al persistir la propuesta', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                return $this->json([
                    'success' => false,
                    'message' => 'Error al guardar la propuesta: ' . $e->getMessage()
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }

        } catch (\Exception $e) {
            $this->logger->error('Error al proponer precio', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return $this->json([
                'success' => false,
                'message' => 'Error al procesar la propuesta: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/chat/{id}/price-proposals', name: 'chat_price_proposals', methods: ['GET'])]
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

    #[Route('/chat/{id}/message', name: 'chat_send_message', methods: ['POST'])]
    public function sendMessage(Request $request, int $id): JsonResponse
    {
        try {
            $this->logger->info('Iniciando envío de mensaje', ['id' => $id]);
            
            if (!$this->getUser()) {
                $this->logger->warning('Usuario no autenticado');
                return $this->json([
                    'success' => false,
                    'message' => 'Usuario no autenticado'
                ], Response::HTTP_UNAUTHORIZED);
            }

            $data = json_decode($request->getContent(), true);
            $this->logger->info('Datos recibidos:', $data);

            if (!isset($data['contenido']) || empty($data['contenido'])) {
                return $this->json([
                    'success' => false,
                    'message' => 'El mensaje no puede estar vacío'
                ], Response::HTTP_BAD_REQUEST);
            }

            $receptor = $this->usuarioRepository->find($id);
            if (!$receptor) {
                $this->logger->warning('Receptor no encontrado', ['id' => $id]);
                return $this->json([
                    'success' => false,
                    'message' => 'Usuario receptor no encontrado'
                ], Response::HTTP_NOT_FOUND);
            }

            // Crear nuevo mensaje
            $mensaje = new Mensaje();
            $mensaje->setEmisor($this->getUser());
            $mensaje->setReceptor($receptor);
            $mensaje->setContenido($data['contenido']);
            $mensaje->setLeido(false);
            $mensaje->setFechaEnvio(new \DateTimeImmutable());

            $this->em->persist($mensaje);
            $this->em->flush();

            // Crear notificación
            $notificacion = new Notificacion();
            $notificacion->setUsuario($receptor);
            $notificacion->setTipo('mensaje');
            $notificacion->setMensaje($this->getUser()->getNombreUsuario() . ' te ha enviado un mensaje');
            $notificacion->setReferenciaId($mensaje->getId_mensaje());
            $notificacion->setEmisor($this->getUser());
            $notificacion->setLeido(false);
            $notificacion->setFechaCreacion(new \DateTimeImmutable());

            $this->em->persist($notificacion);
            $this->em->flush();

            $this->logger->info('Mensaje y notificación guardados exitosamente', [
                'id' => $mensaje->getId_mensaje(),
                'emisor' => $mensaje->getEmisor()->getId_usuario(),
                'receptor' => $mensaje->getReceptor()->getId_usuario()
            ]);

            return $this->json([
                'success' => true,
                'message' => 'Mensaje enviado exitosamente',
                'data' => [
                    'id' => $mensaje->getId_mensaje(),
                    'contenido' => $mensaje->getContenido(),
                    'fecha_envio' => $mensaje->getFechaEnvio()->format('Y-m-d H:i:s'),
                    'emisor' => [
                        'id' => $mensaje->getEmisor()->getId_usuario(),
                        'nombre' => $mensaje->getEmisor()->getNombreUsuario()
                    ],
                    'receptor' => [
                        'id' => $mensaje->getReceptor()->getId_usuario(),
                        'nombre' => $mensaje->getReceptor()->getNombreUsuario()
                    ],
                    'leido' => $mensaje->isLeido()
                ]
            ]);

        } catch (\Exception $e) {
            $this->logger->error('Error al enviar mensaje', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return $this->json([
                'success' => false,
                'message' => 'Error al enviar el mensaje',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/chat/{id}/read', name: 'chat_mark_read', methods: ['POST'])]
    public function markMessagesAsRead(int $id): JsonResponse
    {
        try {
            if (!$this->getUser()) {
                return $this->json([
                    'success' => false,
                    'message' => 'Usuario no autenticado'
                ], Response::HTTP_UNAUTHORIZED);
            }

            // Marcar todos los mensajes no leídos como leídos
            $qb = $this->em->createQueryBuilder();
            $qb->update('App\Entity\Mensaje', 'm')
               ->set('m.leido', true)
               ->where('m.receptor = :userId')
               ->andWhere('m.emisor = :otherId')
               ->andWhere('m.leido = false')
               ->setParameter('userId', $this->getUser()->getId_usuario())
               ->setParameter('otherId', $id);

            $qb->getQuery()->execute();

            return $this->json([
                'success' => true,
                'message' => 'Mensajes marcados como leídos'
            ]);

        } catch (\Exception $e) {
            $this->logger->error('Error al marcar mensajes como leídos', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return $this->json([
                'success' => false,
                'message' => 'Error al marcar mensajes como leídos',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}