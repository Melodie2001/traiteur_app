<?php

namespace App\Controller;

use App\Entity\Menu;
use App\Entity\QuoteRequest;
use App\Entity\Review;
use App\Entity\User;
use App\Repository\MenuRepository;
use App\Repository\QuoteRequestRepository;
use App\Repository\ReviewRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/client', name: 'api_client_')]
#[IsGranted('ROLE_CLIENT')]
final class ClientController extends AbstractController
{
    // =========================
    // QUOTE REQUESTS (CRUD)
    // =========================

    #[Route('/quote-requests', name: 'quote_requests_list', methods: ['GET'])]
    public function listQuoteRequests(QuoteRequestRepository $repo): JsonResponse
    {
        /** @var User|null $user */
        $user = $this->getUser();
        if (!$user) {
            return $this->json(['message' => 'Non authentifié'], 401);
        }

        // On filtre par client si l'entité a getClient()
        if (!method_exists(QuoteRequest::class, 'getClient')) {
            return $this->json([
                'message' => "Ton entité QuoteRequest doit avoir une relation client (getClient/setClient)."
            ], 500);
        }

        $items = $repo->findBy(['client' => $user], ['id' => 'DESC']);

        $data = array_map(fn(QuoteRequest $q) => $this->quoteRequestToArray($q), $items);

        return $this->json(['quote_requests' => $data], 200);
    }

    #[Route('/quote-requests/{id}', name: 'quote_requests_show', methods: ['GET'])]
    public function showQuoteRequest(QuoteRequest $quoteRequest): JsonResponse
    {
        /** @var User|null $user */
        $user = $this->getUser();
        if (!$user) {
            return $this->json(['message' => 'Non authentifié'], 401);
        }

        if (!method_exists($quoteRequest, 'getClient')) {
            return $this->json([
                'message' => "Ton entité QuoteRequest doit avoir getClient/setClient pour vérifier le propriétaire."
            ], 500);
        }

        $client = $quoteRequest->getClient();
        if (!$client || !method_exists($client, 'getId') || $client->getId() !== $user->getId()) {
            return $this->json(['message' => 'Accès refusé (quote request non propriétaire)'], 403);
        }

        return $this->json(['quote_request' => $this->quoteRequestToArray($quoteRequest)], 200);
    }

    #[Route('/quote-requests', name: 'quote_requests_create', methods: ['POST'])]
    public function createQuoteRequest(
        Request $request,
        MenuRepository $menuRepo,
        EntityManagerInterface $em
    ): JsonResponse {
        /** @var User|null $user */
        $user = $this->getUser();
        if (!$user) {
            return $this->json(['message' => 'Non authentifié'], 401);
        }

        $payload = json_decode($request->getContent(), true);
        if (!is_array($payload)) {
            return $this->json(['message' => 'JSON invalide'], 400);
        }

        $menuId = $payload['menu_id'] ?? null;
        if (!$menuId) {
            return $this->json(['message' => 'menu_id est obligatoire'], 422);
        }

        $menu = $menuRepo->find((int) $menuId);
        if (!$menu) {
            return $this->json(['message' => 'Menu non trouvé'], 404);
        }

        $qr = new QuoteRequest();

        // setClient obligatoire pour sécuriser le CRUD
        if (!method_exists($qr, 'setClient')) {
            return $this->json([
                'message' => "Ton entité QuoteRequest doit avoir setClient(User \$client)."
            ], 500);
        }
        $qr->setClient($user);

        // setMenu si existant
        if (method_exists($qr, 'setMenu')) {
            $qr->setMenu($menu);
        } else {
            return $this->json([
                'message' => "Ton entité QuoteRequest doit avoir setMenu(Menu \$menu) pour lier la demande au menu."
            ], 500);
        }

        // Champs optionnels si tu les as dans l'entité
        if (isset($payload['message']) && method_exists($qr, 'setMessage')) {
            $qr->setMessage((string) $payload['message']);
        }
        if (isset($payload['nb_personnes']) && method_exists($qr, 'setNbPersonnes')) {
            $qr->setNbPersonnes((int) $payload['nb_personnes']);
        }
        if (isset($payload['date_evenement']) && method_exists($qr, 'setDateEvenement')) {
            // si tu utilises DateTimeImmutable par exemple
            $qr->setDateEvenement(new \DateTimeImmutable((string) $payload['date_evenement']));
        }

        $em->persist($qr);
        $em->flush();

        return $this->json(['quote_request' => $this->quoteRequestToArray($qr)], 201);
    }

