<?php

namespace App\Tests\Controller;

use App\Entity\User;
use App\Entity\OAuthAccount;
use Doctrine\ORM\EntityManagerInterface;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class GoogleOAuthControllerTest extends WebTestCase
{
    public function testGoogleCallbackCreatesUserAndOauthAccount(): void
    {
        $client = static::createClient(['environment' => 'test']);
        $container = static::getContainer();

        // Fake google user
        $googleUser = new class {
            public function getEmail(): string { return 'test.user@gmail.com'; }
            public function getId(): string { return 'google-123'; }
            public function toArray(): array { return ['email' => 'test.user@gmail.com', 'sub' => 'google-123']; }
        };

        // Fake oauth client
        $oauthClient = new class($googleUser) {
            public function __construct(private $googleUser) {}
            public function fetchUser() { return $this->googleUser; }
        };

        // Mock ClientRegistry
        $registryMock = $this->createMock(ClientRegistry::class);
        $registryMock->method('getClient')->with('google')->willReturn($oauthClient);

        // Mock JWT (باش ما نحتاجوش private/public keys)
        $jwtMock = $this->createMock(JWTTokenManagerInterface::class);
        $jwtMock->method('create')->willReturn('fake-jwt');

        $container->set(ClientRegistry::class, $registryMock);
        $container->set(JWTTokenManagerInterface::class, $jwtMock);

        $client->request('GET', '/api/oauth/google/callback');

        $this->assertResponseIsSuccessful();
        $data = json_decode($client->getResponse()->getContent(), true);

        $this->assertSame('fake-jwt', $data['token']);
        $this->assertSame('test.user@gmail.com', $data['user']['email']);
        $this->assertSame('google-123', $data['oauth']['provider_user_id']);

        /** @var EntityManagerInterface $em */
        $em = $container->get(EntityManagerInterface::class);

        $user = $em->getRepository(User::class)->findOneBy(['email' => 'test.user@gmail.com']);
        $this->assertNotNull($user);

        $oauth = $em->getRepository(OAuthAccount::class)->findOneBy([
            'provider' => 'google',
            'providerUserId' => 'google-123',
        ]);
        $this->assertNotNull($oauth);
        $this->assertSame($user->getId(), $oauth->getUser()->getId());
    }
}
