<?php

namespace App\Repository;

use App\Entity\Objeto;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Objeto>
 */
class ObjetoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Objeto::class);
    }

    public function findBySearchQuery(string $query = ''): array
    {
        $qb = $this->createQueryBuilder('o');

        if ($query) {
            $qb->andWhere('o.titulo LIKE :query OR o.descripcion LIKE :query')
                ->setParameter('query', '%' . $query . '%');
        }

        return $qb->orderBy('o.fecha_creacion', 'DESC')
            ->getQuery()
            ->getResult();
    }

//    /**
//     * @return Objeto[] Returns an array of Objeto objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('c.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Objeto
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
