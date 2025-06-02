<?php

namespace App\Repository;

use App\Entity\Notificacion;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class NotificacionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Notificacion::class);
    }

    public function findNotificacionesByUsuario(int $usuarioId, int $limit = 20, int $offset = 0): array
    {
        return $this->createQueryBuilder('n')
            ->andWhere('n.usuario = :usuarioId')
            ->setParameter('usuarioId', $usuarioId)
            ->orderBy('n.fechaCreacion', 'DESC')
            ->setMaxResults($limit)
            ->setFirstResult($offset)
            ->getQuery()
            ->getResult();
    }

    public function countNotificacionesNoLeidas(int $usuarioId): int
    {
        return $this->createQueryBuilder('n')
            ->select('COUNT(n.id)')
            ->andWhere('n.usuario = :usuarioId')
            ->andWhere('n.leido = :leido')
            ->setParameter('usuarioId', $usuarioId)
            ->setParameter('leido', false)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function marcarComoLeida(int $notificacionId, int $usuarioId): void
    {
        $this->createQueryBuilder('n')
            ->update()
            ->set('n.leido', ':leido')
            ->where('n.id = :id')
            ->andWhere('n.usuario = :usuarioId')
            ->setParameter('leido', true)
            ->setParameter('id', $notificacionId)
            ->setParameter('usuarioId', $usuarioId)
            ->getQuery()
            ->execute();
    }

    public function marcarTodasComoLeidas(int $usuarioId): void
    {
        $this->createQueryBuilder('n')
            ->update()
            ->set('n.leido', ':leido')
            ->where('n.usuario = :usuarioId')
            ->setParameter('leido', true)
            ->setParameter('usuarioId', $usuarioId)
            ->getQuery()
            ->execute();
    }
} 