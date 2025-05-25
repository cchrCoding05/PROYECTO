<?php

namespace App\Controller;

use App\Entity\Usuario;
use App\Entity\Objeto;
use App\Entity\NegociacionPrecio;
use App\Entity\IntercambioObjeto;
use App\Entity\Valoracion;
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
            
            $intercambio = $this->intercambioObjetoRepository->findOneBy(['objeto' => $product]);
            if (!$intercambio) {
                return $this->json([
                    'success' => true,
                    'data' => []
                ]);
            }
            
            $negociaciones = $this->negociacionPrecioRepository->findBy(['intercambio' => $intercambio], ['fecha_creacion' => 'ASC']);
            $data = array_map(function($neg) {
                $intercambio = $neg->getIntercambio();
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
                        'id' => $intercambio->getObjeto()->getId_objeto(),
                        'name' => $intercambio->getObjeto()->getTitulo()
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

        // Solo validar que el producto no esté intercambiado
        if ($product->getEstado() === Objeto::ESTADO_INTERCAMBIADO) {
            return $this->json([
                'success' => false,
                'message' => 'Este producto ya ha sido intercambiado'
            ], Response::HTTP_BAD_REQUEST);
        }

        $data = json_decode($request->getContent(), true);
        if (!isset($data['price']) || !is_numeric($data['price']) || $data['price'] < 1) {
            return $this->json([
                'success' => false,
                'message' => 'Precio inválido'
            ], Response::HTTP_BAD_REQUEST);
        }
        $price = (int)$data['price'];

        // Validar saldo si es comprador
        if ($user->getId_usuario() !== $product->getUsuario()->getId_usuario() && $user->getCreditos() < $price) {
            return $this->json([
                'success' => false,
                'message' => 'No tienes suficientes puntos para ofertar'
            ], Response::HTTP_BAD_REQUEST);
        }

        // Buscar o crear intercambio
        $intercambio = $this->intercambioObjetoRepository->findOneBy(['objeto' => $product]);
        if (!$intercambio) {
            $intercambio = new IntercambioObjeto();
            $intercambio->setObjeto($product);
            $intercambio->setVendedor($product->getUsuario());
            $intercambio->setComprador($user);
            $intercambio->setPrecioPropuesto($price);
            $this->em->persist($intercambio);
        } else {
            // Solo validar si ya hay una oferta aceptada
            foreach ($intercambio->getNegociaciones() as $neg) {
                if ($neg->isAceptado()) {
                    return $this->json([
                        'success' => false,
                        'message' => 'La negociación ya ha sido aceptada'
                    ], Response::HTTP_BAD_REQUEST);
                }
            }
        }

        // Cambiar estado a reservado si está disponible
        if ($product->getEstado() === Objeto::ESTADO_DISPONIBLE) {
            $product->setEstado(Objeto::ESTADO_RESERVADO);
        }

        // Crear nueva negociación
        $neg = new NegociacionPrecio();
        $neg->setComprador($user);
        $neg->setVendedor($product->getUsuario());
        $neg->setPrecioPropuesto($price);
        $neg->setAceptado(false);
        $neg->setAceptadoVendedor(false);
        $neg->setAceptadoComprador(false);
        
        // Agregar la negociación al intercambio usando el método addNegociacion
        $intercambio->addNegociacion($neg);
        
        $this->em->persist($neg);
        $this->em->flush();

        return $this->json([
            'success' => true,
            'message' => 'Oferta enviada',
            'data' => [
                'id' => $neg->getId_negociacion(),
                'proposedCredits' => $neg->getPrecioPropuesto(),
                'createdAt' => $neg->getFechaCreacion()->format('c'),
                'isActive' => $neg->isAceptado()
            ]
        ]);
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
               ->setParameter('userId', $user->getId_usuario())
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
                        'estado' => $neg->getEstado(),
                        'aceptado_comprador' => $neg->isAceptadoComprador(),
                        'aceptado_vendedor' => $neg->isAceptadoVendedor()
                    ];
                } catch (\Exception $e) {
                    $this->logger->error('Error procesando mensaje', [
                        'negociacion_id' => $neg->getId_negociacion(),
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

    #[Route('/chat/{id}/propose-price', name: 'chat_propose_price', methods: ['POST'])]
    public function proposePriceForChat(Request $request, int $id): JsonResponse
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
            $negociacion->setAceptadoComprador(false);
            $negociacion->setAceptadoVendedor(false);
            $negociacion->setEstado('EN_NEGOCIACION');
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
}