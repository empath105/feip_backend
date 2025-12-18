<?php

declare(strict_types=1);

namespace App\Controller;

use App\Services\UserService;
use Exception;
use InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/users')]
class UserController extends AbstractController
{
    private UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    #[Route('', name: 'create_user', methods: ['POST'])]
    public function createUser(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);

            if (null === $data) {
                throw new BadRequestHttpException('Invalid JSON data');
            }

            if (!isset($data['password'])) {
                $data['password'] = bin2hex(random_bytes(8));
            }

            $result = $this->userService->createUser($data);

            return $this->json($result, 201);
        } catch (InvalidArgumentException $e) {
            throw new BadRequestHttpException($e->getMessage());
        } catch (Exception $e) {
            throw new BadRequestHttpException('Failed to create user: ' . $e->getMessage());
        }
    }

    #[Route('/{id}', name: 'get_user', methods: ['GET'])]
    public function getUserById(int $id): JsonResponse
    {
        try {
            $user = $this->userService->getUserById($id);

            if (!$user) {
                throw new NotFoundHttpException('User not found');
            }

            return $this->json(['user' => $user->toArray()]);
        } catch (Exception $e) {
            throw new BadRequestHttpException('Failed to get user: ' . $e->getMessage());
        }
    }

    #[Route('', name: 'get_all_users', methods: ['GET'])]
    public function getAllUsers(): JsonResponse
    {
        try {
            $users = $this->userService->getAllUsers();

            $usersArray = [];
            foreach ($users as $user) {
                $usersArray[] = $user->toArray();
            }

            return $this->json(['users' => $usersArray]);
        } catch (Exception $e) {
            throw new BadRequestHttpException('Failed to get all users: ' . $e->getMessage());
        }
    }

    #[Route('/{id}/bookings', name: 'get_user_bookings', methods: ['GET'])]
    public function getUserBookings(int $id): JsonResponse
    {
        try {
            $bookings = $this->userService->getUserBookings($id);

            return $this->json(['bookings' => $bookings]);
        } catch (InvalidArgumentException $e) {
            throw new NotFoundHttpException($e->getMessage());
        } catch (Exception $e) {
            throw new BadRequestHttpException('Failed to get user bookings: ' . $e->getMessage());
        }
    }

    #[Route('/{id}', name: 'delete_user', methods: ['DELETE'])]
    public function deleteUser(int $id): JsonResponse
    {
        try {
            $this->userService->deleteUser($id);

            return $this->json(['message' => 'User deleted successfully']);
        } catch (InvalidArgumentException $e) {
            throw new NotFoundHttpException($e->getMessage());
        } catch (Exception $e) {
            throw new BadRequestHttpException('Failed to delete user: ' . $e->getMessage());
        }
    }
}
