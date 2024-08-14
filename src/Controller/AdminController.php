<?php

namespace App\Controller;

use App\Entity\Campus;
use App\Entity\Participant;
use App\Form\RegistrationFormType;
use App\Form\UploadParticipantType;
use App\Manager\ParticipantManagerInterface;
use App\Repository\CampusRepository;
use App\Repository\ParticipantRepository;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use League\Csv\Reader;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Validator\ValidatorInterface;

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
        UserPasswordHasherInterface $passwordHasher,
        CampusRepository            $campusRepository,
        EntityManagerInterface      $entityManager,
        ValidatorInterface          $validator): Response
    {
        $formUpload = $this->createForm(UploadParticipantType::class);
        $formUpload->handleRequest($request);

        if ($formUpload->isSubmitted() && $formUpload->isValid()) {
            $fichier = $formUpload->get('fichier_csv')->getData();
            if ($fichier instanceof UploadedFile) {
                if ($this->isCSV($validator, $fichier)) {
                    $records = $this->lireDonneesCVS($fichier);
                    $entityManager->beginTransaction();
                    try {
                        foreach ($records as $record) {
                            $campus = $campusRepository->findOneBy(['nom' => $record['campus']]);
                            if (!$campus) {
                                $this->addFlash('error', "Le campus {$record['campus']} n'existe pas.");
                                $entityManager->rollback();
                                return $this->redirectToRoute('admin_utilisateurs_upload');
                            }
                            $participant = $this->getParticipant($record, $passwordHasher, $campus);
                            $violations = $validator->validate($participant);
                            if (count($violations) > 0) {
                                $this->addFlash('error', "Le participant " . $participant->getPseudo() . " n'est pas valide");
                                return $this->redirectToRoute('admin_utilisateurs_upload');
                            }
                            $entityManager->persist($participant);
                        }
                        $entityManager->flush();
                        $entityManager->commit();
                        $this->addFlash('success', "Les utilisateurs ont bien été ajoutés !");
                        $this->redirectToRoute('admin_utilisateurs_upload');
                    } catch (UniqueConstraintViolationException $e) {
                        $this->addFlash('error', "L'opération a été annulée. Un participant avec l'email '{$record['email']}' existe déjà.");
                        $entityManager->rollback();
                    } catch (\Exception $e) {
                        $this->addFlash('error', "Une erreur est survenue lors de l'ajout des participants.");
                        $entityManager->rollback();
                    }
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

    private function getParticipant(mixed $record, UserPasswordHasherInterface $passwordHasher, Campus $campus): Participant
    {
        $participant = new Participant();
        $participant->setNom(htmlspecialchars($record['nom']));
        $participant->setPrenom(htmlspecialchars($record['prenom']));
        $participant->setMail(htmlspecialchars($record['email']));
        $participant->setPseudo(htmlspecialchars($record['pseudo']));
        $participant->setTelephone(htmlspecialchars($record['telephone']));
        $participant->setAdministrateur(false);
        $participant->setActif(true);
        $mdp = $record['password'];
        $hashPassword = $passwordHasher->hashPassword($participant, $mdp);
        $participant->setMotPasse($hashPassword);
        $participant->setCampus($campus);
        return $participant;
    }

    private function isCSV(ValidatorInterface $validator, mixed $fichier): bool
    {
        $violations = $validator->validate($fichier,
            new File([
                'maxSize' => '1000K',
                'mimeTypes' => [
                    'text/csv',
                    'text/plain',
                ],
            ]));

        if (count($violations) > 0) {
            foreach ($violations as $violation) {
                $this->addFlash('error', $violation->getMessage());
            }
            return false;
        }
        return true;
    }

    private function lireDonneesCVS(mixed $fichier): array
    {
        $csv = Reader::createFromPath($fichier->getRealPath(), 'r');
        $csv->setHeaderOffset(0);
        $csv->setDelimiter(';');
        $records = $csv->getRecords();

        // Filtrer les lignes vides
        $recordsFiltres = [];
        foreach ($records as $record) {
            if (!empty(array_filter($record))) {
                $recordsFiltres[] = $record;
            }
        }
        return $recordsFiltres;
    }
}
