<?php

namespace App\Repository;

use App\Entity\BaobabReservation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<BaobabReservation>
 */
class BaobabReservationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BaobabReservation::class);
    }

    /**
     * @return BaobabReservation[]
     */
    public function findAllForAdmin(): array
    {
        return $this->createQueryBuilder('r')
            ->orderBy('r.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function countAll(): int
    {
        return (int) $this->createQueryBuilder('r')
            ->select('COUNT(r.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function sumPassengers(): int
    {
        return (int) $this->createQueryBuilder('r')
            ->select('COALESCE(SUM(r.passengers), 0)')
            ->getQuery()
            ->getSingleScalarResult();
    }
}
