<?php

declare(strict_types=1);

namespace App\Services;

use App\Entity\User;
use App\Repository\UserRepository;
use InvalidArgumentException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UserService
{
    private UserRepository $userRepository;
    private ValidatorInterface $validator;

    public function __construct(
        UserRepository $userRepository,
        ValidatorInterface $validator,
    ) {
        $this->userRepository = $userRepository;
        $this->validator = $validator;
    }

    public function createUser(array $data): array
    {
        if (!isset($data['email']) || !isset($data['phone']) || !isset($data['name'])) {
            throw new InvalidArgumentException('Missing required fields: email, phone, name');
        }

        $existingUser = $this->userRepository->findByEmail($data['email']);
        if ($existingUser) {
            throw new InvalidArgumentException('User with this email already exists');
        }

        $existingUser = $this->userRepository->findByPhone($data['phone']);
        if ($existingUser) {
            throw new InvalidArgumentException('User with this phone already exists');
        }

        $user = new User();
        $user->setEmail($data['email']);
        $user->setPhone($data['phone']);
        $user->setName($data['name']);

        $errors = $this->validator->validate($user);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }
            throw new InvalidArgumentException('Validation failed: ' . implode(', ', $errorMessages));
        }

        $this->userRepository->save($user);

        return [
            'message' => 'User created successfully',
            'user' => $user->toArray(),
        ];
    }

    public function getUserById(int $id): ?User
    {
        return $this->userRepository->find($id);
    }

    public function getAllUsers(): array
    {
        return $this->userRepository->findAll();
    }

    public function deleteUser(int $id): void
    {
        $user = $this->userRepository->find($id);
        if (!$user) {
            throw new InvalidArgumentException('User not found');
        }

        $this->userRepository->remove($user);
    }

    public function getUserBookings(int $userId): array
    {
        $user = $this->userRepository->find($userId);
        if (!$user) {
            throw new InvalidArgumentException('User not found');
        }

        $bookings = [];
        foreach ($user->getBookings() as $booking) {
            $bookings[] = $booking->toArray();
        }

        return $bookings;
    }
}
