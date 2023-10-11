<?php

namespace App\Repository;

use App\Entity\GroupPost;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<GroupPost>
 *
 * @method GroupPost|null find($id, $lockMode = null, $lockVersion = null)
 * @method GroupPost|null findOneBy(array $criteria, array $orderBy = null)
 * @method GroupPost[]    findAll()
 * @method GroupPost[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GroupPostRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GroupPost::class);
    }

//    /**
//     * @return GroupPost[] Returns an array of GroupPost objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('g')
//            ->andWhere('g.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('g.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?GroupPost
//    {
//        return $this->createQueryBuilder('g')
//            ->andWhere('g.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
