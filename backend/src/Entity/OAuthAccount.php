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

    public function setProvider(string $provider): self
{
    $this->provider = $provider;
    return $this;
}

public function setProviderUserId(string $providerUserId): self
{
    $this->providerUserId = $providerUserId;
    return $this;
}

public function setUser(User $user): self
{
    $this->user = $user;
    return $this;
}

public function getUser(): ?User
{
    return $this->user;
}

public function getProvider(): ?string
{
    return $this->provider;
}

public function getProviderUserId(): ?string
{
    return $this->providerUserId;
}

}