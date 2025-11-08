<?php

namespace App\Tests\API;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class BookingControllerTest extends WebTestCase
{
    private function createTestUser($client)
    {
        $uniqueEmail = 'booking-user-' . uniqid() . '@example.com';
        $uniquePhone = '+7999' . rand(1000000, 9999999);
        
        $client->request(
            'POST',
            '/api/users',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'email' => $uniqueEmail,
                'phone' => $uniquePhone,
                'name' => 'Booking Test User'
            ])
        );
        return json_decode($client->getResponse()->getContent(), true)['user'];
    }

    private function createTestHouse($client)
    {
        $uniqueName = 'Booking House ' . uniqid();
        
        $client->request(
            'POST',
            '/api/houses',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'name' => $uniqueName,
                'beds' => 2,
                'distance_to_sea' => 1,
                'price_per_night' => 4000
            ])
        );
        return json_decode($client->getResponse()->getContent(), true)['house'];
    }

    public function testCreateBooking(): void
    {
        $client = static::createClient();
        
        $user = $this->createTestUser($client);
        $house = $this->createTestHouse($client);

        $client->request(
            'POST',
            '/api/bookings',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'user_id' => $user['id'],
                'house_id' => $house['id'],
                'comment' => 'API Test Booking ' . uniqid()
            ])
        );

        $this->assertEquals(201, $client->getResponse()->getStatusCode());
        
        $responseData = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals('Booking created successfully', $responseData['message']);
        
        $booking = $responseData['booking'];
        $this->assertEquals($user['id'], $booking['user_id']);
        $this->assertEquals($house['id'], $booking['house_id']);
    }

    public function testCreateBookingWithInvalidUser(): void
    {
        $client = static::createClient();
        
        $house = $this->createTestHouse($client);

        $client->request(
            'POST',
            '/api/bookings',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'user_id' => 999999,
                'house_id' => $house['id'],
                'comment' => 'Test Booking'
            ])
        );

        $this->assertEquals(400, $client->getResponse()->getStatusCode());
    }

    public function testUpdateBookingComment(): void
    {
        $client = static::createClient();
        
        $user = $this->createTestUser($client);
        $house = $this->createTestHouse($client);

        $client->request(
            'POST',
            '/api/bookings',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'user_id' => $user['id'],
                'house_id' => $house['id'],
                'comment' => 'Original Comment ' . uniqid()
            ])
        );
        
        $bookingData = json_decode($client->getResponse()->getContent(), true);
        $bookingId = $bookingData['booking']['id'];

        $client->request(
            'PUT',
            "/api/bookings/{$bookingId}",
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'comment' => 'Updated Comment ' . uniqid()
            ])
        );

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        
        $responseData = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals('Booking updated successfully', $responseData['message']);
    }

    public function testGetBookingById(): void
    {
        $client = static::createClient();
        
        $user = $this->createTestUser($client);
        $house = $this->createTestHouse($client);

        $client->request(
            'POST',
            '/api/bookings',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'user_id' => $user['id'],
                'house_id' => $house['id'],
                'comment' => 'Get Test Booking ' . uniqid()
            ])
        );
        
        $bookingData = json_decode($client->getResponse()->getContent(), true);
        $bookingId = $bookingData['booking']['id'];

        $client->request('GET', "/api/bookings/{$bookingId}");

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        
        $responseData = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('booking', $responseData);
        $this->assertEquals($bookingId, $responseData['booking']['id']);
    }

    public function testDeleteBooking(): void
    {
        $client = static::createClient();
        
        $user = $this->createTestUser($client);
        $house = $this->createTestHouse($client);

        $client->request(
            'POST',
            '/api/bookings',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'user_id' => $user['id'],
                'house_id' => $house['id'],
                'comment' => 'Delete Test Booking ' . uniqid()
            ])
        );
        
        $bookingData = json_decode($client->getResponse()->getContent(), true);
        $bookingId = $bookingData['booking']['id'];

        $client->request('DELETE', "/api/bookings/{$bookingId}");

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        
        $responseData = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals('Booking deleted successfully', $responseData['message']);

        $client->request('GET', "/api/bookings/{$bookingId}");
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }
}
