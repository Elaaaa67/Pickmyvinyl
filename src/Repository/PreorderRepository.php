<?php

namespace App\Repository;

use App\Entity\Preorder;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Preorder>
 */
class PreorderRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Preorder::class);
    }

    public function findByUser($user)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.user = :user')
            ->setParameter('user', $user)
            ->orderBy('p.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findAvailableForPreorder()
    {
        return $this->createQueryBuilder('p')
            ->where('p.status = :status')
            ->setParameter('status', 'pending')
            ->orderBy('p.createdAt', 'ASC')
            ->getQuery()
            ->getResult();
    }
}

