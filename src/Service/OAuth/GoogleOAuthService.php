<?php

namespace App\Service\OAuth;

use App\DTO\OAuthUserDTO;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use League\OAuth2\Client\Provider\GoogleUser;

class GoogleOAuthService
{
    public function __construct(
        private ClientRegistry $clientRegistry
    ) {}

    public function fetchUser(): OAuthUserDTO
    {
        $client = $this->clientRegistry->getClient('google');

        /** @var GoogleUser $googleUser */
        $googleUser = $client->fetchUser();

        return new OAuthUserDTO(
            provider: 'google',
            providerUserId: $googleUser->getId(),
            email: $googleUser->getEmail(),
            name: $googleUser->getName()
        );
    }
}
