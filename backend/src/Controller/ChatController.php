<?php

namespace App\Controller;

use App\Entity\Mensaje;
use App\Entity\Usuario;
use App\Repository\MensajeRepository;
use App\Repository\UsuarioRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Psr\Log\LoggerInterface;

#[Route('/api/chat', name: 'api_chat_')]
class ChatController extends AbstractController
{
    public function __construct(
        private MensajeRepository $mensajeRepository,
        private UsuarioRepository $usuarioRepository,
        private EntityManagerInterface $em,
        private LoggerInterface $logger
    ) {}

    #[Route('/my-chats', name: 'my_chats', methods: ['GET'])]
    public function getMyChats(): JsonResponse
    {
        try {
            $user = $this->getUser();
            if (!$user) {
                return $this->json([
                    'success' => false,
                    'message' => 'Usuario no autenticado'
                ], Response::HTTP_UNAUTHORIZED);
            }

            // Obtener todos los mensajes donde el usuario es emisor o receptor
            $mensajes = $this->mensajeRepository->createQueryBuilder('m')
                ->where('m.emisor = :user OR m.receptor = :user')
                ->setParameter('user', $user)
                ->orderBy('m.fecha_envio', 'DESC')
                ->getQuery()
                ->getResult();

            // Agrupar por el otro usuario
            $chats = [];
            foreach ($mensajes as $mensaje) {
                /** @var Mensaje $mensaje */
                $otro = $mensaje->getEmisor()->getId_usuario() === $user->getId_usuario()
                    ? $mensaje->getReceptor()
                    : $mensaje->getEmisor();
                $otroId = $otro->getId_usuario();
                if (!isset($chats[$otroId])) {
                    $chats[$otroId] = [
                        'user' => [
                            'id' => $otro->getId_usuario(),
                            'username' => $otro->getNombreUsuario(),
                            'photo' => $otro->getFotoPerfil(),
                            'profession' => $otro->getProfesion(),
                        ],
                        'lastMessage' => [
                            'content' => $mensaje->getContenido(),
                            'date' => $mensaje->getFechaEnvio()->setTimezone(new \DateTimeZone('Europe/Madrid'))->format('c'),
                        ],
                        // Puedes agregar más info aquí si tienes propuestas, etc.
                    ];
                }
            }

            return $this->json([
                'success' => true,
                'data' => array_values($chats)
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Error en getMyChats: ' . $e->getMessage());
            return $this->json([
                'success' => false,
                'message' => 'Error al obtener los chats',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
} 