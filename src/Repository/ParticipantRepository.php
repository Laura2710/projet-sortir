<?php

namespace App\Repository;

use App\Entity\Participant;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @extends ServiceEntityRepository<Participant>
 */
class ParticipantRepository extends ServiceEntityRepository implements PasswordUpgraderInterface, UserLoaderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Participant::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof Participant) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setMotPasse($newHashedPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    //    /**
    //     * @return Participant[] Returns an array of Participant objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('p.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

        public function findParticipantById($participantId): ?Participant
        {
            return $this->createQueryBuilder('p')
                ->andWhere('p.id = :id')
                ->setParameter('id', $participantId)
                ->getQuery()
                ->getOneOrNullResult()
            ;
        }
    public function loadUserByIdentifier(string $identifier): ?UserInterface
    {
        $entityManager = $this->getEntityManager();

        return $entityManager->createQuery(
            'SELECT p
                FROM App\Entity\Participant p
                WHERE p.pseudo = :query
                OR p.mail = :query'
        )
            ->setParameter('query', $identifier)
            ->getOneOrNullResult();
    }

    public function findParticipants($admin)
    {
        return $this->createQueryBuilder('p')
            ->join('p.campus', 'c')
            ->addSelect('c')
            ->leftJoin('p.sorties', 's')
            ->addSelect('s')
            ->orderBy('p.nom', 'ASC')
            ->orderBy('p.prenom', 'ASC')
            ->where('p <> :user')
            ->setParameter('user', $admin)
            ->getQuery()
            ->getResult();
    }

    public function getParticipantById($participantId)
    {

    }

}
