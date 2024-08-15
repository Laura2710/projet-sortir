<?php

namespace App\Manager;

use App\Entity\Campus;
use App\Entity\Participant;
use App\Models\ParticipantVueDTO;
use App\Repository\CampusRepository;
use App\Repository\ParticipantRepository;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ParticipantManager implements ParticipantManagerInterface
{
    public function __construct(
        private readonly ParticipantRepository $participantRepository,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly ValidatorInterface $validator,
        private readonly EntityManagerInterface $entityManager,
        private readonly CampusRepository $campusRepository,
    )
    {

    }
    public function findParticipants(UserInterface $user): array
    {
        $participants = $this->participantRepository->findParticipants($user);
        return array_map(function (Participant $participant) {
            return ParticipantVueDTO::createFromEntity($participant);
        }, $participants);
    }

    public function creerParticipant(array $data, Campus $campus): array
    {
        $participant = new Participant();
        $participant->setNom(htmlspecialchars($data['nom']));
        $participant->setPrenom(htmlspecialchars($data['prenom']));
        $participant->setMail(htmlspecialchars($data['email']));
        $participant->setPseudo(htmlspecialchars($data['pseudo']));
        $participant->setTelephone(htmlspecialchars($data['telephone']));
        $participant->setAdministrateur(false);
        $participant->setActif(true);
        $participant->setMotPasse($this->passwordHasher->hashPassword($participant, $data['password']));
        $participant->setCampus($campus);

        $violations = $this->validator->validate($participant);
        if (count($violations) > 0) {
            return ['error', "Le participant " . $participant->getPseudo() . " n'est pas valide"];
        }

        return ['success', $participant];

    }

    public function enregistrerParticipantsUpload(array $records) : array {
        $this->entityManager->beginTransaction();
        try {
            foreach ($records as $record) {
                $campus = $this->campusRepository->findOneBy(['nom' => $record['campus']]);
                if (!$campus) {
                    $this->entityManager->rollback();
                    return ['error', "Le campus {$record['campus']} n'existe pas."];
                }

                $result = $this->creerParticipant($record, $campus);
                if ($result[0] === 'error') {
                    $this->entityManager->rollback();
                    return $result;
                }

                $participant = $result[1];
                $this->entityManager->persist($participant);
                $this->entityManager->flush();
            }
            $this->entityManager->commit();
            return ['success', "Les utilisateurs ont bien été ajoutés !"];
        } catch (UniqueConstraintViolationException $e) {
            $this->entityManager->rollback();
            return ['error', "L'opération a été annulée. Un participant avec cet email existe déjà."];
        } catch (\Exception $e) {
            $this->entityManager->rollback();
            return ['error', "Une erreur est survenue. Impossible d'enregistrer les participants"];
        }
    }
}