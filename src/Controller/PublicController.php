<?php

namespace App\Controller;

use App\Entity\Menu;
use App\Repository\MenuRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final class PublicController extends AbstractController
{
    #[Route('/api/menus', name: 'api_public_menus_list', methods: ['GET'])]
    public function listMenus(MenuRepository $menuRepository): JsonResponse
    {
        $menus = $menuRepository->findBy([], ['date_creation' => 'DESC']);
        $data = array_map(fn (Menu $m) => $this->menuToArray($m), $menus);

        return $this->json(['menus' => $data], 200);
    }

    #[Route('/api/menus/{id}', name: 'api_public_menus_show', methods: ['GET'])]
    public function showMenu(Menu $menu): JsonResponse
    {
        return $this->json(['menu' => $this->menuToArray($menu)], 200);
    }

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
}

