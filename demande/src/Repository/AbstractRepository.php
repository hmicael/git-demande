<?php
// src/Repository/AbstractRepository.php
namespace App\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;

abstract class AbstractRepository extends ServiceEntityRepository
{
    protected function paginate(QueryBuilder $qb, $limit = 20, $offset = 1)
    {
        if (0 == $limit || 0 == $offset) {
            throw new \LogicException('$limit & $offstet must be greater than 0.');
        }

        $adpater = new DoctrineORMAdapter($qb);
        $pager = new Pagerfanta($adpater);
        $currentPage = ceil($offset / $limit);
        $pager->setCurrentPage($currentPage);
        $pager->setMaxPerPage((int)$limit);

        return $pager;
    }
}
