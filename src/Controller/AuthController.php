<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\UserRepository;
use App\Services\AccessTokenService;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api')]
class AuthController extends AbstractController
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher,
        private AccessTokenService $accessTokenService,
        private UserRepository $userRepository,
        private EntityManagerInterface $entityManager,
    ) {
    }

    #[Route('/login', name: 'api_login', methods: ['POST'])]
    public function login(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);

            if (null === $data) {
                throw new BadRequestHttpException('Invalid JSON data');
            }

            if (!isset($data['phone']) || !isset($data['password'])) {
                throw new BadRequestHttpException('Phone and password are required');
            }

            $user = $this->userRepository->findOneBy(['phone' => $data['phone']]);

            if (!$user || !$this->passwordHasher->isPasswordValid($user, $data['password'])) {
                throw new UnauthorizedHttpException('Bearer', 'Invalid credentials');
            }

            $accessToken = $this->accessTokenService->createToken($user);

            return $this->json([
                'message' => 'Login successful',
                'token' => $accessToken->getValue(),
                'token_type' => 'Bearer',
                'expires_at' => $accessToken->getExpiresAt()->format('Y-m-d H:i:s'),
                'user' => $user->toArray(),
            ]);
        } catch (BadRequestHttpException|UnauthorizedHttpException $e) {
            throw $e;
        } catch (Exception $e) {
            throw new BadRequestHttpException('Failed to login: ' . $e->getMessage());
        }
    }

    #[Route('/logout', name: 'api_logout', methods: ['POST'])]
    public function logout(): JsonResponse
    {
        $user = $this->getUser();

        if ($user) {
            $this->accessTokenService->removeUserTokens($user);
        }

        try {
            return $this->json(['message' => 'Logout successful']);
        } catch (Exception $e) {
            throw new BadRequestHttpException('Failed to logout: ' . $e->getMessage());
        }
    }

    #[Route('/profile', name: 'api_profile', methods: ['GET'])]
    public function profile(): JsonResponse
    {
        try {
            $user = $this->getUser();

            if (!$user) {
                throw new UnauthorizedHttpException('Bearer', 'Not authenticated');
            }

            return $this->json([
                'user' => $user->toArray(),
            ]);
        } catch (UnauthorizedHttpException $e) {
            throw $e;
        } catch (Exception $e) {
            throw new BadRequestHttpException('Failed to get profile: ' . $e->getMessage());
        }
    }
}
