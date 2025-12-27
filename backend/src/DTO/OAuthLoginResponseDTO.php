<?php

namespace App\DTO;

class OAuthLoginResponseDTO
{
    public function __construct(
        public readonly string $token
    ) {}
}
