<?php

namespace App\Repository;

use App\Entity\Vinyl;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Vinyl>
 */
class VinylRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Vinyl::class);
    }

    //    /**
    //     * @return Vinyl[] Returns an array of Vinyl objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('v')
    //            ->andWhere('v.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('v.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Vinyl
    //    {
    //        return $this->createQueryBuilder('v')
    //            ->andWhere('v.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }

    /**
     * Retourne les vinyles qui ont au moins une quantité en stock (>0)
     *
     * @return Vinyl[]
     */
    public function findAvailable(): array
    {
        $qb = $this->createQueryBuilder('v')
            ->innerJoin('v.stocks', 's')
            ->andWhere('s.quantity > 0')
            ->groupBy('v.id')
        ;

        return $qb->getQuery()->getResult();
    }

    /**
     * Retourne les vinyles disponibles pour un magasin spécifique
     * (vinyles avec stock > 0 dans ce magasin)
     *
     * @return Vinyl[]
     */
    public function findByStore($store): array
    {
        return $this->createQueryBuilder('v')
            ->innerJoin('v.stocks', 's')
            ->andWhere('s.store = :store')
            ->andWhere('s.quantity > 0')
            ->setParameter('store', $store)
            ->orderBy('v.title', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
