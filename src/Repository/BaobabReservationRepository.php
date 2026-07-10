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

    /**
     * @return BaobabReservation[]
     */
    public function findFiltered(?string $departureCity, ?string $timeSlot, ?string $search): array
    {
        $qb = $this->createQueryBuilder('r')->orderBy('r.createdAt', 'DESC');

        if ($departureCity) {
            $qb->andWhere('r.departureCity = :departureCity')->setParameter('departureCity', $departureCity);
        }
        if ($timeSlot) {
            $qb->andWhere('r.timeSlot = :timeSlot')->setParameter('timeSlot', $timeSlot);
        }
        if ($search) {
            $qb->andWhere('r.fullName LIKE :search OR r.phone LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * @return string[]
     */
    public function findDistinctDepartureCities(): array
    {
        return array_column(
            $this->createQueryBuilder('r')
                ->select('DISTINCT r.departureCity AS city')
                ->orderBy('r.departureCity', 'ASC')
                ->getQuery()
                ->getScalarResult(),
            'city',
        );
    }

    /**
     * @return string[]
     */
    public function findDistinctTimeSlots(): array
    {
        return array_column(
            $this->createQueryBuilder('r')
                ->select('DISTINCT r.timeSlot AS slot')
                ->orderBy('r.timeSlot', 'ASC')
                ->getQuery()
                ->getScalarResult(),
            'slot',
        );
    }

    /**
     * Reservation counts grouped by departure point, and within each departure
     * point, by time slot: ['Ville (lieu)' => ['total' => 12, 'slots' => ['08h00' => 7, '11h00' => 5]]]
     *
     * @return array<string, array{total: int, slots: array<string, int>}>
     */
    public function countByDepartureCityAndTimeSlot(): array
    {
        $rows = $this->createQueryBuilder('r')
            ->select('r.departureCity AS city, r.timeSlot AS slot, COUNT(r.id) AS cnt')
            ->groupBy('r.departureCity, r.timeSlot')
            ->orderBy('r.departureCity', 'ASC')
            ->addOrderBy('r.timeSlot', 'ASC')
            ->getQuery()
            ->getResult();

        $stats = [];
        foreach ($rows as $row) {
            $city = $row['city'];
            $count = (int) $row['cnt'];
            $stats[$city]['total'] = ($stats[$city]['total'] ?? 0) + $count;
            $stats[$city]['slots'][$row['slot']] = $count;
        }

        return $stats;
    }
}
