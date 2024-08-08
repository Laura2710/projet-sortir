<?php

namespace App\Repository;

use App\Entity\Etat;
use App\Entity\Sortie;
use App\Enum\EtatEnum;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Sortie>
 */
class SortieRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Sortie::class);
    }

    public function queryBuilderSortie(): QueryBuilder
    {
        return $this->createQueryBuilder('s')
            ->join('s.campus', 'c')
            ->addSelect('c')
            ->join('s.etat', 'e')
            ->addSelect('e')
            ->join('s.organisateur', 'o')
            ->addSelect('o')
            ->join('s.lieu', 'l')
            ->addSelect('l')
            ->leftJoin('s.participants', 'p')
            ->addSelect('p');
    }

    public function findSorties($utilisateur)
    {
        $now = new \DateTime();
        $lastMonth = $now->sub(new \DateInterval('P1M'));

        return $this->queryBuilderSortie()
            ->andWhere('(e.libelle <> \'Créée\' OR o = :utilisateur)')
            ->setParameter('utilisateur', $utilisateur)
            ->andWhere('c = :campus')
            ->setParameter('campus', $utilisateur->getCampus())
            ->andWhere('s.dateHeureDebut > :date')
            ->setParameter('date', $lastMonth)
            ->orderBy('s.dateHeureDebut', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findByCriteres($filtre, $utilisateur)
    {
        $now = new \DateTime();
        $lastMonth = $now->sub(new \DateInterval('P1M'));

        $query = $this->queryBuilderSortie()
            ->andWhere('(e.libelle <> \'Créée\' OR o = :utilisateur)')
            ->setParameter('utilisateur', $utilisateur);

        // Si la case "inscrit" est cochée
        if ($filtre->getEstInscrit()) {
            $query->andWhere(':inscrit MEMBER OF s.participants')
                ->setParameter('inscrit', $utilisateur);
        }

        // Si la case "non inscrit" est cochée
        if ($filtre->getNonInscrit()) {
            $query->andWhere(':inscrit NOT MEMBER OF s.participants AND s.organisateur != :inscrit')
                ->setParameter('inscrit', $utilisateur)
                ->andWhere('e.libelle = \'Ouverte\'');
        }

        // Si la case "sortie passée" est cochée
        if ($filtre->getSortiesPassees()) {
            $query->andWhere('s.dateHeureDebut < :date')
                ->setParameter('date', $lastMonth);
        } else {
            $query->andWhere('s.dateHeureDebut >= :date')
                ->setParameter('date', $lastMonth);
        }

        // Si la case organisateur est cochée
        if ($filtre->getEstOrganisateur()) {
            $query->andWhere('s.organisateur = :organisateur')
                ->setParameter('organisateur', $utilisateur);
        }

        if ($filtre->getNomSortie()) {
            $query->andWhere('s.nom LIKE :nomSortie')
                ->setParameter('nomSortie', '%' . $filtre->getNomSortie() . '%');
        }

        if ($filtre->getDateDebutSortie()) {
            $query->andWhere('s.dateHeureDebut >= :dateDebut')
                ->setParameter('dateDebut', $filtre->getDateDebutSortie());
        }

        if ($filtre->getDateFinSortie()) {
            $dateFin = clone $filtre->getDateFinSortie();
            $dateFin->setTime(23, 59, 59);
            $query->andWhere('s.dateHeureDebut <= :dateFin')
                ->setParameter('dateFin', $dateFin);
        }

        return $query->andWhere('s.campus = :campus')
            ->setParameter('campus', $filtre->getCampus())
            ->orderBy('s.dateHeureDebut', 'DESC')
            ->getQuery()
            ->getResult();

    }

    public function findSortie(Sortie $sortie)
    {
        return $this->queryBuilderSortie()
            ->join('l.ville', 'v')
            ->addSelect('v')
            ->andWhere('s = :sortie')
            ->setParameter('sortie', $sortie)
            ->getQuery()
            ->getOneOrNullResult();
    }


    public function findACloturee()
    {
        $timezone = new \DateTimeZone('Europe/Paris');
        $date = new \DateTime('now', $timezone);
        $formattedDate = $date->format('Y-m-d H:i:s');

        $query = $this->queryBuilderSortie()
            ->andWhere(':date >= s.dateLimiteInscription')
            ->andWhere(':date <= s.dateHeureDebut')
            ->setParameter('date', $formattedDate)
            ->getQuery();
        return $query->getResult();
    }

    public function findEnCours()
    {
        $timezone = new \DateTimeZone('Europe/Paris');
        $date = new \DateTime('now', $timezone);
        $formattedDate = $date->format('Y-m-d H:i:s');

        $query = $this->queryBuilderSortie()
            ->andWhere(':date >= s.dateHeureDebut')
            ->andWhere(':date < DATE_ADD(s.dateHeureDebut, s.duree, \'MINUTE\')')
            ->setParameter('date', $formattedDate)
            ->getQuery();
        return $query->getResult();

    }

    public function findTerminee()
    {
        $timezone = new \DateTimeZone('Europe/Paris');
        $date = new \DateTime('now', $timezone);
        $formattedDate = $date->format('Y-m-d H:i:s');

        $query = $this->queryBuilderSortie()
            ->andWhere(':date > DATE_ADD(s.dateHeureDebut, s.duree, \'MINUTE\')')
            ->andWhere(':date < DATE_ADD(s.dateHeureDebut, 1, \'MONTH\')')
            ->setParameter('date', $formattedDate)
            ->getQuery();
        return $query->getResult();
    }

    public function findPassees()
    {
        $timezone = new \DateTimeZone('Europe/Paris');
        $date = new \DateTime('now', $timezone);
        $formattedDate = $date->format('Y-m-d H:i:s');

        $query = $this->queryBuilderSortie()
            ->where(':date >= DATE_ADD(s.dateHeureDebut, 1, \'MONTH\')')
            ->setParameter('date', $formattedDate)
            ->getQuery();
        return $query->getResult();
    }


}
