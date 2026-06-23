<?php

namespace App\Repository;

use App\Entity\Donation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Donation>
 */
class DonationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Donation::class);
    }

    public function findOneByFedapayTransactionId(string $transactionId): ?Donation
    {
        return $this->findOneBy(['fedapayTransactionId' => $transactionId]);
    }

    public function sumApprovedAmount(): int
    {
        return (int) $this->createQueryBuilder('d')
            ->select('COALESCE(SUM(d.amount), 0)')
            ->where('d.status = :status')
            ->andWhere('d.visible = true')
            ->setParameter('status', 'approved')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @return Donation[]
     */
    public function findApproved(): array
    {
        return $this->createQueryBuilder('d')
            ->where('d.status = :status')
            ->andWhere('d.visible = true')
            ->setParameter('status', 'approved')
            ->orderBy('d.confirmedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function countApproved(): int
    {
        return (int) $this->createQueryBuilder('d')
            ->select('COUNT(d.id)')
            ->where('d.status = :status')
            ->andWhere('d.visible = true')
            ->setParameter('status', 'approved')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @return Donation[]
     */
    public function findAllForAdmin(): array
    {
        return $this->createQueryBuilder('d')
            ->orderBy('d.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
