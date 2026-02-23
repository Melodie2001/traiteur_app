<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

final class AuthController extends AbstractController
{
    #[Route('/api/register', name: 'api_register', methods: ['POST'])]
    public function register(
        Request $request,
        EntityManagerInterface $em,
        UserRepository $userRepository,
        UserPasswordHasherInterface $passwordHasher
    ): JsonResponse {
        $payload = json_decode($request->getContent(), true);
        if (!is_array($payload)) {
            return $this->json(['message' => 'JSON invalide'], 400);
        }

        $email = isset($payload['email']) ? trim((string) $payload['email']) : '';
        $password = isset($payload['password']) ? (string) $payload['password'] : '';

        $errors = [];
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Email invalide';
        }
        if ($password === '' || mb_strlen($password) < 6) {
            $errors[] = 'Mot de passe invalide (min 6 caractères)';
        }
        if (!empty($errors)) {
            return $this->json(['errors' => $errors], 422);
        }

        if ($userRepository->findOneBy(['email' => $email])) {
            return $this->json(['message' => 'Cet email est déjà utilisé'], 409);
        }

        $user = new User();
        $user->setEmail($email);

        
        $user->setRoles([]);

        $user->setPassword($passwordHasher->hashPassword($user, $password));

        $em->persist($user);
        $em->flush();

        return $this->json([
            'message' => 'Compte créé',
            'user' => [
                'id' => $user->getId(),
                'email' => $user->getEmail(),
                'roles' => $user->getRoles(),
            ],
        ], 201);
    }

    #[Route('/api/me', name: 'api_me', methods: ['GET'])]
    public function me(): JsonResponse
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        /** @var User|null $user */
        $user = $this->getUser();
        if (!$user) {
            return $this->json(['message' => 'Non authentifié'], 401);
        }

        return $this->json([
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'roles' => $user->getRoles(),
        ], 200);
    }
}


