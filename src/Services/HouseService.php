<?php

namespace App\Services;

use App\Entity\House;
use App\Repository\HouseRepository;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class HouseService
{
    private HouseRepository $houseRepository;
    private ValidatorInterface $validator;

    public function __construct(
        HouseRepository $houseRepository,
        ValidatorInterface $validator
    ) {
        $this->houseRepository = $houseRepository;
        $this->validator = $validator;
    }

    public function createHouse(array $data): array
    {
        $requiredFields = ['name', 'beds', 'distance_to_sea', 'price_per_night'];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field])) {
                throw new \InvalidArgumentException("Missing required field: $field");
            }
        }

        $house = new House();
        $house->setName($data['name']);
        $house->setBeds((int)$data['beds']);
        $house->setAmenities($data['amenities'] ?? '');
        $house->setDistanceToSea((int)$data['distance_to_sea']);
        $house->setPricePerNight((string)$data['price_per_night']);
        $house->setIsAvailable($data['is_available'] ?? true);

        $errors = $this->validator->validate($house);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }
            throw new \InvalidArgumentException('Validation failed: ' . implode(', ', $errorMessages));
        }

        $this->houseRepository->save($house);

        return [
            'message' => 'House created successfully',
            'house' => $house->toArray()
        ];
    }

    public function getAvailableHouses(): array
    {
        return $this->houseRepository->findAvailableHouses();
    }

    public function getHouseById(int $id): ?House
    {
        return $this->houseRepository->find($id);
    }

    public function deleteHouse(int $id): void
    {
        $house = $this->houseRepository->find($id);
        if (!$house) {
            throw new \InvalidArgumentException('House not found');
        }

        $this->houseRepository->remove($house);
    }

    public function getHouseBookings(int $houseId): array
    {
        $house = $this->houseRepository->find($houseId);
        if (!$house) {
            throw new \InvalidArgumentException('House not found');
        }

        $bookings = [];
        foreach ($house->getBookings() as $booking) {
            $bookings[] = $booking->toArray();
        }

        return $bookings;
    }
}
