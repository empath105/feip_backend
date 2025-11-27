<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\House;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<House>
 */
class HouseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, House::class);
    }

    public function findAvailableHouses(): array
    {
        return $this->findBy(['isAvailable' => true]);
    }

    public function save(House $house, bool $flush = true): void
    {
        $this->getEntityManager()->persist($house);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(House $house, bool $flush = true): void
    {
        $this->getEntityManager()->remove($house);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
