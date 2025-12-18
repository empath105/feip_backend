<?php

declare(strict_types=1);

namespace App\Tests\API;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AuthControllerTest extends WebTestCase
{
    private function createTestUser($client): array
    {
        $uniquePhone = '+7999' . rand(1000000, 9999999);
        $uniqueEmail = 'auth-test-' . uniqid() . '@example.com';
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
                'name' => 'Auth Test User',
                'password' => $password,
            ])
        );

        return [
            'phone' => $uniquePhone,
            'email' => $uniqueEmail,
            'password' => $password,
            'user' => json_decode($client->getResponse()->getContent(), true)['user'],
        ];
    }

    private function loginAndGetToken($client, $phone, $password): string
    {
        $client->request(
            'POST',
            '/api/login',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'phone' => $phone,
                'password' => $password,
            ])
        );

        $responseData = json_decode($client->getResponse()->getContent(), true);

        return $responseData['token'];
    }

    public function testLoginSuccess(): void
    {
        $client = static::createClient();

        $userData = $this->createTestUser($client);

        $client->request(
            'POST',
            '/api/login',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'phone' => $userData['phone'],
                'password' => $userData['password'],
            ])
        );

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $responseData = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals('Login successful', $responseData['message']);
        $this->assertArrayHasKey('token', $responseData);
        $this->assertEquals('Bearer', $responseData['token_type']);
        $this->assertArrayHasKey('expires_at', $responseData);
        $this->assertArrayHasKey('user', $responseData);
        $this->assertEquals($userData['phone'], $responseData['user']['phone']);
    }

    public function testLoginWithWrongPassword(): void
    {
        $client = static::createClient();

        $userData = $this->createTestUser($client);

        $client->request(
            'POST',
            '/api/login',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'phone' => $userData['phone'],
                'password' => 'wrongpassword',
            ])
        );

        $this->assertEquals(401, $client->getResponse()->getStatusCode());
    }

    public function testLoginWithNonExistentUser(): void
    {
        $client = static::createClient();

        $client->request(
            'POST',
            '/api/login',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'phone' => '+79990000000',
                'password' => 'anypassword',
            ])
        );

        $this->assertEquals(401, $client->getResponse()->getStatusCode());
    }

    public function testLoginMissingCredentials(): void
    {
        $client = static::createClient();

        $client->request(
            'POST',
            '/api/login',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'password' => 'test123',
            ])
        );

        $this->assertEquals(400, $client->getResponse()->getStatusCode());

        $client->request(
            'POST',
            '/api/login',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'phone' => '+79991234567',
            ])
        );

        $this->assertEquals(400, $client->getResponse()->getStatusCode());
    }

    public function testProfileEndpointSuccess(): void
    {
        $client = static::createClient();

        $userData = $this->createTestUser($client);

        $token = $this->loginAndGetToken($client, $userData['phone'], $userData['password']);

        $client->request(
            'GET',
            '/api/profile',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $token]
        );

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $profileData = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('user', $profileData);
        $this->assertEquals($userData['phone'], $profileData['user']['phone']);
    }

    public function testProfileEndpointWithoutToken(): void
    {
        $client = static::createClient();

        $client->request('GET', '/api/profile');

        $this->assertEquals(401, $client->getResponse()->getStatusCode());
    }

    public function testProfileEndpointWithInvalidToken(): void
    {
        $client = static::createClient();

        $client->request(
            'GET',
            '/api/profile',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer invalid_token_here']
        );

        $this->assertEquals(401, $client->getResponse()->getStatusCode());
    }

    public function testLogoutSuccess(): void
    {
        $client = static::createClient();

        $userData = $this->createTestUser($client);

        $token = $this->loginAndGetToken($client, $userData['phone'], $userData['password']);

        $client->request(
            'GET',
            '/api/profile',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $token]
        );
        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $client->request(
            'POST',
            '/api/logout',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $token]
        );

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $logoutData = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals('Logout successful', $logoutData['message']);

        $client->request(
            'GET',
            '/api/profile',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $token]
        );

        $this->assertEquals(401, $client->getResponse()->getStatusCode());
    }
}
