<?php

namespace App\Controller;

use App\Services\ServicesCSV;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class BookingController extends AbstractController
{
    private ServicesCSV $csvService;

    public function __construct(ServicesCSV $csvService)
    {
        $this->csvService = $csvService;
    }

    #[Route('/api/bookings', name: 'create_booking', methods: ['POST'])]
    public function createBooking(Request $request): JsonResponse {
        $data = json_decode($request->getContent(), true);
        
        if (!isset($data['house_id']) || !isset($data['phone'])) {
            return $this->json(['error' => 'Missing required fields: house_id and phone'], 400);
        }
        
        $houseId = (int)$data['house_id'];
        $phone = $data['phone'];
        $comment = $data['comment'] ?? '';
        
        $house = $this->csvService->getHouseById($houseId);
        if (!$house) {
            return $this->json(['error' => 'House not found'], 404);
        }
        
        $success = $this->csvService->createBooking($houseId, $phone, $comment);
        
        if ($success) {
            return $this->json(['message' => 'Booking created successfully']);
        }
        
        return $this->json(['error' => 'Failed to create booking'], 500);
    }

    #[Route('/api/bookings/{id}', name: 'update_booking', methods: ['PUT'])]
    public function updateBooking(int $id, Request $request): JsonResponse {
        $data = json_decode($request->getContent(), true);
        
        if (!isset($data['comment'])) {
            return $this->json(['error' => 'Comment is required'], 400);
        }
        
        $booking = $this->csvService->getBookingById($id);
        if (!$booking) {
            return $this->json(['error' => 'Booking not found'], 404);
        }
        
        $success = $this->csvService->updateBooking($id, $data['comment']);
        
        if ($success) {
            return $this->json(['message' => 'Booking updated successfully']);
        }
        
        return $this->json(['error' => 'Failed to update booking'], 500);
    }

    #[Route('/api/bookings/{id}', name: 'delete_booking', methods: ['DELETE'])]
    public function deleteBooking(int $id): JsonResponse {
        $booking = $this->csvService->getBookingById($id);
        if (!$booking) {
            return $this->json(['error' => 'Booking not found'], 404);
        }
        
        $success = $this->csvService->deleteBooking($id);
        
        if ($success) {
            return $this->json(['message' => 'Booking deleted successfully']);
        }
        
        return $this->json(['error' => 'Failed to delete booking'], 500);
    }
}
