<?php

namespace App\Repository;

use App\Entity\Sortie;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Sortie|null find($id, $lockMode = null, $lockVersion = null)
 * @method Sortie|null findOneBy(array $criteria, array $orderBy = null)
 * @method Sortie[]    findAll()
 * @method Sortie[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SortieRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Sortie::class);
    }

    // /**
    //  * @return Sortie[] Returns an array of Sortie objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('s.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Sortie
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    public function search($user, $site, $nom, $dateDebut, $dateFin, $organised, $registered, $notRegistered, $passed)
    {
        $qb = $this->createQueryBuilder('s');

        if ($site != null and $site != 'all_sites') {
            $qb->andWhere('s.site = :site')
                ->setParameter('site', $site);
        }
        if ($nom != null) {
            $qb->andWhere('s.nom LIKE :nom')
                ->setParameter('nom', '%' . $nom . '%');
        }
        if ($dateDebut != null) {
            $qb->andWhere('s.dateHeureDebut >= :dateDebut')
                ->setParameter('dateDebut', $dateDebut);
        }
        if ($dateFin != null) {
            $qb->andWhere('s.dateLimiteInscription <= :dateFin')
                ->setParameter('dateFin', $dateFin);
        }
        if ($organised != null) {
            $qb->andWhere('s.organisateur = :organisateur')
                ->setParameter('organisateur', $user);
        }
        if ($registered != null && $notRegistered == null) {
            $qb->join('s.inscriptions', 'i')
                ->andWhere('i.participant = :registered')
                ->setParameter('registered', $user);
        }
        if ($notRegistered != null && $registered == null) {
            $q2 = $this->createQueryBuilder('s2')
                ->join('s2.inscriptions', 'i2')
                ->where('i2.participant = :user');

            $qb->andWhere('s.organisateur != :not_registered')
                ->andWhere('s.id NOT in(' . $q2->getDQL() . ')')
                ->setParameter('not_registered', $user)
                ->setParameter('user', $user);
        }
        if ($passed != null) {
            $qb->join('s.etat', 'e')
                ->andWhere('e.libelle = :passed')
                ->setParameter('passed', 'passée');
        }
        $qb->orderBy('s.dateHeureDebut', 'ASC');
        return $qb->getQuery()->getResult();
    }

}
