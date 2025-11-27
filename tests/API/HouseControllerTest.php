<?php

declare(strict_types=1);

namespace App\tests\API;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class HouseControllerTest extends WebTestCase
{
    private function createUserAndGetToken($client): string
    {
        $uniqueEmail = 'house-test-' . uniqid() . '@example.com';
        $uniquePhone = '+7999' . rand(1000000, 9999999);
        $password = 'test123';

        $client->request(
            'POST',
            '/api/users',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'phone' => $uniquePhone,
                'email' => $uniqueEmail,
                'name' => 'House Test User',
                'password' => $password,
            ])
        );

        $this->assertResponseStatusCodeSame(201, 'Failed to create user');

        $client->request(
            'POST',
            '/api/login',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'phone' => $uniquePhone,
                'password' => $password,
            ])
        );

        $this->assertResponseIsSuccessful('Login failed');

        $loginData = json_decode($client->getResponse()->getContent(), true);

        return $loginData['token'];
    }

    public function testCreateHouse(): void
    {
        $client = static::createClient();

        $token = $this->createUserAndGetToken($client);

        $client->request(
            'POST',
            '/api/houses',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
            ],
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

    public function testCreateHouseWithoutToken(): void
    {
        $client = static::createClient();

        $client->request(
            'POST',
            '/api/houses',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'name' => 'Unauthorized House',
                'beds' => 2,
                'distance_to_sea' => 1,
                'price_per_night' => 3000,
            ])
        );

        $this->assertResponseStatusCodeSame(401, 'Should require token for creating house');
    }

    public function testGetAvailableHouses(): void
    {
        $client = static::createClient();

        $token = $this->createUserAndGetToken($client);

        $client->request(
            'GET',
            '/api/houses/available',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $token]
        );

        $this->assertResponseIsSuccessful();

        $responseData = json_decode($client->getResponse()->getContent(), true);
        $this->assertIsArray($responseData);
    }

    public function testGetHouseById(): void
    {
        $client = static::createClient();

        $token = $this->createUserAndGetToken($client);

        $client->request(
            'POST',
            '/api/houses',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
            ],
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

        $client->request(
            'GET',
            "/api/houses/{$houseId}",
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $token]
        );

        $this->assertResponseIsSuccessful();

        $responseData = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals('Get Test House', $responseData['name']);
    }

    public function testGetHouseBookings(): void
    {
        $client = static::createClient();

        $token = $this->createUserAndGetToken($client);

        $client->request(
            'POST',
            '/api/houses',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
            ],
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

        $client->request(
            'GET',
            "/api/houses/{$houseId}/bookings",
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $token]
        );

        $this->assertResponseIsSuccessful();

        $responseData = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('bookings', $responseData);
        $this->assertIsArray($responseData['bookings']);
    }

    public function testDeleteHouse(): void
    {
        $client = static::createClient();

        $token = $this->createUserAndGetToken($client);

        $client->request(
            'POST',
            '/api/houses',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
            ],
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

        $client->request(
            'DELETE',
            "/api/houses/{$houseId}",
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $token]
        );

        $this->assertResponseIsSuccessful();

        $responseData = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals('House deleted successfully', $responseData['message']);

        $client->request(
            'GET',
            "/api/houses/{$houseId}",
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $token]
        );

        $this->assertResponseStatusCodeSame(400);
    }
}
