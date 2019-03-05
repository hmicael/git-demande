<?php

namespace App\Repository;

use App\Entity\Parrain;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;

/**
 * @method Parrain|null find($id, $lockMode = null, $lockVersion = null)
 * @method Parrain|null findOneBy(array $criteria, array $orderBy = null)
 * @method Parrain[]    findAll()
 * @method Parrain[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ParrainRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Parrain::class);
    }

    public function getList($limit = 20, $offset = 1)
    {
        if (0 == $limit || 0 == $offset) {
            throw new \LogicException('$limit & $offstet must be greater than 0.');
        }

        $qb = $this
            ->createQueryBuilder('p')
            ->andWhere('p.filleuls IS NOT EMPTY')
            ->orderBy('p.userId', 'asc');

        $adpater = new DoctrineORMAdapter($qb);
        $pager = new Pagerfanta($adpater);
        $currentPage = ceil($offset / $limit);
        $pager->setCurrentPage($currentPage);
        $pager->setMaxPerPage((int)$limit);

        return $pager;
    }

    // /**
    //  * @return Parrain[] Returns an array of Parrain objects
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
    public function findOneBySomeField($value): ?Parrain
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
