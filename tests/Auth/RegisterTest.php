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
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'email' => 'user'.uniqid().'@example.com',
                'password' => 'password123'
            ])
        );

        $this->assertResponseStatusCodeSame(201);
    }

    public function testRegisterFailsWithMissingFields(): void
    {
        $client = static::createClient();

        $client->request(
            'POST',
            '/api/register',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'email' => ''
            ])
        );

        $this->assertResponseStatusCodeSame(400);
    }
}