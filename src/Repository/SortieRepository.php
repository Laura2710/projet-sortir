<?php

namespace App\Repository;

use App\Entity\Sortie;
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

    public function queryBuilderSortie($utilisateur = null) : QueryBuilder
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
            ->andWhere('(e.libelle <> \'Créée\' OR o = :utilisateur)')
            ->setParameter('utilisateur', $utilisateur);


    }
    public function findSorties($utilisateur)
    {

        return $this->queryBuilderSortie($utilisateur)
            ->andWhere('c = :campus')
            ->setParameter('campus', $utilisateur->getCampus())
            ->andWhere('DATE_ADD(s.dateHeureDebut, s.duree, \'MINUTE\') > :date')
            ->setParameter('date', new \DateTime('-1 month'))
            ->orderBy('s.dateHeureDebut', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findByCriteres($filtre, $utilisateur)
    {

        $query = $this->queryBuilderSortie($utilisateur);

        // Si la case "inscrit" est cochée
        if ($filtre->getEstInscrit()) {
            $query->andWhere(':inscrit MEMBER OF s.participants')
                ->setParameter('inscrit', $utilisateur);
        }

        // Si la case "non inscrit" est cochée
        if ($filtre->getNonInscrit()) {
            $query->andWhere(':inscrit NOT MEMBER OF s.participants AND s.organisateur != :inscrit')
                ->setParameter('inscrit', $utilisateur);
        }

        // Si la case "sortie passée" est cochée
        if ($filtre->getSortiesPassees()) {
            $query->andWhere('DATE_ADD(s.dateHeureDebut, s.duree, \'MINUTE\') <= :date')
                ->setParameter('date', new \DateTime('-1 month'));
        }
        else {
            $query->andWhere('DATE_ADD(s.dateHeureDebut, s.duree, \'MINUTE\') > :date')
                ->setParameter('date', new \DateTime('-1 month'));
        }

        // Si la case organisateur est cochée
        if ($filtre->getEstOrganisateur()) {
            $query->andWhere('s.organisateur = :organisateur')
                ->setParameter('organisateur', $utilisateur);
        }

        if ($filtre->getNomSortie()) {
            $query->andWhere('s.nom LIKE :nomSortie')
                ->setParameter('nomSortie', '%' .$filtre->getNomSortie(). '%');
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
}
