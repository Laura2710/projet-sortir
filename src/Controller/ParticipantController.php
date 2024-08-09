<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Form\ParticipantType;
use App\Repository\ParticipantRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\User\UserInterface;

class ParticipantController extends AbstractController
{
    #[Route('/participant/{id}', name: 'participant_details', methods: ['GET', 'POST'])]
    public function show(Participant $participantId,
                         Request $request,
                         ParticipantRepository $participantRepository,
                         EntityManagerInterface $entityManager,
                        UserPasswordHasherinterface $userPasswordHasher): Response
    {
        $participant = $participantRepository->findParticipantById($participantId);
        if ($this->getUser() === $participant ){
            $participantForm = $this->createForm(ParticipantType::class, $participant);
            $participantForm->handleRequest($request);

            if ($participantForm->isSubmitted() && $participantForm->isValid()) {
                if ($participantForm->get('motDePasse')->getData()) {
                    $participant->setMotPasse(
                        $userPasswordHasher->hashPassword(
                            $participant,
                            $participantForm->get('motDePasse')->getData()
                        )
                    );
                };
                $entityManager->persist($participant);;
                $entityManager->flush();
                $this->addFlash('success', 'Profil mis à jour !');
//                return $this->render('participant/details.html.twig', [
//                    'participant' => $participant,
//                    'participantForm' => $participantForm->createView()
//                ]);
            }
            return $this->render('participant/details.html.twig', [
                'participant' => $participant,
                'participantForm' => $participantForm->createView()
            ]);

        }
        else {
            return $this->redirectToRoute('sortie_liste');
        }

    }
}
