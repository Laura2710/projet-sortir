<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Form\RegistrationFormType;
use App\Form\UploadParticipantType;
use App\Manager\ParticipantManagerInterface;
use App\Repository\ParticipantRepository;
use App\Service\CsvService;
use App\Service\CsvValidator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

#[Route('/admin', name: 'admin_')]
class AdminController extends AbstractController
{
    #[Route('/utilisateurs', name: 'utilisateurs')]
    public function gererUtilisateurs(ParticipantManagerInterface $participantManager): Response
    {
        $participants = $participantManager->findParticipants($this->getUser());
        return $this->render('admin/utilisateurs-gestion.html.twig', [
            'participants' => $participants
        ]);
    }

    #[Route('/utilisateurs/upload', name: 'utilisateurs_upload', methods: ['GET', 'POST'])]
    public function uploaderUtilisateurs(
        Request                     $request,
        CsvValidator                $csvValidator,
        CsvService                  $csvService,
        ParticipantManagerInterface $participantManager,
    ): Response
    {
        $formUpload = $this->createForm(UploadParticipantType::class);
        $formUpload->handleRequest($request);

        if ($formUpload->isSubmitted() && $formUpload->isValid()) {
            $fichier = $formUpload->get('fichier_csv')->getData();
            if ($fichier instanceof UploadedFile && $csvValidator->validate($fichier)) {
                $records = $csvService->lire($fichier);
                $reponse = $participantManager->enregistrerParticipantsUpload($records);

                if ($reponse[0] == "success") {
                    $this->addFlash("success", $reponse[1]);
                    return $this->redirectToRoute('admin_utilisateurs');
                } else {
                    $this->addFlash('error', $reponse[1]);
                }
            }
        }
        return $this->render('admin/utilisateurs-upload.html.twig', [
            'formUpload' => $formUpload->createView()
        ]);
    }

    #[Route('/utilisateur/creer', name: 'utilisateur_creer', methods: ['GET', 'POST'])]
    public function creerUtilisateur(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {
        $participant = new Participant();
        $form = $this->createForm(RegistrationFormType::class, $participant);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // encoder le mot de passe
            $participant->setMotPasse(
                $userPasswordHasher->hashPassword(
                    $participant,
                    $form->get('plainPassword')->getData()
                )
            );
            $participant->setAdministrateur(false);
            $participant->setActif(true);

            $entityManager->persist($participant);
            $entityManager->flush();
            $this->addFlash('success', "L'utilisateur a bien été créé");
            $this->redirectToRoute('admin_utilisateurs');
        }

        return $this->render('admin/utilisateur-creer.html.twig', ['registrationForm' => $form]);
    }

    #[Route('/utilisateur/activer/{id}', name: 'utilisateur_activer', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function desactiverUtilisateur(int $id, EntityManagerInterface $entityManager, ParticipantRepository $participantRepository, Request $request, CsrfTokenManagerInterface $csrfTokenManager): Response
    {
        $token = $request->query->get('_token');

        if ($csrfTokenManager->isTokenValid(new CsrfToken('activer-' . $id, $token))) {
            $participant = $participantRepository->find($id);
            if (!$participant) {
                $this->addFlash('error', "L'utilisateur n'existe pas.");
                return $this->redirectToRoute('admin_utilisateurs');
            }
            if ($participant->isAdministrateur()) {
                $this->addFlash('error', "Action interdite");
                return $this->redirectToRoute('admin_utilisateurs');
            }

            $participant->setActif(!$participant->isActif());
            $entityManager->flush();

            $action = $participant->isActif() ? 'activé' : 'désactivé';
            $message = sprintf('Le compte de %s a été %s avec succès.', $participant->getPseudo(), $action);
            $this->addFlash('success', $message);

        } else {
            $this->addFlash('error', "Token CSRF invalide.");
        }

        return $this->redirectToRoute('admin_utilisateurs');
    }

    #[Route('/utilisateur/supprimer/{id}', name: 'utilisateur_supprimer', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function supprimerUtilisateur(int $id, EntityManagerInterface $entityManager, Request $request, CsrfTokenManagerInterface $csrfTokenManager, ParticipantRepository $participantRepository): Response
    {
        $token = $request->query->get('_token');

        if ($csrfTokenManager->isTokenValid(new CsrfToken('supprimer-' . $id, $token))) {
            $participant = $participantRepository->find($id);
            if (!$participant) {
                $this->addFlash('error', "L'utilisateur n'existe pas.");
                return $this->redirectToRoute('admin_utilisateurs');
            }
            if ($participant->isAdministrateur()) {
                $this->addFlash('error', "Action interdite");
                return $this->redirectToRoute('admin_utilisateurs');
            }
            $entityManager->remove($participant);
            $entityManager->flush();
            $this->addFlash('success', "L'utilisateur a été supprimé!");
        } else {
            $this->addFlash('error', "Token CSRF invalide.");
        }

        return $this->redirectToRoute('admin_utilisateurs');
    }

}
