<?php

declare(strict_types=1);

namespace App\Services;

use App\Entity\AccessToken;
use App\Entity\User;
use App\Repository\AccessTokenRepository;
use DateTimeImmutable;

class AccessTokenService
{
    public function __construct(
        private AccessTokenRepository $accessTokenRepository,
    ) {
    }

    public function createToken(User $user, ?DateTimeImmutable $expiresAt = null): AccessToken
    {
        if (!$expiresAt) {
            $expiresAt = (new DateTimeImmutable())->modify('+7 days');
        }

        $this->removeUserTokens($user);

        $accessToken = new AccessToken();
        $accessToken->setUser($user);
        $accessToken->setExpiresAt($expiresAt);

        $this->accessTokenRepository->save($accessToken);

        return $accessToken;
    }

    public function removeToken(AccessToken $accessToken): void
    {
        $this->accessTokenRepository->remove($accessToken);
    }

    public function removeUserTokens(User $user): void
    {
        $tokens = $this->accessTokenRepository->findBy(['user' => $user]);

        foreach ($tokens as $token) {
            $this->accessTokenRepository->remove($token, false);
        }

        $this->accessTokenRepository->getEntityManager()->flush();
    }

    public function findToken(string $value): ?AccessToken
    {
        return $this->accessTokenRepository->findByValue($value);
    }

    public function validateToken(string $value): bool
    {
        $token = $this->findToken($value);

        return $token && $token->isValid();
    }
}
