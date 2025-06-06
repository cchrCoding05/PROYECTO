<?php

namespace App\Repository;

use App\Entity\Chat;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Chat>
 */
class ChatRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Chat::class);
    }

    /**
     * @param \App\Entity\Usuario $user
     * @return Chat[]
     */
    public function findByUser($user): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.usuario1 = :user OR c.usuario2 = :user')
            ->setParameter('user', $user)
            ->orderBy('c.id', 'DESC')
            ->getQuery()
            ->getResult();
    }
} 