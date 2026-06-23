<?php

namespace App\Repository;

use App\Entity\BirthdayMessage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<BirthdayMessage>
 */
class BirthdayMessageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BirthdayMessage::class);
    }

    /**
     * @return BirthdayMessage[]
     */
    public function findLatest(int $limit = 12): array
    {
        return $this->createQueryBuilder('m')
            ->where('m.visible = true')
            ->orderBy('m.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function countAll(): int
    {
        return (int) $this->createQueryBuilder('m')
            ->select('COUNT(m.id)')
            ->where('m.visible = true')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @return BirthdayMessage[]
     */
    public function findAllForAdmin(): array
    {
        return $this->createQueryBuilder('m')
            ->orderBy('m.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
