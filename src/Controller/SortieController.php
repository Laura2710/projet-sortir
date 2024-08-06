<?php

namespace App\Controller;

use App\FiltreSortie\FiltreSortie;
use App\Form\SortieFiltreType;
use App\Repository\SortieRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class SortieController extends AbstractController
{
    #[Route('/', name: 'sortie_liste', methods: ['GET', 'POST'])]
    public function liste(Request $request, SortieRepository $sortieRepository): Response
    {
        $filtre = new FiltreSortie();
        $formulaire_filtre = $this->createForm(SortieFiltreType::class, $filtre);
        $formulaire_filtre->handleRequest($request);

        $sorties = $formulaire_filtre->isSubmitted() && $formulaire_filtre->isValid()
            ? $sortieRepository->findByCriteres($filtre, $this->getUser())
            : $sortieRepository->findSorties();

        return $this->render('sortie/liste.html.twig', [
            'sorties' => $sorties,
            'formulaire_filtres' => $formulaire_filtre->createView()
        ]);
    }
}
