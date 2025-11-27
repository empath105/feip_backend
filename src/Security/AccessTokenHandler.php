<?php

declare(strict_types=1);

namespace App\Security;

use App\Repository\AccessTokenRepository;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Http\AccessToken\AccessTokenHandlerInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;

class AccessTokenHandler implements AccessTokenHandlerInterface
{
    public function __construct(
        private AccessTokenRepository $accessTokenRepository,
    ) {
    }

    public function getUserBadgeFrom(string $accessToken): UserBadge
    {
        $token = $this->accessTokenRepository->findByValue($accessToken);

        if (!$token) {
            throw new BadCredentialsException('Invalid access token');
        }

        if (!$token->isValid()) {
            throw new BadCredentialsException('Token expired');
        }

        $user = $token->getUser();

        if (!$user) {
            throw new BadCredentialsException('Token has no user');
        }

        return new UserBadge($user->getUserIdentifier());
    }
}
