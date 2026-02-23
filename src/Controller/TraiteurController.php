<?php

namespace App\Controller;

use App\Entity\Menu;
use App\Entity\QuoteRequest;
use App\Repository\MenuRepository;
use App\Repository\QuoteRequestRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class TraiteurController extends AbstractController
{
    // ===========================
    // MENU - LIST
    // ===========================
    #[Route('/api/traiteur/menus', name: 'api_traiteur_menus_list', methods: ['GET'])]
    public function listMyMenus(MenuRepository $menuRepository): JsonResponse
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $this->denyAccessUnlessGranted('ROLE_TRAITEUR');

        $user = $this->getUser();
        if (!$user) {
            return $this->json(['message' => 'Non authentifié'], 401);
        }

        $menus = $menuRepository->findBy(['traiteur' => $user], ['date_creation' => 'DESC']);
        $data = array_map(fn(Menu $m) => $this->menuToArray($m), $menus);

        return $this->json(['menus' => $data], 200);
    }

    // ===========================
    // MENU - CREATE
    // ===========================
    #[Route('/api/traiteur/menus', name: 'api_traiteur_menus_create', methods: ['POST'])]
    public function createMenu(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $this->denyAccessUnlessGranted('ROLE_TRAITEUR');

        $user = $this->getUser();
        if (!$user) {
            return $this->json(['message' => 'Non authentifié'], 401);
        }

        $payload = json_decode($request->getContent(), true);
        if (!is_array($payload)) {
            return $this->json(['message' => 'JSON invalide'], 400);
        }

        $errors = $this->validateMenuPayload($payload, true);
        if (!empty($errors)) {
            return $this->json(['errors' => $errors], 422);
        }

        $menu = new Menu();
        $menu->setTitre(trim((string) $payload['titre']));
        $menu->setDescription(array_key_exists('description', $payload) ? ($payload['description'] !== null ? (string) $payload['description'] : null) : null);
        $menu->setPrixParPersonne((float) $payload['prix_par_personne']);
        $menu->setNombreMinPersonnes((int) $payload['nombre_min_personnes']);
        $menu->setNombreMaxPersonnes((int) $payload['nombre_max_personnes']);
        $menu->setTraiteur($user);

        $em->persist($menu);
        $em->flush();

        return $this->json(['menu' => $this->menuToArray($menu)], 201);
    }

    // ===========================
    // MENU - UPDATE
    // ===========================
    #[Route('/api/traiteur/menus/{id}', name: 'api_traiteur_menus_update', methods: ['PUT'])]
    public function updateMenu(Menu $menu, Request $request, EntityManagerInterface $em): JsonResponse
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $this->denyAccessUnlessGranted('ROLE_TRAITEUR');

        $user = $this->getUser();
        if (!$user) {
            return $this->json(['message' => 'Non authentifié'], 401);
        }

        // Seul le propriétaire peut modifier
        if (!$menu->getTraiteur() || $menu->getTraiteur()->getId() !== $user->getId()) {
            return $this->json(['message' => 'Accès refusé (menu non propriétaire)'], 403);
        }

        $payload = json_decode($request->getContent(), true);
        if (!is_array($payload)) {
            return $this->json(['message' => 'JSON invalide'], 400);
        }

        $errors = $this->validateMenuPayload($payload, false);
        if (!empty($errors)) {
            return $this->json(['errors' => $errors], 422);
        }

        if (array_key_exists('titre', $payload)) {
            $menu->setTitre(trim((string) $payload['titre']));
        }
        if (array_key_exists('description', $payload)) {
            $menu->setDescription($payload['description'] !== null ? (string) $payload['description'] : null);
        }
        if (array_key_exists('prix_par_personne', $payload)) {
            $menu->setPrixParPersonne((float) $payload['prix_par_personne']);
        }
        if (array_key_exists('nombre_min_personnes', $payload)) {
            $menu->setNombreMinPersonnes((int) $payload['nombre_min_personnes']);
        }
        if (array_key_exists('nombre_max_personnes', $payload)) {
            $menu->setNombreMaxPersonnes((int) $payload['nombre_max_personnes']);
        }

        $em->flush();

        return $this->json(['menu' => $this->menuToArray($menu)], 200);
    }

    // ===========================
    // MENU - DELETE
    // ===========================
    #[Route('/api/traiteur/menus/{id}', name: 'api_traiteur_menus_delete', methods: ['DELETE'])]
    public function deleteMenu(Menu $menu, EntityManagerInterface $em): JsonResponse
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $this->denyAccessUnlessGranted('ROLE_TRAITEUR');

        $user = $this->getUser();
        if (!$user) {
            return $this->json(['message' => 'Non authentifié'], 401);
        }

        // Seul le propriétaire peut supprimer
        if (!$menu->getTraiteur() || $menu->getTraiteur()->getId() !== $user->getId()) {
            return $this->json(['message' => 'Accès refusé (menu non propriétaire)'], 403);
        }

        $em->remove($menu);
        $em->flush();

        return $this->json(['message' => 'Menu supprimé'], 200);
    }

    // ===========================
    // QUOTE REQUEST - LIST
    // ===========================
    #[Route('/api/traiteur/quote-requests', name: 'api_traiteur_quote_requests_list', methods: ['GET'])]
    public function listQuoteRequests(QuoteRequestRepository $quoteRequestRepository): JsonResponse
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $this->denyAccessUnlessGranted('ROLE_TRAITEUR');

        $user = $this->getUser();
        if (!$user) {
            return $this->json(['message' => 'Non authentifié'], 401);
        }

        $requests = $quoteRequestRepository->findBy(['traiteur' => $user], ['createdAt' => 'DESC']);

        $data = array_map(fn(QuoteRequest $q) => [
            'id' => $q->getId(),
            'client' => [
                'id' => $q->getClient()->getId(),
                'email' => $q->getClient()->getEmail(),
            ],
            'menu' => [
                'id' => $q->getMenu()->getId(),
                'titre' => $q->getMenu()->getTitre(),
            ],
            'status' => $q->getStatus(),
            'createdAt' => $q->getCreatedAt()->format('c'),
        ], $requests);

        return $this->json(['quote_requests' => $data], 200);
    }

    // ===========================
    // QUOTE REQUEST - ACCEPT
    // ===========================
    #[Route('/api/traiteur/quote-requests/{id}/accept', name: 'api_traiteur_quote_accept', methods: ['PUT'])]
    public function acceptQuoteRequest(QuoteRequest $quoteRequest, EntityManagerInterface $em): JsonResponse
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $this->denyAccessUnlessGranted('ROLE_TRAITEUR');

        $user = $this->getUser();
        if (!$user) {
            return $this->json(['message' => 'Non authentifié'], 401);
        }

        if ($quoteRequest->getTraiteur()->getId() !== $user->getId()) {
            return $this->json(['message' => 'Accès refusé'], 403);
        }

        $quoteRequest->setStatus('ACCEPTED');
        $em->flush();

        return $this->json(['message' => 'Demande acceptée'], 200);
    }

    // ===========================
    // QUOTE REQUEST - REJECT
    // ===========================
    #[Route('/api/traiteur/quote-requests/{id}/reject', name: 'api_traiteur_quote_reject', methods: ['PUT'])]
    public function rejectQuoteRequest(QuoteRequest $quoteRequest, EntityManagerInterface $em): JsonResponse
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $this->denyAccessUnlessGranted('ROLE_TRAITEUR');

        $user = $this->getUser();
        if (!$user) {
            return $this->json(['message' => 'Non authentifié'], 401);
        }

        if ($quoteRequest->getTraiteur()->getId() !== $user->getId()) {
            return $this->json(['message' => 'Accès refusé'], 403);
        }

        $quoteRequest->setStatus('REJECTED');
        $em->flush();

        return $this->json(['message' => 'Demande refusée'], 200);
    }

    // ===========================
    // HELPERS
    // ===========================
    private function menuToArray(Menu $m): array
    {
        $traiteur = $m->getTraiteur();

        return [
            'id' => $m->getId(),
            'titre' => $m->getTitre(),
            'description' => $m->getDescription(),
            'prix_par_personne' => $m->getPrixParPersonne(),
            'nombre_min_personnes' => $m->getNombreMinPersonnes(),
            'nombre_max_personnes' => $m->getNombreMaxPersonnes(),
            'date_creation' => $m->getDateCreation()?->format('c'),
            'date_modification' => $m->getDateModification()?->format('c'),
            'traiteur' => $traiteur ? [
                'id' => $traiteur->getId(),
                'email' => method_exists($traiteur, 'getEmail') ? $traiteur->getEmail() : null,
            ] : null,
        ];
    }

    /**
     * @return array<string>
     */
    private function validateMenuPayload(array $payload, bool $isCreate): array
    {
        $errors = [];

        $required = ['titre', 'prix_par_personne', 'nombre_min_personnes', 'nombre_max_personnes'];
        if ($isCreate) {
            foreach ($required as $field) {
                if (!array_key_exists($field, $payload)) {
                    $errors[] = "Champ obligatoire manquant : $field";
                }
            }
        }

        if (array_key_exists('titre', $payload)) {
            $titre = trim((string) $payload['titre']);
            if ($titre === '' || mb_strlen($titre) > 255) {
                $errors[] = "Le champ 'titre' est invalide";
            }
        }

        if (array_key_exists('prix_par_personne', $payload)) {
            $prix = (float) $payload['prix_par_personne'];
            if ($prix <= 0) {
                $errors[] = "Le champ 'prix_par_personne' doit être > 0";
            }
        }

        if (array_key_exists('nombre_min_personnes', $payload)) {
            $min = (int) $payload['nombre_min_personnes'];
            if ($min <= 0) {
                $errors[] = "Le champ 'nombre_min_personnes' doit être > 0";
            }
        }

        if (array_key_exists('nombre_max_personnes', $payload)) {
            $max = (int) $payload['nombre_max_personnes'];
            if ($max <= 0) {
                $errors[] = "Le champ 'nombre_max_personnes' doit être > 0";
            }
        }

        if (array_key_exists('nombre_min_personnes', $payload) && array_key_exists('nombre_max_personnes', $payload)) {
            $min = (int) $payload['nombre_min_personnes'];
            $max = (int) $payload['nombre_max_personnes'];
            if ($min > $max) {
                $errors[] = "nombre_min_personnes ne peut pas être supérieur à nombre_max_personnes";
            }
        }

        return $errors;
    }
}