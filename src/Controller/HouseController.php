<?php

namespace App\Controller;

use App\Services\ServicesCSV;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

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
            return $this->json(['error' => $e->getMessage()], 500);
        }
    }

    #[Route('/api/houses/{id}', name: 'get_house', methods: ['GET'])]
    public function getHouse(int $id): JsonResponse {
        try {
            $house = $this->csvService->getHouseById($id);
            
            if (!$house) {
                return $this->json(['error' => 'House not found'], 404);
            }
            
            return $this->json($house->toArray(), 200, [], ['json_encode_options' => JSON_UNESCAPED_UNICODE]);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 500);
        }
    }
}
