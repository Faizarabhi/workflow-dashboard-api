<?php

namespace App\Tests\Auth;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class RegisterTest extends WebTestCase
{
    public function testUserCanRegister(): void
    {
        $client = static::createClient();

        $client->request(
            'POST',
            '/api/register',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode([
                'email' => 'user1@example.com',
                'password' => 'password123',
            ], JSON_THROW_ON_ERROR)
        );

        $this->assertResponseStatusCodeSame(201);
        $this->assertResponseHeaderSame('content-type', 'application/json');
    }

    public function testRegisterFailsWithMissingFields(): void
    {
        $client = static::createClient();

        $client->request(
            'POST',
            '/api/register',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode([
                'email' => 'user2@example.com',
                // missing password
            ], JSON_THROW_ON_ERROR)
        );

        $this->assertResponseStatusCodeSame(400);
    }

    public function testRegisterFailsWhenEmailAlreadyExists(): void
    {
        $client = static::createClient();

        // First register
        $client->request(
            'POST',
            '/api/register',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode([
                'email' => 'dup@example.com',
                'password' => 'password123',
            ], JSON_THROW_ON_ERROR)
        );
        $this->assertResponseStatusCodeSame(201);

        // Second register with same email
        $client->request(
            'POST',
            '/api/register',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode([
                'email' => 'dup@example.com',
                'password' => 'password123',
            ], JSON_THROW_ON_ERROR)
        );

        // We want a proper API behavior
        $this->assertResponseStatusCodeSame(409);
    }
}
