<?php

namespace App\Controller\Auth;

use App\Entity\User;
use App\Entity\OAuthAccount;
use Doctrine\ORM\EntityManagerInterface;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class GoogleOAuthController extends AbstractController
{
    #[Route('/api/oauth/google', name: 'google_oauth_start', methods: ['GET'])]
    public function connect(ClientRegistry $clientRegistry): RedirectResponse
    {
        return $clientRegistry
            ->getClient('google')
            ->redirect(['email', 'profile'], []);
    }

    #[Route('/api/oauth/google/callback', name: 'google_oauth_callback', methods: ['GET'])]
    public function callback(
        ClientRegistry $clientRegistry,
        EntityManagerInterface $em,
        JWTTokenManagerInterface $jwtManager,
        UserPasswordHasherInterface $passwordHasher
    ): JsonResponse {
        // 1) Fetch Google user
        $googleUser = $clientRegistry->getClient('google')->fetchUser();

        $payload = method_exists($googleUser, 'toArray') ? $googleUser->toArray() : [];

        $email = method_exists($googleUser, 'getEmail')
            ? $googleUser->getEmail()
            : ($payload['email'] ?? null);

        // Google unique id غالباً هو "sub"
        $googleId = (method_exists($googleUser, 'getId') ? $googleUser->getId() : null)
            ?? ($payload['sub'] ?? $payload['id'] ?? null);

        if (!$email) {
            return $this->json(['message' => 'Google email not found', 'payload' => $payload], 400);
        }

        if (!$googleId) {
            return $this->json(['message' => 'Google id not found', 'payload' => $payload], 400);
        }

        // 2) Find or create local user
        $user = $em->getRepository(User::class)->findOneBy(['email' => $email]);

        if (!$user) {
            $user = new User();
            $user->setEmail($email);
            $user->setRoles(['ROLE_USER']);

            // DB column is NOT NULL, OAuth doesn’t use it
            $randomPassword = bin2hex(random_bytes(16));
            $user->setPassword($passwordHasher->hashPassword($user, $randomPassword));

            $em->persist($user);
            $em->flush(); // get id
        }

        // 3) Link oauth_account (provider + providerUserId)
        $oauthRepo = $em->getRepository(OAuthAccount::class);

        $oauthAccount = $oauthRepo->findOneBy([
            'provider' => 'google',
            'providerUserId' => (string) $googleId,
        ]);

        if (!$oauthAccount) {
            $oauthAccount = new OAuthAccount();
            $oauthAccount->setProvider('google');
            $oauthAccount->setProviderUserId((string) $googleId);
            $oauthAccount->setUser($user);

            $em->persist($oauthAccount);
        } else {
            // ensure linked user
            if ($oauthAccount->getUser()?->getId() !== $user->getId()) {
                $oauthAccount->setUser($user);
            }
        }

        $em->flush();

        // 4) JWT
        $token = $jwtManager->create($user);

        return $this->json([
            'token' => $token,
            'provider' => 'google',
            'user' => [
                'id' => $user->getId(),
                'email' => $user->getEmail(),
                'roles' => $user->getRoles(),
            ],
            'oauth' => [
                'provider' => 'google',
                'provider_user_id' => (string) $googleId,
            ],
        ]);
    }
}
