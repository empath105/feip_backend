<?php

namespace App\Controller;

use App\Services\BookingService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/bookings')]
class BookingController extends AbstractController
{
    private BookingService $bookingService;

    public function __construct(BookingService $bookingService)
    {
        $this->bookingService = $bookingService;
    }

    #[Route('', name: 'create_booking', methods: ['POST'])]
    public function createBooking(Request $request): JsonResponse 
    {
        try {
            $data = json_decode($request->getContent(), true);
            $result = $this->bookingService->createBooking($data);
            
            return $this->json($result, 201);
            
        } catch (\InvalidArgumentException $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            return $this->json(['error' => 'Failed to create booking: ' . $e->getMessage()], 500);
        }
    }

    #[Route('/{id}', name: 'update_booking', methods: ['PUT'])]
    public function updateBooking(int $id, Request $request): JsonResponse 
    {
        try {
            $data = json_decode($request->getContent(), true);
            
            if (!isset($data['comment'])) {
                return $this->json(['error' => 'Comment is required'], 400);
            }
            
            $result = $this->bookingService->updateBookingComment($id, $data['comment']);
            
            return $this->json($result);
            
        } catch (\InvalidArgumentException $e) {
            return $this->json(['error' => $e->getMessage()], 404);
        } catch (\Exception $e) {
            return $this->json(['error' => 'Failed to update booking: ' . $e->getMessage()], 500);
        }
    }

    #[Route('/{id}', name: 'delete_booking', methods: ['DELETE'])]
    public function deleteBooking(int $id): JsonResponse 
    {
        try {
            $this->bookingService->deleteBooking($id);
            return $this->json(['message' => 'Booking deleted successfully']);
            
        } catch (\InvalidArgumentException $e) {
            return $this->json(['error' => $e->getMessage()], 404);
        } catch (\Exception $e) {
            return $this->json(['error' => 'Failed to delete booking: ' . $e->getMessage()], 500);
        }
    }

    #[Route('/{id}', name: 'get_booking', methods: ['GET'])]
    public function getBooking(int $id): JsonResponse
    {
        try {
            $booking = $this->bookingService->getBookingById($id);
            if (!$booking) {
                return $this->json(['error' => 'Booking not found'], 404);
            }
            
            return $this->json(['booking' => $booking->toArray()]);
            
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 500);
        }
    }
}
