<?php

namespace App\Handler;

use App\DTO\OAuthUserDTO;
use App\Entity\User;
use App\Entity\OAuthAccount;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;

final class OAuthLoginHandler
{
    public function __construct(
        private EntityManagerInterface $em,
        private JWTTokenManagerInterface $jwtManager
    ) {}

    public function handle(OAuthUserDTO $oauthUser): string
    {
        
        $oauthAccount = $this->em->getRepository(OAuthAccount::class)->findOneBy([
            'provider' => $oauthUser->provider,
            'providerUserId' => $oauthUser->providerUserId,
        ]);

        if ($oauthAccount) {
            $user = $oauthAccount->getUser();
        } else {

            $user = $this->em->getRepository(User::class)
                ->findOneBy(['email' => $oauthUser->email]);

            if (!$user) {
                $user = new User();
                $user->setEmail($oauthUser->email);
                $user->setRoles(['ROLE_USER']);
                $this->em->persist($user);
            }

            // 3️⃣ كنربطو OAuthAccount مع User
            $oauthAccount = new OAuthAccount();
            $oauthAccount->setProvider($oauthUser->provider);
            $oauthAccount->setProviderUserId($oauthUser->providerUserId);
            $oauthAccount->setUser($user);

            $this->em->persist($oauthAccount);
        }

        $this->em->flush();

        return $this->jwtManager->create($user);
    }
}
