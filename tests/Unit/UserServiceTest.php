<?php

namespace App\tests\Unit;

use App\Entity\User;
use App\Entity\Booking;
use App\Repository\UserRepository;
use App\Services\UserService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UserServiceTest extends TestCase
{
    private UserService $userService;
    private UserRepository $userRepository;
    private ValidatorInterface $validator;

    protected function setUp(): void
    {
        $this->userRepository = $this->createMock(UserRepository::class);
        $this->validator = $this->createMock(ValidatorInterface::class);
        
        $this->userService = new UserService(
            $this->userRepository,
            $this->validator
        );
    }

    public function testCreateUserSuccess(): void
    {
        $userData = [
            'email' => 'test@example.com',
            'phone' => '+79991234567',
            'name' => 'Test User'
        ];

        $this->userRepository->method('findByEmail')->willReturn(null);
        $this->userRepository->method('findByPhone')->willReturn(null);
        $this->validator->method('validate')->willReturn(new ConstraintViolationList());

        $this->userRepository->expects($this->once())
            ->method('save')
            ->with($this->isInstanceOf(User::class));

        $result = $this->userService->createUser($userData);

        $this->assertEquals('User created successfully', $result['message']);
        $this->assertArrayHasKey('user', $result);
        $this->assertEquals('test@example.com', $result['user']['email']);
    }

    public function testCreateUserWithExistingEmail(): void
    {
        $userData = [
            'email' => 'existing@example.com',
            'phone' => '+79991234567',
            'name' => 'Test User'
        ];

        $existingUser = new User();
        $this->userRepository->method('findByEmail')->willReturn($existingUser);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('User with this email already exists');

        $this->userService->createUser($userData);
    }

    public function testCreateUserWithExistingPhone(): void
    {
        $userData = [
            'email' => 'test@example.com',
            'phone' => '+79998887766',
            'name' => 'Test User'
        ];

        $existingUser = new User();
        $this->userRepository->method('findByPhone')->willReturn($existingUser);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('User with this phone already exists');

        $this->userService->createUser($userData);
    }

    public function testCreateUserWithMissingFields(): void
    {
        $userData = ['email' => 'test@example.com'];

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing required fields: email, phone, name');

        $this->userService->createUser($userData);
    }

    public function testCreateUserWithValidationErrors(): void
    {
        $userData = [
            'email' => 'invalid-email',
            'phone' => '+79991234567',
            'name' => 'Test User'
        ];

        $this->userRepository->method('findByEmail')->willReturn(null);
        $this->userRepository->method('findByPhone')->willReturn(null);

        $violation = $this->createMock(ConstraintViolation::class);
        $violation->method('getMessage')->willReturn('Invalid email format');
        $violations = new ConstraintViolationList([$violation]);

        $this->validator->method('validate')->willReturn($violations);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Validation failed: Invalid email format');

        $this->userService->createUser($userData);
    }

    public function testGetUserByIdSuccess(): void
    {
        $user = new User();
        $user->setEmail('test@example.com');
        $user->setPhone('+79991234567');
        $user->setName('Test User');

        $this->userRepository->method('find')->with(1)->willReturn($user);

        $result = $this->userService->getUserById(1);

        $this->assertSame($user, $result);
    }

    public function testGetUserByIdNotFound(): void
    {
        $this->userRepository->method('find')->with(999)->willReturn(null);

        $result = $this->userService->getUserById(999);

        $this->assertNull($result);
    }

    public function testGetAllUsers(): void
    {
        $user1 = new User();
        $user2 = new User();
        $users = [$user1, $user2];

        $this->userRepository->method('findAll')->willReturn($users);

        $result = $this->userService->getAllUsers();

        $this->assertSame($users, $result);
        $this->assertCount(2, $result);
    }

    public function testDeleteUserSuccess(): void
    {
        $user = new User();
        $this->userRepository->method('find')->with(1)->willReturn($user);

        $this->userRepository->expects($this->once())
            ->method('remove')
            ->with($user);

        $this->userService->deleteUser(1);

        $this->assertTrue(true);
    }

    public function testDeleteUserNotFound(): void
    {
        $this->userRepository->method('find')->with(999)->willReturn(null);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('User not found');

        $this->userService->deleteUser(999);
    }

    public function testGetUserBookingsSuccess(): void
    {
        $user = new User();
        $booking1 = new Booking();
        $booking2 = new Booking();
        
        $user->addBooking($booking1);
        $user->addBooking($booking2);

        $this->userRepository->method('find')->with(1)->willReturn($user);

        $result = $this->userService->getUserBookings(1);

        $this->assertIsArray($result);
        $this->assertCount(2, $result);
    }

    public function testGetUserBookingsUserNotFound(): void
    {
        $this->userRepository->method('find')->with(999)->willReturn(null);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('User not found');

        $this->userService->getUserBookings(999);
    }
}
