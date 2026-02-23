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

#[Route('/api/admin')]
final class AdminController extends AbstractController
{
    #[Route('/users', name: 'api_admin_users_list', methods: ['GET'])]
    public function listUsers(UserRepository $userRepository): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $users = $userRepository->findBy([], ['id' => 'DESC']);
        $data = array_map(fn(User $u) => [
            'id' => $u->getId(),
            'email' => $u->getEmail(),
            'roles' => $u->getRoles(),
        ], $users);

        return $this->json(['users' => $data], 200);
    }

    #[Route('/new', name: 'api_admin_new_user', methods: ['POST'])]
    public function newUser(
        Request $request,
        UserRepository $userRepository,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $em
    ): JsonResponse {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $payload = json_decode($request->getContent(), true);

        if (!is_array($payload)) {
            return $this->json(['error' => 'JSON invalide'], 400);
        }

        $email = $payload['email'] ?? null;
        $password = $payload['password'] ?? null;
        $roles = $payload['roles'] ?? ['ROLE_USER'];

        if (!$email || !$password) {
            return $this->json(['error' => 'email et password sont obligatoires'], 400);
        }

        if ($userRepository->findOneBy(['email' => $email])) {
            return $this->json(['error' => 'Cet email existe déjà'], 409);
        }

        $user = new User();
        $user->setEmail($email);
        $user->setRoles($roles);
        $user->setPassword($passwordHasher->hashPassword($user, $password));

        $em->persist($user);
        $em->flush();

        return $this->json([
            'message' => 'Utilisateur créé',
            'user' => [
                'id' => $user->getId(),
                'email' => $user->getEmail(),
                'roles' => $user->getRoles(),
            ]
        ], 201);
    }

    #[Route('/users/{id}', name: 'api_admin_delete_user', methods: ['DELETE'])]
    public function deleteUser(
        User $user,
        EntityManagerInterface $em
    ): JsonResponse {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        // Empêcher l'admin de se supprimer lui-même
        if ($user === $this->getUser()) {
            return $this->json([
                'error' => 'Vous ne pouvez pas supprimer votre propre compte'
            ], 400);
        }

        $em->remove($user);
        $em->flush();

        return $this->json([
            'message' => 'Utilisateur supprimé'
        ], 200);
    }
}


