<?php

namespace App\Controller;

use App\Entity\Sortie;
use App\Enum\EtatEnum;
use App\FiltreSortie\FiltreSortie;
use App\Form\CreerSortieType;
use App\Form\SortieFiltreType;
use App\Repository\EtatRepository;
use App\Repository\LieuRepository;
use App\Repository\SortieRepository;
use Doctrine\ORM\EntityManagerInterface;
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

    #[Route('/sortie/creer', name: 'sortie_creer', methods: ['GET', 'POST'])]
    public function creer(Request $request, LieuRepository $lieuRepository, EntityManagerInterface $entityManager, EtatRepository $etatRepository): Response
    {
        $sortie = new Sortie();
        $lieux = $lieuRepository->findAll();
        $user = $this->getUser();
        $sortie->setCampus($this->getUser()->getCampus());
        $creerSortieForm = $this->createForm(CreerSortieType::class, $sortie, ['lieux'=>$lieux]);

        $creerSortieForm->handleRequest($request);
        if ($creerSortieForm->isSubmitted()) {
            $sortie->setOrganisateur($user);
            $etat = $etatRepository->findOneBy(['libelle'=>EtatEnum::Creee]);
            $sortie->setEtat($etat);

            $entityManager->persist($sortie);
            $entityManager->flush();
            return $this->redirectToRoute('sortie_liste');
        }

        return $this->render('sortie/creer.html.twig', [
            'creerSortieForm' => $creerSortieForm->createView(),
        ]);
    }
}
