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
            // Si el vendedor acepta, cambiar estado del producto a intercambiado
            $product->setEstado(Objeto::ESTADO_INTERCAMBIADO);
        }
        if ($isComprador) {
            $neg->setAceptadoComprador(true);
        }

        // Si ambos han aceptado, completar el intercambio
        if ($neg->isAceptadoVendedor() && $neg->isAceptadoComprador()) {
            $neg->setAceptado(true);
            $intercambio->setPrecioPropuesto($neg->getPrecioPropuesto());
            $intercambio->marcarComoCompletado();
            
            // Transferir puntos
            $comprador = $intercambio->getComprador();
            $vendedor = $intercambio->getVendedor();
            $monto = $neg->getPrecioPropuesto();
            
            if ($comprador->getCreditos() >= $monto) {
                $comprador->setCreditos($comprador->getCreditos() - $monto);
                $vendedor->setCreditos($vendedor->getCreditos() + $monto);
            } else {
                return $this->json([
                    'success' => false,
                    'message' => 'El comprador no tiene suficientes créditos'
                ], Response::HTTP_BAD_REQUEST);
            }
        }

        $this->em->flush();
        return $this->json([
            'success' => true,
            'message' => 'Negociación aceptada'
        ]);
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
                    'aceptado' => $negotiation->isAceptado(),
                    'aceptado_vendedor' => $negotiation->isAceptadoVendedor(),
                    'aceptado_comprador' => $negotiation->isAceptadoComprador(),
                    'estado_objeto' => $objeto ? $objeto->getEstado() : null
                ]);

                // Determinar el estado de la negociación
                $status = 1; // Por defecto: activa
                
                // Si el objeto está reservado, la negociación está activa
                if ($objeto && $objeto->getEstado() === Objeto::ESTADO_RESERVADO) {
                    $status = 1; // Activa
                }
                // Si el vendedor ha aceptado, la negociación está finalizada
                else if ($negotiation->isAceptadoVendedor()) {
                    $status = 2; // Finalizada
                }
                // Si el comprador o vendedor han rechazado, la negociación está finalizada
                else if (!$negotiation->isAceptadoVendedor() && !$negotiation->isAceptadoComprador()) {
                    $status = 3; // Finalizada (rechazada)
                }

                // Determinar si está activa
                $isActive = $status === 1;
                
                $this->logger->info('Estado final', [
                    'id' => $negotiation->getId_negociacion(),
                    'status' => $status,
                    'isActive' => $isActive
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
}