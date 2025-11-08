<?php

namespace App\Services;

use App\Entity\Booking;
use App\Repository\BookingRepository;
use App\Repository\UserRepository;
use App\Repository\HouseRepository;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class BookingService
{
    private BookingRepository $bookingRepository;
    private UserRepository $userRepository;
    private HouseRepository $houseRepository;
    private ValidatorInterface $validator;

    public function __construct(
        BookingRepository $bookingRepository,
        UserRepository $userRepository,
        HouseRepository $houseRepository,
        ValidatorInterface $validator
    ) {
        $this->bookingRepository = $bookingRepository;
        $this->userRepository = $userRepository;
        $this->houseRepository = $houseRepository;
        $this->validator = $validator;
    }

    public function createBooking(array $data): array
    {
        if (!isset($data['user_id']) || !isset($data['house_id'])) {
            throw new \InvalidArgumentException('Missing required fields: user_id and house_id');
        }

        $userId = (int)$data['user_id'];
        $houseId = (int)$data['house_id'];
        $comment = $data['comment'] ?? '';

        $user = $this->userRepository->find($userId);
        if (!$user) {
            throw new \InvalidArgumentException('User not found');
        }

        $house = $this->houseRepository->find($houseId);
        if (!$house) {
            throw new \InvalidArgumentException('House not found');
        }

        if (!$house->isIsAvailable()) {
            throw new \InvalidArgumentException('House is not available for booking');
        }

        $booking = new Booking();
        $booking->setUser($user);
        $booking->setHouse($house);
        $booking->setComment($comment);
        $booking->setStatus('active');

        $errors = $this->validator->validate($booking);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }
            throw new \InvalidArgumentException('Validation failed: ' . implode(', ', $errorMessages));
        }

        $this->bookingRepository->save($booking);

        return [
            'message' => 'Booking created successfully',
            'booking' => $booking->toArray()
        ];
    }

    public function updateBookingComment(int $id, string $comment): array
    {
        $booking = $this->bookingRepository->find($id);
        if (!$booking) {
            throw new \InvalidArgumentException('Booking not found');
        }

        $booking->setComment($comment);

        $this->bookingRepository->save($booking);

        return [
            'message' => 'Booking updated successfully',
            'booking' => $booking->toArray()
        ];
    }

    public function getBookingById(int $id): ?Booking
    {
        return $this->bookingRepository->find($id);
    }

    public function deleteBooking(int $id): void
    {
        $booking = $this->bookingRepository->find($id);
        if (!$booking) {
            throw new \InvalidArgumentException('Booking not found');
        }

        $this->bookingRepository->remove($booking);
    }
}
