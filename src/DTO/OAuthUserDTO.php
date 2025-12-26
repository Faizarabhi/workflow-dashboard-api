<?php

namespace App\DTO;

class OAuthUserDTO
{
    public function __construct(
        public string $provider,
        public string $providerUserId,
        public string $email,
        public ?string $name = null
    ) {}
}
