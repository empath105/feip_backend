<?php

namespace App\Controller;

use App\Services\ServicesCSV;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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
            throw new BadRequestHttpException('Missing required fields: house_id and phone');
        }
        
        $houseId = (int)$data['house_id'];
        $phone = $data['phone'];
        $comment = $data['comment'] ?? '';
        
        $house = $this->csvService->getHouseById($houseId);
        if (!$house) {
            throw new NotFoundHttpException('House not found');
        }
        
        $success = $this->csvService->createBooking($houseId, $phone, $comment);
        
        if (!$success) {
            throw new BadRequestHttpException('Failed to create booking');
        }
        
        return $this->json(['message' => 'Booking created successfully'], 201);
    }

    #[Route('/api/bookings/{id}', name: 'update_booking', methods: ['PUT'])]
    public function updateBooking(int $id, Request $request): JsonResponse {
        $data = json_decode($request->getContent(), true);
        
        if (!isset($data['comment'])) {
            throw new BadRequestHttpException('Comment is required');
        }
        
        $booking = $this->csvService->getBookingById($id);
        if (!$booking) {
            throw new NotFoundHttpException('Booking not found');
        }
        
        $success = $this->csvService->updateBooking($id, $data['comment']);
        
        if (!$success) {
            throw new BadRequestHttpException('Failed to update booking');
        }
        
        return $this->json(['message' => 'Booking updated successfully']);
    }

    #[Route('/api/bookings/{id}', name: 'delete_booking', methods: ['DELETE'])]
    public function deleteBooking(int $id): JsonResponse {
        $booking = $this->csvService->getBookingById($id);
        if (!$booking) {
            throw new NotFoundHttpException('Booking not found');
        }
        
        $success = $this->csvService->deleteBooking($id);
        
        if (!$success) {
            throw new BadRequestHttpException('Failed to delete booking');
        }
        
        return $this->json(['message' => 'Booking deleted successfully']);
    }
}
