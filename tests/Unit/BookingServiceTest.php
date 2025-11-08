<?php

namespace App\tests\Unit;

use App\Entity\Booking;
use App\Entity\User;
use App\Entity\House;
use App\Repository\BookingRepository;
use App\Repository\UserRepository;
use App\Repository\HouseRepository;
use App\Services\BookingService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class BookingServiceTest extends TestCase
{
    private BookingService $bookingService;
    private BookingRepository $bookingRepository;
    private UserRepository $userRepository;
    private HouseRepository $houseRepository;
    private ValidatorInterface $validator;

    protected function setUp(): void
    {
        $this->bookingRepository = $this->createMock(BookingRepository::class);
        $this->userRepository = $this->createMock(UserRepository::class);
        $this->houseRepository = $this->createMock(HouseRepository::class);
        $this->validator = $this->createMock(ValidatorInterface::class);
        
        $this->bookingService = new BookingService(
            $this->bookingRepository,
            $this->userRepository,
            $this->houseRepository,
            $this->validator
        );
    }

    public function testCreateBookingSuccess(): void
    {
        $bookingData = [
            'user_id' => 1,
            'house_id' => 1,
            'comment' => 'Test booking'
        ];

        $user = new User();
        $user->setPhone('+79991234567');

        $house = new House();
        $house->setIsAvailable(true);

        $this->userRepository->method('find')->with(1)->willReturn($user);
        $this->houseRepository->method('find')->with(1)->willReturn($house);
        $this->validator->method('validate')->willReturn(new ConstraintViolationList());

        $this->bookingRepository->expects($this->once())
            ->method('save')
            ->with($this->isInstanceOf(Booking::class));

        $result = $this->bookingService->createBooking($bookingData);

        $this->assertEquals('Booking created successfully', $result['message']);
        $this->assertArrayHasKey('booking', $result);
        $this->assertEquals('Test booking', $result['booking']['comment']);
    }

    public function testCreateBookingWithMissingFields(): void
    {
        $bookingData = ['comment' => 'Test booking'];

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing required fields: user_id and house_id');

        $this->bookingService->createBooking($bookingData);
    }

    public function testCreateBookingUserNotFound(): void
    {
        $bookingData = [
            'user_id' => 999,
            'house_id' => 1,
            'comment' => 'Test booking'
        ];

        $house = new House();
        $house->setIsAvailable(true);

        $this->userRepository->method('find')->with(999)->willReturn(null);
        $this->houseRepository->method('find')->with(1)->willReturn($house);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('User not found');

        $this->bookingService->createBooking($bookingData);
    }

    public function testCreateBookingHouseNotFound(): void
    {
        $bookingData = [
            'user_id' => 1,
            'house_id' => 999,
            'comment' => 'Test booking'
        ];

        $user = new User();
        $user->setPhone('+79991234567');

        $this->userRepository->method('find')->with(1)->willReturn($user);
        $this->houseRepository->method('find')->with(999)->willReturn(null);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('House not found');

        $this->bookingService->createBooking($bookingData);
    }

    public function testCreateBookingHouseNotAvailable(): void
    {
        $bookingData = [
            'user_id' => 1,
            'house_id' => 1,
            'comment' => 'Test booking'
        ];

        $user = new User();
        $user->setPhone('+79991234567');

        $house = new House();
        $house->setIsAvailable(false);

        $this->userRepository->method('find')->with(1)->willReturn($user);
        $this->houseRepository->method('find')->with(1)->willReturn($house);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('House is not available for booking');

        $this->bookingService->createBooking($bookingData);
    }

    public function testUpdateBookingCommentSuccess(): void
    {
        $booking = new Booking();
        $this->bookingRepository->method('find')->with(1)->willReturn($booking);

        $this->bookingRepository->expects($this->once())
            ->method('save')
            ->with($booking);

        $result = $this->bookingService->updateBookingComment(1, 'Updated comment');

        $this->assertEquals('Booking updated successfully', $result['message']);
        $this->assertEquals('Updated comment', $booking->getComment());
    }

    public function testUpdateBookingCommentNotFound(): void
    {
        $this->bookingRepository->method('find')->with(999)->willReturn(null);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Booking not found');

        $this->bookingService->updateBookingComment(999, 'Updated comment');
    }

    public function testGetBookingByIdSuccess(): void
    {
        $booking = new Booking();
        $this->bookingRepository->method('find')->with(1)->willReturn($booking);

        $result = $this->bookingService->getBookingById(1);

        $this->assertSame($booking, $result);
    }

    public function testGetBookingByIdNotFound(): void
    {
        $this->bookingRepository->method('find')->with(999)->willReturn(null);

        $result = $this->bookingService->getBookingById(999);

        $this->assertNull($result);
    }

    public function testDeleteBookingSuccess(): void
    {
        $booking = new Booking();
        $this->bookingRepository->method('find')->with(1)->willReturn($booking);

        $this->bookingRepository->expects($this->once())
            ->method('remove')
            ->with($booking);

        $this->bookingService->deleteBooking(1);

        $this->assertTrue(true);
    }

    public function testDeleteBookingNotFound(): void
    {
        $this->bookingRepository->method('find')->with(999)->willReturn(null);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Booking not found');

        $this->bookingService->deleteBooking(999);
    }
}
