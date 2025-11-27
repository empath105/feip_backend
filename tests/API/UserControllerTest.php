<?php

declare(strict_types=1);

namespace App\Tests\API;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class UserControllerTest extends WebTestCase
{
    public function testCreateUser(): void
    {
        $client = static::createClient();

        $uniqueEmail = 'test-user-' . uniqid() . '@example.com';
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
                'name' => 'Test User',
                'password' => 'test123',
            ])
        );

        $this->assertEquals(201, $client->getResponse()->getStatusCode());

        $responseData = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals('User created successfully', $responseData['message']);
        $this->assertEquals($uniqueEmail, $responseData['user']['email']);
    }

    public function testCreateUserWithDuplicateEmail(): void
    {
        $client = static::createClient();

        $email = 'duplicate-test-' . uniqid() . '@example.com';

        $client->request(
            'POST',
            '/api/users',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'email' => $email,
                'phone' => '+7999' . rand(1000000, 9999999),
                'name' => 'First User',
                'password' => 'test123',
            ])
        );
        $this->assertEquals(201, $client->getResponse()->getStatusCode());

        $client->request(
            'POST',
            '/api/users',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'email' => $email,
                'phone' => '+7999' . rand(1000000, 9999999),
                'name' => 'Second User',
                'password' => 'test123',
            ])
        );

        $this->assertEquals(400, $client->getResponse()->getStatusCode());
    }

    public function testGetAllUsers(): void
    {
        $client = static::createClient();

        $uniqueEmail = 'test-getall-' . uniqid() . '@example.com';
        $uniquePhone = '+7999' . rand(1000000, 9999999);
        $password = 'test123';

        $client->request(
            'POST',
            '/api/users',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'email' => $uniqueEmail,
                'phone' => $uniquePhone,
                'name' => 'Test Get All User',
                'password' => $password,
            ])
        );

        $this->assertEquals(201, $client->getResponse()->getStatusCode());

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

        $loginData = json_decode($client->getResponse()->getContent(), true);
        $token = $loginData['token'];

        $client->request(
            'GET',
            '/api/users',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $token]
        );

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $responseData = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('users', $responseData);
        $this->assertIsArray($responseData['users']);
    }

    public function testGetUserById(): void
    {
        $client = static::createClient();

        $uniqueEmail = 'get-test-' . uniqid() . '@example.com';
        $uniquePhone = '+7999' . rand(1000000, 9999999);
        $password = 'test123';

        $client->request(
            'POST',
            '/api/users',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'email' => $uniqueEmail,
                'phone' => $uniquePhone,
                'name' => 'Get Test User',
                'password' => $password,
            ])
        );

        $createResponse = json_decode($client->getResponse()->getContent(), true);
        $userId = $createResponse['user']['id'];

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

        $loginData = json_decode($client->getResponse()->getContent(), true);
        $token = $loginData['token'];

        $client->request(
            'GET',
            "/api/users/{$userId}",
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $token]
        );

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $responseData = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('user', $responseData);
        $this->assertEquals($uniqueEmail, $responseData['user']['email']);
    }

    public function testDeleteUser(): void
    {
        $client = static::createClient();

        $uniqueEmail = 'delete-test-' . uniqid() . '@example.com';
        $uniquePhone = '+7999' . rand(1000000, 9999999);
        $password = 'test123';

        $client->request(
            'POST',
            '/api/users',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'email' => $uniqueEmail,
                'phone' => $uniquePhone,
                'name' => 'Delete Test User',
                'password' => $password,
            ])
        );
        $userData = json_decode($client->getResponse()->getContent(), true);
        $userId = $userData['user']['id'];

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

        $loginData = json_decode($client->getResponse()->getContent(), true);
        $token = $loginData['token'];

        $client->request(
            'DELETE',
            "/api/users/{$userId}",
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $token]
        );

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $responseData = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals('User deleted successfully', $responseData['message']);
    }

    public function testAccessProtectedRouteWithoutToken(): void
    {
        $client = static::createClient();

        $client->request('GET', '/api/users');

        $this->assertEquals(401, $client->getResponse()->getStatusCode());
    }

    public function testAccessWithInvalidToken(): void
    {
        $client = static::createClient();

        $client->request(
            'GET',
            '/api/users',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer invalid_token_here']
        );

        $this->assertEquals(401, $client->getResponse()->getStatusCode());
    }
}
