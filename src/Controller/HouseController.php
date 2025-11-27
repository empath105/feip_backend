<?php

declare(strict_types=1);

namespace App\Controller;

use App\Services\HouseService;
use Exception;
use InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/houses')]
class HouseController extends AbstractController
{
    private HouseService $houseService;

    public function __construct(HouseService $houseService)
    {
        $this->houseService = $houseService;
    }

    #[Route('', name: 'create_house', methods: ['POST'])]
    public function createHouse(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);

            if (null === $data) {
                throw new BadRequestHttpException('Invalid JSON data');
            }

            $result = $this->houseService->createHouse($data);

            return $this->json($result, 201);
        } catch (InvalidArgumentException $e) {
            throw new BadRequestHttpException($e->getMessage());
        } catch (Exception $e) {
            throw new BadRequestHttpException('Failed to create house: ' . $e->getMessage());
        }
    }

    #[Route('/available', name: 'available_houses', methods: ['GET'])]
    public function getAvailableHouses(): JsonResponse
    {
        try {
            $houses = $this->houseService->getAvailableHouses();

            $housesArray = [];
            foreach ($houses as $house) {
                $housesArray[] = $house->toArray();
            }

            return $this->json($housesArray, 200, [], ['json_encode_options' => JSON_UNESCAPED_UNICODE]);
        } catch (Exception $e) {
            throw new BadRequestHttpException('Failed to get available houses: ' . $e->getMessage());
        }
    }

    #[Route('/{id}', name: 'get_house', methods: ['GET'])]
    public function getHouse(int $id): JsonResponse
    {
        try {
            $house = $this->houseService->getHouseById($id);

            if (!$house) {
                throw new NotFoundHttpException('House not found');
            }

            return $this->json($house->toArray(), 200, [], ['json_encode_options' => JSON_UNESCAPED_UNICODE]);
        } catch (Exception $e) {
            throw new BadRequestHttpException('Failed to get house: ' . $e->getMessage());
        }
    }

    #[Route('/{id}', name: 'delete_house', methods: ['DELETE'])]
    public function deleteHouse(int $id): JsonResponse
    {
        try {
            $this->houseService->deleteHouse($id);

            return $this->json(['message' => 'House deleted successfully']);
        } catch (InvalidArgumentException $e) {
            throw new NotFoundHttpException($e->getMessage());
        } catch (Exception $e) {
            throw new BadRequestHttpException('Failed to delete house: ' . $e->getMessage());
        }
    }

    #[Route('/{id}/bookings', name: 'get_house_bookings', methods: ['GET'])]
    public function getHouseBookings(int $id): JsonResponse
    {
        try {
            $bookings = $this->houseService->getHouseBookings($id);

            return $this->json(['bookings' => $bookings]);
        } catch (InvalidArgumentException $e) {
            throw new NotFoundHttpException($e->getMessage());
        } catch (Exception $e) {
            throw new BadRequestHttpException('Failed to get house bookings: ' . $e->getMessage());
        }
    }
}
