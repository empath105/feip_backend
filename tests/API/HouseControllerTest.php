<?php

declare(strict_types=1);

namespace App\tests\API;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class HouseControllerTest extends WebTestCase
{
    public function testCreateHouse(): void
    {
        $client = static::createClient();

        $client->request(
            'POST',
            '/api/houses',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'name' => 'API Test House',
                'beds' => 3,
                'amenities' => 'WiFi, TV, Kitchen',
                'distance_to_sea' => 2,
                'price_per_night' => 4500,
            ])
        );

        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(201);

        $responseData = json_decode($client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('message', $responseData);
        $this->assertArrayHasKey('house', $responseData);
        $this->assertEquals('House created successfully', $responseData['message']);
        $this->assertEquals('API Test House', $responseData['house']['name']);
    }

    public function testGetAvailableHouses(): void
    {
        $client = static::createClient();

        $client->request('GET', '/api/houses/available');

        $this->assertResponseIsSuccessful();

        $responseData = json_decode($client->getResponse()->getContent(), true);
        $this->assertIsArray($responseData);
    }

    public function testGetHouseById(): void
    {
        $client = static::createClient();

        $client->request(
            'POST',
            '/api/houses',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'name' => 'Get Test House',
                'beds' => 2,
                'amenities' => 'None',
                'distance_to_sea' => 1,
                'price_per_night' => 3000,
            ])
        );

        $createResponse = json_decode($client->getResponse()->getContent(), true);
        $houseId = $createResponse['house']['id'];

        $client->request('GET', "/api/houses/{$houseId}");

        $this->assertResponseIsSuccessful();

        $responseData = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals('Get Test House', $responseData['name']);
    }

    public function testGetHouseBookings(): void
    {
        $client = static::createClient();

        $client->request(
            'POST',
            '/api/houses',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'name' => 'Bookings House',
                'beds' => 2,
                'amenities' => 'WiFi, Kitchen',
                'distance_to_sea' => 1,
                'price_per_night' => 3500,
            ])
        );
        $houseData = json_decode($client->getResponse()->getContent(), true);
        $houseId = $houseData['house']['id'];

        $client->request('GET', "/api/houses/{$houseId}/bookings");

        $this->assertResponseIsSuccessful();

        $responseData = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('bookings', $responseData);
        $this->assertIsArray($responseData['bookings']);
    }

    public function testDeleteHouse(): void
    {
        $client = static::createClient();

        $client->request(
            'POST',
            '/api/houses',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'name' => 'Delete Test House',
                'beds' => 1,
                'amenities' => 'WiFi',
                'distance_to_sea' => 3,
                'price_per_night' => 2000,
            ])
        );
        $houseData = json_decode($client->getResponse()->getContent(), true);
        $houseId = $houseData['house']['id'];

        $client->request('DELETE', "/api/houses/{$houseId}");

        $this->assertResponseIsSuccessful();

        $responseData = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals('House deleted successfully', $responseData['message']);

        $client->request('GET', "/api/houses/{$houseId}");
        $this->assertResponseStatusCodeSame(400);
    }
}
