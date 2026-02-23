<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\User\UserInterface;

final class MeController extends AbstractController
{
    #[Route('/api/me', name: 'api_me', methods: ['GET'])]
    public function me(?UserInterface $user): JsonResponse
    {
        if (!$user) {
            return $this->json(['message' => 'Not authenticated'], 401);
        }

        return $this->json([
            'email' => $user->getUserIdentifier(),
            'roles' => $user->getRoles(),
        ]);
    }
}
