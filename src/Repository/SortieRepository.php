<?php

namespace App\Repository;

use App\Entity\Sortie;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
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

    public function findSorties()
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
            ->addSelect('p')
            ->where('s.dateHeureDebut > :date')
            ->setParameter('date', new \DateTime('-1 month'))
            ->orderBy('s.dateHeureDebut', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findByCriteres($filtre, $utilisateur)
    {
        dump($filtre, $utilisateur);
        $query = $this->createQueryBuilder('s')
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


        // Si la case organisateur est cochée
        if ($filtre->getEstOrganisateur()) {
            $query->orWhere('s.organisateur = :organisateur')
                ->setParameter('organisateur', $utilisateur);
        } else {
            $query->andWhere('s.organisateur <> :organisateur')
                ->setParameter('organisateur', $utilisateur);
        }

        // Si la case "inscrit" est cochée
        if ($filtre->getEstInscrit()) {
            $query->orWhere(':inscrit MEMBER OF s.participants')
                ->setParameter('inscrit', $utilisateur);
        }

        // Si la case "non inscrit" est cochée
        if ($filtre->getNonInscrit()) {
            $query->orWhere(':inscrit NOT MEMBER OF s.participants')
                ->setParameter('inscrit', $utilisateur);
        }

        // Si la case "sortie passée" est décochée
        if ($filtre->getSortiesPassees()) {
            $query->orWhere('s.dateHeureDebut <= :date')
                ->setParameter('date', new \DateTime('-1 month'));
        }
        else {
            $query->andWhere('s.dateHeureDebut > :date')
                ->setParameter('date', new \DateTime('-1 month'));
        }


        if ($filtre->getNomSortie()) {
            $nomSortie = trim($filtre->getNomSortie());
            $query->andWhere('s.nom LIKE :nomSortie')
                ->setParameter('nomSortie', '%' . $nomSortie . '%');
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
}
