<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Form\ParticipantType;
use App\Repository\ParticipantRepository;
use App\Service\FileUploadService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\String\Slugger\SluggerInterface;

class ParticipantController extends AbstractController
{
    #[Route('/participant/{id}', name: 'participant_details', methods: ['GET', 'POST'])]
    public function show(
        Participant $participantId,
        Request $request,
        ParticipantRepository $participantRepository,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $userPasswordHasher,
        FileUploadService $fileUploadService
    ): Response {
        $participant = $participantRepository->findParticipantById($participantId);
        if ($this->getUser() === $participant) {
            $participantForm = $this->createForm(ParticipantType::class, $participant);
            $participantForm->handleRequest($request);

            // Si le formulaire est soumis et valide
            if ($participantForm->isSubmitted() && $participantForm->isValid()) {
                // Upload de l'image si elle existe
                $avatar = $participantForm->get('avatar')->getData();
                if ($avatar) {
                    try {
                        $participant->setAvatar($fileUploadService->uploadImage($avatar, 'avatar'));
                    } catch (\Exception $e) {
                        $this->addFlash('error', 'Impossible de télécharger l\'image');
                    }
                }
                // Mise à jour du mot de passe si modifié
                if ($participantForm->get('motDePasse')->getData()) {
                    $participant->setMotPasse($userPasswordHasher->hashPassword($participant, $participantForm->get('motDePasse')->getData()));
                }
                // Mise à jour du participant
                $entityManager->flush();
                $this->addFlash('success', 'Profil mis à jour !');
                $entityManager->refresh($this->getUser());
                return $this->redirectToRoute('participant_details', ['id' => $participant->getId()]);
            }

            return $this->render('participant/details.html.twig', [
                'participant' => $participant,
                'participantForm' => $participantForm->createView()
            ]);
        }

        return $this->render('participant/details.html.twig', [
            'participant' => $participant
        ]);
    }
}
