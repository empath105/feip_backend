<?php

namespace App\Controller;

use App\Services\ServicesCSV;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class HouseController extends AbstractController
{
   private ServicesCSV $csvService;

    public function __construct(ServicesCSV $csvService)
    {
        $this->csvService = $csvService;
    }

    #[Route('/api/houses/available', name: 'available_houses', methods: ['GET'])]
    public function getAvailableHouses(): JsonResponse {
        try {
            $houses = $this->csvService->getAvailableHouses();
            
            $housesArray = [];
            foreach ($houses as $house) {
                $housesArray[] = $house->toArray();
            }
            
            return $this->json($housesArray, 200, [], ['json_encode_options' => JSON_UNESCAPED_UNICODE]);
        } catch (\Exception $e) {
            throw new BadRequestHttpException('Failed to get available houses: ' . $e->getMessage());
        }
    }

    #[Route('/api/houses/{id}', name: 'get_house', methods: ['GET'])]
    public function getHouse(int $id): JsonResponse {
        try {
            $house = $this->csvService->getHouseById($id);
            
            if (!$house) {
                throw new NotFoundHttpException('House not found');
            }
            
            return $this->json($house->toArray(), 200, [], ['json_encode_options' => JSON_UNESCAPED_UNICODE]);
        } catch (\Exception $e) {
            throw new BadRequestHttpException('Failed to get house: ' . $e->getMessage());
        }
    }
}
