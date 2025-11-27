<?php

declare(strict_types=1);

namespace App\Controller;

use App\Services\BookingService;
use Exception;
use InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
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

            if (null === $data) {
                throw new BadRequestHttpException('Invalid JSON data');
            }

            $result = $this->bookingService->createBooking($data);

            return $this->json($result, 201);
        } catch (InvalidArgumentException $e) {
            throw new BadRequestHttpException($e->getMessage());
        } catch (Exception $e) {
            throw new BadRequestHttpException('Failed to create booking: ' . $e->getMessage());
        }
    }

    #[Route('/{id}', name: 'update_booking', methods: ['PUT'])]
    public function updateBooking(int $id, Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);

            if (null === $data) {
                throw new BadRequestHttpException('Invalid JSON data');
            }

            if (!isset($data['comment'])) {
                throw new BadRequestHttpException('Comment is required');
            }

            $result = $this->bookingService->updateBookingComment($id, $data['comment']);

            return $this->json($result);
        } catch (InvalidArgumentException $e) {
            throw new NotFoundHttpException($e->getMessage());
        } catch (Exception $e) {
            throw new BadRequestHttpException('Failed to update booking: ' . $e->getMessage());
        }
    }

    #[Route('/{id}', name: 'delete_booking', methods: ['DELETE'])]
    public function deleteBooking(int $id): JsonResponse
    {
        try {
            $this->bookingService->deleteBooking($id);

            return $this->json(['message' => 'Booking deleted successfully']);
        } catch (InvalidArgumentException $e) {
            throw new NotFoundHttpException($e->getMessage());
        } catch (Exception $e) {
            throw new BadRequestHttpException('Failed to delete booking: ' . $e->getMessage());
        }
    }

    #[Route('/{id}', name: 'get_booking', methods: ['GET'])]
    public function getBooking(int $id): JsonResponse
    {
        try {
            $booking = $this->bookingService->getBookingById($id);
            if (!$booking) {
                throw new NotFoundHttpException('Booking not found');
            }

            return $this->json(['booking' => $booking->toArray()]);
        } catch (Exception $e) {
            throw new BadRequestHttpException('Failed to get booking: ' . $e->getMessage());
        }
    }
}
