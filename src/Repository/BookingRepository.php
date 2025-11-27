<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Booking;
use App\Entity\House;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Booking>
 */
class BookingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Booking::class);
    }

    public function findUserBookings(User $user): array
    {
        return $this->findBy(['user' => $user]);
    }

    public function findHouseBookings(House $house): array
    {
        return $this->findBy(['house' => $house]);
    }

    public function findActiveBookings(): array
    {
        return $this->findBy(['status' => 'active']);
    }

    public function save(Booking $booking, bool $flush = true): void
    {
        $this->getEntityManager()->persist($booking);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Booking $booking, bool $flush = true): void
    {
        $this->getEntityManager()->remove($booking);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