    #[Route('/quote-requests/{id}', name: 'quote_requests_update', methods: ['PUT'])]
    public function updateQuoteRequest(
        QuoteRequest $quoteRequest,
        Request $request,
        EntityManagerInterface $em
    ): JsonResponse {
        /** @var User|null $user */
        $user = $this->getUser();
        if (!$user) {
            return $this->json(['message' => 'Non authentifié'], 401);
        }

        if (!method_exists($quoteRequest, 'getClient')) {
            return $this->json([
                'message' => "Ton entité QuoteRequest doit avoir getClient/setClient."
            ], 500);
        }

        $client = $quoteRequest->getClient();
        if (!$client || !method_exists($client, 'getId') || $client->getId() !== $user->getId()) {
            return $this->json(['message' => 'Accès refusé (quote request non propriétaire)'], 403);
        }

        $payload = json_decode($request->getContent(), true);
        if (!is_array($payload)) {
            return $this->json(['message' => 'JSON invalide'], 400);
        }

        // Update champs optionnels
        if (array_key_exists('message', $payload) && method_exists($quoteRequest, 'setMessage')) {
            $quoteRequest->setMessage($payload['message'] !== null ? (string) $payload['message'] : null);
        }
        if (array_key_exists('nb_personnes', $payload) && method_exists($quoteRequest, 'setNbPersonnes')) {
            $quoteRequest->setNbPersonnes((int) $payload['nb_personnes']);
        }
        if (array_key_exists('date_evenement', $payload) && method_exists($quoteRequest, 'setDateEvenement')) {
            $quoteRequest->setDateEvenement($payload['date_evenement'] ? new \DateTimeImmutable((string) $payload['date_evenement']) : null);
        }

        $em->flush();

        return $this->json(['quote_request' => $this->quoteRequestToArray($quoteRequest)], 200);
    }

    #[Route('/quote-requests/{id}', name: 'quote_requests_delete', methods: ['DELETE'])]
    public function deleteQuoteRequest(QuoteRequest $quoteRequest, EntityManagerInterface $em): JsonResponse
    {
        /** @var User|null $user */
        $user = $this->getUser();
        if (!$user) {
            return $this->json(['message' => 'Non authentifié'], 401);
        }

        if (!method_exists($quoteRequest, 'getClient')) {
            return $this->json([
                'message' => "Ton entité QuoteRequest doit avoir getClient/setClient."
            ], 500);
        }

        $client = $quoteRequest->getClient();
        if (!$client || !method_exists($client, 'getId') || $client->getId() !== $user->getId()) {
            return $this->json(['message' => 'Accès refusé (quote request non propriétaire)'], 403);
        }

        $em->remove($quoteRequest);
        $em->flush();

        return $this->json(['message' => 'QuoteRequest supprimée'], 200);
    }

    // =========================
    // REVIEWS (CRUD)
    // =========================

    #[Route('/reviews', name: 'reviews_list', methods: ['GET'])]
    public function listReviews(ReviewRepository $repo): JsonResponse
    {
        /** @var User|null $user */
        $user = $this->getUser();
        if (!$user) {
            return $this->json(['message' => 'Non authentifié'], 401);
        }

        if (!method_exists(Review::class, 'getClient')) {
            return $this->json([
                'message' => "Ton entité Review doit avoir une relation client (getClient/setClient)."
            ], 500);
        }

        $items = $repo->findBy(['client' => $user], ['id' => 'DESC']);
        $data = array_map(fn(Review $r) => $this->reviewToArray($r), $items);

        return $this->json(['reviews' => $data], 200);
    }

    #[Route('/reviews/{id}', name: 'reviews_show', methods: ['GET'])]
    public function showReview(Review $review): JsonResponse
    {
        /** @var User|null $user */
        $user = $this->getUser();
        if (!$user) {
            return $this->json(['message' => 'Non authentifié'], 401);
        }

        if (!method_exists($review, 'getClient')) {
            return $this->json([
                'message' => "Ton entité Review doit avoir getClient/setClient."
            ], 500);
        }

        $client = $review->getClient();
        if (!$client || !method_exists($client, 'getId') || $client->getId() !== $user->getId()) {
            return $this->json(['message' => 'Accès refusé (review non propriétaire)'], 403);
        }

        return $this->json(['review' => $this->reviewToArray($review)], 200);
    }

    #[Route('/reviews', name: 'reviews_create', methods: ['POST'])]
    public function createReview(Request $request, EntityManagerInterface $em): JsonResponse
    {
        /** @var User|null $user */
        $user = $this->getUser();
        if (!$user) {
            return $this->json(['message' => 'Non authentifié'], 401);
        }

        $payload = json_decode($request->getContent(), true);
        if (!is_array($payload)) {
            return $this->json(['message' => 'JSON invalide'], 400);
        }

        $traiteurId = $payload['traiteur_id'] ?? null;
        if (!$traiteurId) {
            return $this->json(['message' => 'traiteur_id est obligatoire'], 422);
        }

        // On cherche le traiteur dans la table User (ton projet utilise App\Entity\User)
        $traiteur = $em->getRepository(User::class)->find((int) $traiteurId);
        if (!$traiteur) {
            return $this->json(['message' => 'Traiteur non trouvé'], 404);
        }

        $review = new Review();

        if (!method_exists($review, 'setClient')) {
            return $this->json([
                'message' => "Ton entité Review doit avoir setClient(User \$client)."
            ], 500);
        }
        $review->setClient($user);

        if (!method_exists($review, 'setTraiteur')) {
            return $this->json([
                'message' => "Ton entité Review doit avoir setTraiteur(User \$traiteur)."
            ], 500);
        }
        $review->setTraiteur($traiteur);

        // Champs principaux
        $note = $payload['note'] ?? null;
        if ($note === null || !is_numeric($note)) {
            return $this->json(['message' => 'note est obligatoire (nombre)'], 422);
        }
        if (method_exists($review, 'setNote')) {
            $review->setNote((int) $note);
        } else {
            return $this->json([
                'message' => "Ton entité Review doit avoir setNote(int \$note)."
            ], 500);
        }

        if (array_key_exists('commentaire', $payload) && method_exists($review, 'setCommentaire')) {
            $review->setCommentaire($payload['commentaire'] !== null ? (string) $payload['commentaire'] : null);
        }

        $em->persist($review);
        $em->flush();

        return $this->json(['review' => $this->reviewToArray($review)], 201);
    }

