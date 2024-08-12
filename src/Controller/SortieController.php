<?php

namespace App\Controller;

use App\Entity\Lieu;
use App\Entity\Sortie;
use App\Enum\EtatEnum;
use App\FiltreSortie\FiltreSortie;
use App\Form\CreerSortieType;
use App\Form\AnnulationSortieType;
use App\Form\SortieFiltreType;
use App\Repository\EtatRepository;
use App\Repository\LieuRepository;
use App\Repository\ParticipantRepository;
use App\Repository\SortieRepository;
use App\Service\MajEtatSortie;
use App\Service\NotifierParticipant;
use Doctrine\ORM\EntityManagerInterface;
use phpDocumentor\Reflection\Types\This;
use PHPUnit\Util\Json;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\Notifier\Notification\Notification;
use Symfony\Component\Notifier\NotifierInterface;
use Symfony\Component\Routing\Attribute\Route;

class SortieController extends AbstractController
{
    #[Route('/', name: 'sortie_liste', methods: ['GET', 'POST'])]
    public function liste(Request $request, SortieRepository $sortieRepository, MajEtatSortie $majEtatSortie): Response
    {
        // Mise à jour de l'état des sortie
        $majEtatSortie->mettreAjourEtatSortie();

        $filtre = new FiltreSortie();
        //$filtre->setCampus($this->getUser()->getCampus());
        $formulaire_filtre = $this->createForm(SortieFiltreType::class, $filtre);
        $formulaire_filtre->handleRequest($request);
        $sorties = $sortieRepository->findSorties($this->getUser());

        if ($formulaire_filtre->isSubmitted() && $formulaire_filtre->isValid()) {
            $campus = $formulaire_filtre->get('campus')->getData();
            $filtre->setCampus($campus);
            $sorties = $sortieRepository->findByCriteres($filtre, $this->getUser());
        }

        return $this->render('sortie/liste.html.twig', [
            'sorties' => $sorties,
            'formulaire_filtres' => $formulaire_filtre->createView()
        ]);
    }
    #[Route('/sortie/inscrire/{id}', name: 'inscrire', methods: ['GET'])]
    public function inscrire(Sortie $sortie, EntityManagerInterface $entityManager, EtatRepository $etatRepository): Response
    {
        if ($sortie->getOrganisateur() === $this->getUser()) {
            $this->addFlash('error', 'Vous ne pouvez pas vous inscrire à votre propre sortie.');
            return $this->redirectToRoute('sortie_liste');
        }

        if ($sortie->getEtat()->getLibelle()->value !== 'Ouverte') {
            $this->addFlash('error', 'Vous ne pouvez vous inscrire qu\'à des sorties ouvertes.');
            return $this->redirectToRoute('sortie_liste');
        }

        if (!$sortie->getParticipants()->contains($this->getUser()) && $sortie->getParticipants()->count() < $sortie->getNbInscriptionsMax()) {
            $sortie->addParticipant($this->getUser());
            $entityManager->persist($sortie);
            $entityManager->flush();
            $this->addFlash('success', 'Vous vous êtes inscrit à la sortie.');

            if ($sortie->getParticipants()->count() >= $sortie->getNbInscriptionsMax()) {
                $etatCloturee = $etatRepository->findOneBy(['libelle' => 'Clôturée']);
                $sortie->setEtat($etatCloturee);
                $entityManager->persist($sortie);
                $entityManager->flush();
            }
        } else {
            $this->addFlash('error', 'Le nombre maximum de participants est atteint.');
        }

        return $this->redirectToRoute('sortie_liste');
    }



    #[Route('/sortie/se-desister/{id}', name: 'se_desister',requirements: ['id'=>'\d+'], methods: ['GET'])]
    public function seDesister(Sortie $sortie, EntityManagerInterface $entityManager, EtatRepository $etatRepository): Response
    {
        if ($sortie->getParticipants()->contains($this->getUser())) {
            $sortie->removeParticipant($this->getUser());
            $entityManager->persist($sortie);
            $entityManager->flush();
            $this->addFlash('success', 'Vous vous êtes désisté de la sortie.');

            if ($sortie->getParticipants()->count() < $sortie->getNbInscriptionsMax()) {
                $etatOuverte = $etatRepository->findOneBy(['libelle' => 'Ouverte']);
                $sortie->setEtat($etatOuverte);
                $entityManager->persist($sortie);
                $entityManager->flush();
            }
        } else {
            $this->addFlash('error', 'Vous n\'êtes pas inscrit à cette sortie.');
        }

        return $this->redirectToRoute('sortie_liste');

    }

    #[Route('/sortie/detail/{id}', name: 'sortie_detail', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function show(Sortie $sortieParam, SortieRepository $sortieRepository): Response
    {
        $sortie = $sortieRepository->findSortie($sortieParam);
        if (!$sortie || $sortie->getEtat()->getLibelle()->value == 'Créée' || $sortie->getEtat()->getLibelle()->value == 'Activité passée') {
            $this->addFlash('error', 'Accès interdit.');
            return $this->redirectToRoute('sortie_liste');
        }
        return $this->render('sortie/detail.html.twig', [
            'sortie' => $sortie,
        ]);
    }

