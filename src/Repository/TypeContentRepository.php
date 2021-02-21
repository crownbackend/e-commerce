<?php

namespace App\Repository;

use App\Entity\TypeContent;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method TypeContent|null find($id, $lockMode = null, $lockVersion = null)
 * @method TypeContent|null findOneBy(array $criteria, array $orderBy = null)
 * @method TypeContent[]    findAll()
 * @method TypeContent[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TypeContentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TypeContent::class);
    }

    // /**
    //  * @return TypeContent[] Returns an array of TypeContent objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('t.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?TypeContent
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