    #[Route('/reviews/{id}', name: 'reviews_update', methods: ['PUT'])]
    public function updateReview(Review $review, Request $request, EntityManagerInterface $em): JsonResponse
    {
        /** @var User|null $user */
        $user = $this->getUser();
        if (!$user) {
            return $this->json(['message' => 'Non authentifié'], 401);
        }

        if (!method_exists($review, 'getClient')) {
            return $this->json([
                'message' => "Ton entité Review doit avoir getClient/setClient."
            ], 500);
        }

        $client = $review->getClient();
        if (!$client || !method_exists($client, 'getId') || $client->getId() !== $user->getId()) {
            return $this->json(['message' => 'Accès refusé (review non propriétaire)'], 403);
        }

        $payload = json_decode($request->getContent(), true);
        if (!is_array($payload)) {
            return $this->json(['message' => 'JSON invalide'], 400);
        }

        if (array_key_exists('note', $payload) && method_exists($review, 'setNote')) {
            if (!is_numeric($payload['note'])) {
                return $this->json(['message' => 'note doit être un nombre'], 422);
            }
            $review->setNote((int) $payload['note']);
        }

        if (array_key_exists('commentaire', $payload) && method_exists($review, 'setCommentaire')) {
            $review->setCommentaire($payload['commentaire'] !== null ? (string) $payload['commentaire'] : null);
        }

        $em->flush();

        return $this->json(['review' => $this->reviewToArray($review)], 200);
    }

    #[Route('/reviews/{id}', name: 'reviews_delete', methods: ['DELETE'])]
    public function deleteReview(Review $review, EntityManagerInterface $em): JsonResponse
    {
        /** @var User|null $user */
        $user = $this->getUser();
        if (!$user) {
            return $this->json(['message' => 'Non authentifié'], 401);
        }

        if (!method_exists($review, 'getClient')) {
            return $this->json([
                'message' => "Ton entité Review doit avoir getClient/setClient."
            ], 500);
        }

        $client = $review->getClient();
        if (!$client || !method_exists($client, 'getId') || $client->getId() !== $user->getId()) {
            return $this->json(['message' => 'Accès refusé (review non propriétaire)'], 403);
        }

        $em->remove($review);
        $em->flush();

        return $this->json(['message' => 'Review supprimée'], 200);
    }

    // =========================
    // HELPERS
    // =========================

    private function quoteRequestToArray(QuoteRequest $q): array
    {
        $client = method_exists($q, 'getClient') ? $q->getClient() : null;
        $menu = method_exists($q, 'getMenu') ? $q->getMenu() : null;

        return [
            'id' => method_exists($q, 'getId') ? $q->getId() : null,
            'message' => method_exists($q, 'getMessage') ? $q->getMessage() : null,
            'nb_personnes' => method_exists($q, 'getNbPersonnes') ? $q->getNbPersonnes() : null,
            'date_evenement' => method_exists($q, 'getDateEvenement') && $q->getDateEvenement()
                ? $q->getDateEvenement()->format('c')
                : null,
            'client' => $client && method_exists($client, 'getId') ? [
                'id' => $client->getId(),
                'email' => method_exists($client, 'getEmail') ? $client->getEmail() : null,
            ] : null,
            'menu' => $menu && method_exists($menu, 'getId') ? [
                'id' => $menu->getId(),
                'titre' => method_exists($menu, 'getTitre') ? $menu->getTitre() : null,
            ] : null,
        ];
    }

    private function reviewToArray(Review $r): array
    {
        $client = method_exists($r, 'getClient') ? $r->getClient() : null;
        $traiteur = method_exists($r, 'getTraiteur') ? $r->getTraiteur() : null;

        return [
            'id' => method_exists($r, 'getId') ? $r->getId() : null,
            'note' => method_exists($r, 'getNote') ? $r->getNote() : null,
            'commentaire' => method_exists($r, 'getCommentaire') ? $r->getCommentaire() : null,
            'client' => $client && method_exists($client, 'getId') ? [
                'id' => $client->getId(),
                'email' => method_exists($client, 'getEmail') ? $client->getEmail() : null,
            ] : null,
            'traiteur' => $traiteur && method_exists($traiteur, 'getId') ? [
                'id' => $traiteur->getId(),
                'email' => method_exists($traiteur, 'getEmail') ? $traiteur->getEmail() : null,
            ] : null,
        ];
    }
}
