<?php

namespace App\Repository;

use App\Entity\NegociacionServicio;
use App\Entity\Usuario;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class NegociacionServicioRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, NegociacionServicio::class);
    }

    public function findByClienteOrProfesional(Usuario $usuario): array
    {
        return $this->createQueryBuilder('n')
            ->where('n.cliente = :usuario')
            ->orWhere('n.profesional = :usuario')
            ->setParameter('usuario', $usuario)
            ->orderBy('n.fecha_creacion', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findNegociacionesActivas(Usuario $usuario): array
    {
        return $this->createQueryBuilder('n')
            ->where('(n.cliente = :usuario OR n.profesional = :usuario)')
            ->andWhere('n.estado IN (:estados)')
            ->setParameter('usuario', $usuario)
            ->setParameter('estados', [
                NegociacionServicio::ESTADO_EN_NEGOCIACION,
                NegociacionServicio::ESTADO_ACEPTADA
            ])
            ->orderBy('n.fecha_creacion', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findNegociacionesCompletadas(Usuario $usuario): array
    {
        return $this->createQueryBuilder('n')
            ->where('(n.cliente = :usuario OR n.profesional = :usuario)')
            ->andWhere('n.estado = :estado')
            ->setParameter('usuario', $usuario)
            ->setParameter('estado', NegociacionServicio::ESTADO_COMPLETADA)
            ->orderBy('n.fecha_completado', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findNegociacionesRechazadas(Usuario $usuario): array
    {
        return $this->createQueryBuilder('n')
            ->where('(n.cliente = :usuario OR n.profesional = :usuario)')
            ->andWhere('n.estado = :estado')
            ->setParameter('usuario', $usuario)
            ->setParameter('estado', NegociacionServicio::ESTADO_RECHAZADA)
            ->orderBy('n.fecha_creacion', 'DESC')
            ->getQuery()
            ->getResult();
    }
} 