    #[Route('/sortie/annuler/{id}', name: 'sortie_annuler', methods: ['GET', 'POST'])]
    public function annuler(
        int                    $id,
        Request                $request,
        SortieRepository       $sortieRepository,
        EntityManagerInterface $entityManager,
        EtatRepository         $etatRepository
    ): Response
    {
        $sortie = $sortieRepository->find($id);

        // Si l'utilisateur n'est pas l'organisateur ou l'administrateur alors rediriger avec un message d'erreur
        if ($sortie == null || ($sortie->getOrganisateur() != $this->getUser() && !$this->isGranted('ROLE_ADMIN'))) {
            $this->addFlash('error', "Accès interdit. Vous n'êtes pas autorisé à annuler cette sortie.");
            return $this->redirectToRoute('sortie_liste');
        }


        // Vérifier que la sortie à l'état 'Ouverte' et que la date actuelle est inférieure ou égale à la date du début de la sortie
        if ($sortie->getEtat()->getLibelle()->value == 'Ouverte' && new \DateTime() <= $sortie->getDateHeureDebut()) {
            $formAnnulation = $this->createForm(AnnulationSortieType::class);
            $formAnnulation->handleRequest($request);

            if ($formAnnulation->isSubmitted() && $formAnnulation->isValid()) {
                $etat = $etatRepository->findOneBy(['libelle' => 'Annulée']);
                $sortie->setEtat($etat);
                $sortie->setInfosSortie($formAnnulation->get('motifAnnulation')->getData());
                $entityManager->flush();
                $this->addFlash('success', 'La sortie a bien été annulée !');
                return $this->redirectToRoute('sortie_detail', ['id' => $sortie->getId()]);
            }

        } else {
            $this->addFlash('error', "Vous ne pouvez pas annuler la sortie.");
            return $this->redirectToRoute('sortie_liste');
        }

        return $this->render('sortie/annuler.html.twig', [
            'formAnnulation' => $formAnnulation,
            'sortie' => $sortie,
        ]);
    }

    #[Route('/sortie/supprimer/{id}', name: 'sortie_supprimer', methods: ['GET'])]
    public function supprimer(int $id, SortieRepository $sortieRepository, EntityManagerInterface $entityManager): Response
    {
        $sortie = $sortieRepository->find($id);

        // Si la sortie n'existe pas : rediriger avec un message d'erreur
        if (!$sortie) {
            $this->addFlash('error', 'La sortie n\'existe pas.');
            return $this->redirectToRoute('sortie_liste');
        }

        // Vérifier que la sortie à l'état 'Créée' et que l'utilisateur est bien l'organisateur
        if ($sortie->getEtat()->getLibelle()->value == 'Créée' && $sortie->getOrganisateur() == $this->getUser()) {
            $entityManager->remove($sortie);
            $entityManager->flush();
            $this->addFlash('success', 'La sortie a bien été supprimée');
        }

        return $this->redirectToRoute('sortie_liste');
    }

    #[Route('/sortie/publier/{id}', name: 'sortie_publier', methods: ['GET'])]
    public function publier(
        int                    $id,
        EntityManagerInterface $entityManager,
        SortieRepository       $sortieRepository,
        EtatRepository $etatRepository,
        NotifierParticipant $notifierParticipant
    ): Response
    {

        $sortie = $sortieRepository->find($id);

        // Vérifie si la sortie existe
        if (!$sortie) {
            $this->addFlash('error', 'La sortie n\'existe pas.');
            return $this->redirectToRoute('sortie_liste');
        }

        $etatLibelle = $sortie->getEtat()->getLibelle()->value;

        // Si la sortie n'est pas en création ou si l'utilisateur n'est l'organisateur : rediriger avec message d'erreur
        if ($etatLibelle !== 'Créée' || $sortie->getOrganisateur() !== $this->getUser()) {
            $this->addFlash('error', 'Accès interdit. Vous n\'êtes pas autorisé à publier cette sortie.');
            return $this->redirectToRoute('sortie_liste');
        }

        // Si la date de début de l'événement n'est pas dans le futur
        if ($sortie->getDateHeureDebut() <= new \DateTime()) {
            $this->addFlash('error', 'La date de début doit être dans le futur pour publier la sortie.');
            return $this->redirectToRoute('sortie_liste');
        }

        $nouvelEtat = $etatRepository->findOneBy(['libelle' => 'Ouverte']);
        $nouvelEtat->setLibelle(EtatEnum::Ouverte);
        $sortie->setEtat($nouvelEtat);
        $entityManager->flush();

        // notification des participants par email
        $notifierParticipant->alerterParEmail($sortie);

        $this->addFlash('success', 'La sortie a bien été publiée.');
        return $this->redirectToRoute('sortie_liste');
    }

    #[Route('/sortie/creer', name: 'sortie_creer', methods: ['GET', 'POST'])]
    public function creer(Request $request, LieuRepository $lieuRepository, EntityManagerInterface $entityManager, EtatRepository $etatRepository): Response
    {
        $sortie = new Sortie();
        $lieus = $lieuRepository->findAll();
        $user = $this->getUser();
        $sortie->setCampus($this->getUser()->getCampus());

        $creerSortieForm = $this->createForm(CreerSortieType::class, $sortie, ['lieus'=>$lieus]);
        $creerSortieForm->handleRequest($request);

        if ($creerSortieForm->isSubmitted() && $creerSortieForm->isValid()) {
            $sortie->setOrganisateur($user);
            $etat = $etatRepository->findOneBy(['libelle'=>EtatEnum::Creee]);
            $sortie->setEtat($etat);

            $entityManager->persist($sortie);
            $entityManager->flush();
            $this->addFlash('success', 'La sortie a bien été créée!');
            return $this->redirectToRoute('sortie_liste');
        }

        return $this->render('sortie/creer.html.twig', [
            'creerSortieForm' => $creerSortieForm->createView(),
        ]);
    }

    #[Route('/sortie/lieu', name: 'sortie_lieu', methods: ['POST'])]
    public function lieu(Request $request, LieuRepository $lieuRepository): Response
    {
        $lieu = $lieuRepository->find($request->request->get('id'));
        return $this->json([
            'rue' => $lieu ? $lieu->getRue() : null,
        ]);
    }
}

