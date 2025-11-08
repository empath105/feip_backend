<?php

namespace App\Controller;

use App\Services\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
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
            
            // Добавляем проверку на null
            if ($data === null) {
                return $this->json(['error' => 'Invalid JSON data'], 400);
            }
            
            $result = $this->userService->createUser($data);
            
            return $this->json($result, 201);
            
        } catch (\InvalidArgumentException $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            return $this->json(['error' => 'Failed to create user: ' . $e->getMessage()], 500);
        }
    }

    #[Route('/{id}', name: 'get_user', methods: ['GET'])]
    public function getUserById(int $id): JsonResponse
    {
        try {
            $user = $this->userService->getUserById($id);
            
            if (!$user) {
                return $this->json(['error' => 'User not found'], 404);
            }

            return $this->json(['user' => $user->toArray()]);
            
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 500);
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
            
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 500);
        }
    }

    #[Route('/{id}/bookings', name: 'get_user_bookings', methods: ['GET'])]
    public function getUserBookings(int $id): JsonResponse
    {
        try {
            $bookings = $this->userService->getUserBookings($id);
            
            return $this->json(['bookings' => $bookings]);
            
        } catch (\InvalidArgumentException $e) {
            return $this->json(['error' => $e->getMessage()], 404);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 500);
        }
    }

    #[Route('/{id}', name: 'delete_user', methods: ['DELETE'])]
    public function deleteUser(int $id): JsonResponse
    {
        try {
            $this->userService->deleteUser($id);
            
            return $this->json(['message' => 'User deleted successfully']);
            
        } catch (\InvalidArgumentException $e) {
            return $this->json(['error' => $e->getMessage()], 404);
        } catch (\Exception $e) {
            return $this->json(['error' => 'Failed to delete user: ' . $e->getMessage()], 500);
        }
    }
}
