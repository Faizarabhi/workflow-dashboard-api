<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class OAuthAccount
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private string $provider;

    #[ORM\Column(length: 255)]
    private string $providerUserId;

    #[ORM\ManyToOne(inversedBy: 'oauthAccounts')]
    #[ORM\JoinColumn(nullable: false)]
    private User $user;

    // getters/setters
}
