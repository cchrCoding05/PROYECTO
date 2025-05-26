<?php

namespace App\Controller;

use App\Entity\Notificacion;
use App\Repository\NotificacionRepository;
use App\Repository\UsuarioRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/notifications')]
#[IsGranted('ROLE_USER')]
class NotificacionController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private NotificacionRepository $notificacionRepository,
        private UsuarioRepository $usuarioRepository
    ) {}

    #[Route('', name: 'api_notifications_get', methods: ['GET'])]
    public function getNotificaciones(Request $request): JsonResponse
    {
        $usuario = $this->getUser();
        $limit = $request->query->getInt('limit', 20);
        $offset = $request->query->getInt('offset', 0);

        $notificaciones = $this->notificacionRepository->findNotificacionesByUsuario(
            $usuario->getId_usuario(),
            $limit,
            $offset
        );

        $notificacionesArray = array_map(function (Notificacion $notificacion) {
            return [
                'id' => $notificacion->getId(),
                'tipo' => $notificacion->getTipo(),
                'mensaje' => $notificacion->getMensaje(),
                'leido' => $notificacion->isLeido(),
                'fecha_creacion' => $notificacion->getFechaCreacion()->format('Y-m-d H:i:s'),
                'referencia_id' => $notificacion->getReferenciaId(),
                'emisor' => [
                    'id' => $notificacion->getEmisor()->getId_usuario(),
                    'username' => $notificacion->getEmisor()->getNombreUsuario()
                ]
            ];
        }, $notificaciones);

        return $this->json([
            'success' => true,
            'data' => $notificacionesArray
        ]);
    }

    #[Route('/unread/count', name: 'api_notifications_unread_count', methods: ['GET'])]
    public function getUnreadCount(): JsonResponse
    {
        $usuario = $this->getUser();
        $count = $this->notificacionRepository->countNotificacionesNoLeidas($usuario->getId_usuario());

        return $this->json([
            'success' => true,
            'data' => [
                'count' => $count
            ]
        ]);
    }

    #[Route('/{id}/read', name: 'api_notifications_mark_read', methods: ['PUT'])]
    public function marcarComoLeida(int $id): JsonResponse
    {
        $usuario = $this->getUser();
        $this->notificacionRepository->marcarComoLeida($id, $usuario->getId_usuario());

        return $this->json([
            'success' => true,
            'message' => 'Notificación marcada como leída'
        ]);
    }

    #[Route('/read-all', name: 'api_notifications_mark_all_read', methods: ['PUT'])]
    public function marcarTodasComoLeidas(): JsonResponse
    {
        $usuario = $this->getUser();
        $this->notificacionRepository->marcarTodasComoLeidas($usuario->getId_usuario());

        return $this->json([
            'success' => true,
            'message' => 'Todas las notificaciones marcadas como leídas'
        ]);
    }

    #[Route('/create', name: 'api_notifications_create', methods: ['POST'])]
    public function crearNotificacion(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        if (!isset($data['usuario_id'], $data['tipo'], $data['mensaje'], $data['referencia_id'], $data['emisor_id'])) {
            return $this->json([
                'success' => false,
                'message' => 'Faltan datos obligatorios'
            ], 400);
        }

        $usuario = $this->usuarioRepository->find($data['usuario_id']);
        $emisor = $this->usuarioRepository->find($data['emisor_id']);

        if (!$usuario || !$emisor) {
            return $this->json([
                'success' => false,
                'message' => 'Usuario o emisor no encontrado'
            ], 404);
        }

        $notificacion = new Notificacion();
        $notificacion->setUsuario($usuario);
        $notificacion->setTipo($data['tipo']);
        $notificacion->setMensaje($data['mensaje']);
        $notificacion->setReferenciaId($data['referencia_id']);
        $notificacion->setEmisor($emisor);

        $this->entityManager->persist($notificacion);
        $this->entityManager->flush();

        return $this->json([
            'success' => true,
            'message' => 'Notificación creada correctamente',
            'data' => [
                'id' => $notificacion->getId()
            ]
        ]);
    }
} 