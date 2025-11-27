<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\AccessToken;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<AccessToken>
 */
class AccessTokenRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AccessToken::class);
    }

    public function findByValue(string $value): ?AccessToken
    {
        return $this->findOneBy(['value' => $value]);
    }

    public function findByUser(User $user): ?AccessToken
    {
        return $this->findOneBy(['user' => $user]);
    }

    public function save(AccessToken $accessToken, bool $flush = true): void
    {
        $this->getEntityManager()->persist($accessToken);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(AccessToken $accessToken, bool $flush = true): void
    {
        $this->getEntityManager()->remove($accessToken);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
