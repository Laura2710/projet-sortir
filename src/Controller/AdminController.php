<?php

namespace App\Controller;

use App\Entity\Campus;
use App\Entity\Participant;
use App\Form\UploadParticipantType;
use App\Repository\CampusRepository;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use League\Csv\Reader;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/admin', name: 'admin_')]
class AdminController extends AbstractController
{
    #[Route('/creer-utilisateurs', name: 'creer_utilisateurs')]
    public function creerUtilisateurs(
        Request                     $request,
        UserPasswordHasherInterface $passwordHasher,
        CampusRepository            $campusRepository,
        EntityManagerInterface      $entityManager,
        ValidatorInterface          $validator ): Response
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
                                return $this->redirectToRoute('admin_creer_utilisateurs');
                            }
                            $participant = $this->getParticipant($record, $passwordHasher, $campus);
                            $entityManager->persist($participant);
                        }
                        $entityManager->flush();
                        $entityManager->commit();
                        $this->addFlash('success', "Les utilisateurs ont bien été ajoutés !");

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
        return $this->render('admin/creer-utilisateurs.html.twig', [
            'formUpload' => $formUpload->createView()
        ]);
    }


    public function getParticipant(mixed $record, UserPasswordHasherInterface $passwordHasher, Campus $campus): Participant
    {
        $participant = new Participant();
        $participant->setNom($record['nom']);
        $participant->setPrenom($record['prenom']);
        $participant->setMail($record['email']);
        $participant->setPseudo($record['pseudo']);
        $participant->setTelephone($record['telephone']);
        $participant->setAdministrateur(false);
        $participant->setActif(true);
        $mdp = $record['password'];
        $hashPassword = $passwordHasher->hashPassword($participant, $mdp);
        $participant->setMotPasse($hashPassword);
        $participant->setCampus($campus);
        return $participant;
    }


    public function isCSV(ValidatorInterface $validator, mixed $fichier): bool
    {
        $violations = $validator->validate($fichier,
            new File([
                'maxSize' => '200k',
                'mimeTypes' => [
                    'text/csv',
                    'text/plain'
                ],
            ]));

        if (count($violations) > 0) {
            $this->addFlash('error', "Le fichier n'est pas valide.");
            return false;
        }
        return true;
    }

    public function lireDonneesCVS(mixed $fichier): \Iterator
    {
        $csv = Reader::createFromPath($fichier->getRealPath(), 'r');
        $csv->setHeaderOffset(0);
        $csv->setDelimiter(';');
        $records = $csv->getRecords();
        return $records;
    }
}
