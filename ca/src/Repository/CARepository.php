<?php

namespace App\Repository;

use App\Entity\CA;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method CA|null find($id, $lockMode = null, $lockVersion = null)
 * @method CA|null findOneBy(array $criteria, array $orderBy = null)
 * @method CA[]    findAll()
 * @method CA[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CARepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, CA::class);
    }

    public function findByYearMonth($userId, string $date)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.date LIKE :val')
            ->setParameter('val', $date)
            ->andWhere('c.userId = :userId')
            ->andWhere('c.orders IS NOT EMPTY')
            ->setParameter('userId', $userId)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function getList($userId, string $year, $limit = 20, $offset = 1)
    {
        if (0 == $limit || 0 == $offset) {
            throw new \LogicException('$limit & $offstet must be greater than 0.');
        }
        $qb = $this->createQueryBuilder('c')
            ->andWhere('c.date LIKE :val')
            ->setParameter('val', $year)
            ->andWhere('c.userId = :userId')
            ->andWhere('c.orders IS NOT EMPTY')
            ->setParameter('userId', $userId);
        $adpater = new DoctrineORMAdapter($qb);
        $pager = new Pagerfanta($adpater);
        $currentPage = ceil($offset / $limit);
        $pager->setCurrentPage($currentPage);
        $pager->setMaxPerPage((int)$limit);

        return $pager;
    }

//    /**
//     * @return CA[] Returns an arr ay of CA objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?CA
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
