<?php

namespace App\Repository;

use App\Entity\Filleul;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;

/**
 * @method Filleul|null find($id, $lockMode = null, $lockVersion = null)
 * @method Filleul|null findOneBy(array $criteria, array $orderBy = null)
 * @method Filleul[]    findAll()
 * @method Filleul[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FilleulRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Filleul::class);
    }

    public function getList($parrain, $limit = 20, $offset = 1)
    {
        $qb = $this
            ->createQueryBuilder('f')
            ->andWhere('f.parrain = :parrain')
            ->setParameter(':parrain', $parrain)
            ->orderBy('f.userId', 'asc');

        $adpater = new DoctrineORMAdapter($qb);
        $pager = new Pagerfanta($adpater);
        $currentPage = ceil(($offset) / $limit);
        $pager->setCurrentPage($currentPage);
        $pager->setMaxPerPage((int)$limit);

        return $pager;
    }

    public function getFilleulIndirectList($limit = 20, $offset = 1)
    {
        $qb = $this
            ->createQueryBuilder('f')
            ->andWhere('f.parrain IS NULL')
            ->orderBy('f.userId', 'asc');

        $adpater = new DoctrineORMAdapter($qb);
        $pager = new Pagerfanta($adpater);
        $currentPage = ceil(($offset) / $limit);
        $pager->setCurrentPage($currentPage);
        $pager->setMaxPerPage((int)$limit);

        return $pager;
    }

    // /**
    //  * @return Filleul[] Returns an array of Parrain objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Filleul
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
