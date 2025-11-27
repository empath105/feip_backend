<?php

declare(strict_types=1);

namespace App\tests\Unit;

use App\Entity\Booking;
use App\Entity\House;
use App\Repository\HouseRepository;
use App\Services\HouseService;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class HouseServiceTest extends TestCase
{
    private HouseService $houseService;
    private HouseRepository $houseRepository;
    private ValidatorInterface $validator;

    protected function setUp(): void
    {
        $this->houseRepository = $this->createMock(HouseRepository::class);
        $this->validator = $this->createMock(ValidatorInterface::class);

        $this->houseService = new HouseService(
            $this->houseRepository,
            $this->validator
        );
    }

    public function testCreateHouseSuccess(): void
    {
        $houseData = [
            'name' => 'Test House',
            'beds' => 2,
            'amenities' => 'WiFi, TV',
            'distance_to_sea' => 1,
            'price_per_night' => 5000,
        ];

        $this->validator->method('validate')->willReturn(new ConstraintViolationList());

        $this->houseRepository->expects($this->once())
            ->method('save')
            ->with($this->isInstanceOf(House::class));

        $result = $this->houseService->createHouse($houseData);

        $this->assertEquals('House created successfully', $result['message']);
        $this->assertArrayHasKey('house', $result);
        $this->assertEquals('Test House', $result['house']['name']);
    }

    public function testCreateHouseWithMissingFields(): void
    {
        $houseData = ['name' => 'Test House'];

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing required field: beds');

        $this->houseService->createHouse($houseData);
    }

    public function testGetAvailableHouses(): void
    {
        $house1 = new House();
        $house2 = new House();
        $availableHouses = [$house1, $house2];

        $this->houseRepository->method('findAvailableHouses')->willReturn($availableHouses);

        $result = $this->houseService->getAvailableHouses();

        $this->assertSame($availableHouses, $result);
        $this->assertCount(2, $result);
    }

    public function testGetHouseByIdSuccess(): void
    {
        $house = new House();
        $house->setName('Test House');

        $this->houseRepository->method('find')->with(1)->willReturn($house);

        $result = $this->houseService->getHouseById(1);

        $this->assertSame($house, $result);
    }

    public function testGetHouseByIdNotFound(): void
    {
        $this->houseRepository->method('find')->with(999)->willReturn(null);

        $result = $this->houseService->getHouseById(999);

        $this->assertNull($result);
    }

    public function testDeleteHouseSuccess(): void
    {
        $house = new House();
        $this->houseRepository->method('find')->with(1)->willReturn($house);

        $this->houseRepository->expects($this->once())
            ->method('remove')
            ->with($house);

        $this->houseService->deleteHouse(1);

        $this->assertTrue(true);
    }

    public function testDeleteHouseNotFound(): void
    {
        $this->houseRepository->method('find')->with(999)->willReturn(null);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('House not found');

        $this->houseService->deleteHouse(999);
    }

    public function testGetHouseBookingsSuccess(): void
    {
        $house = new House();
        $booking1 = new Booking();
        $booking2 = new Booking();

        $house->addBooking($booking1);
        $house->addBooking($booking2);

        $this->houseRepository->method('find')->with(1)->willReturn($house);

        $result = $this->houseService->getHouseBookings(1);

        $this->assertIsArray($result);
        $this->assertCount(2, $result);
    }

    public function testGetHouseBookingsHouseNotFound(): void
    {
        $this->houseRepository->method('find')->with(999)->willReturn(null);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('House not found');

        $this->houseService->getHouseBookings(999);
    }
}
