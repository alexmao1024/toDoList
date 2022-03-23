<?php

namespace App\Repository;

use App\Entity\WorkSpace;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method WorkSpace|null find($id, $lockMode = null, $lockVersion = null)
 * @method WorkSpace|null findOneBy(array $criteria, array $orderBy = null)
 * @method WorkSpace[]    findAll()
 * @method WorkSpace[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class WorkSpaceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, WorkSpace::class);
    }

    // /**
    //  * @return WorkSpace[] Returns an array of WorkSpace objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('w')
            ->andWhere('w.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('w.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?WorkSpace
    {
        return $this->createQueryBuilder('w')
            ->andWhere('w.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
