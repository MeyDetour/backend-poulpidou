<?php

namespace App\Repository;

use App\Entity\Task;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Task>
 */
class TaskRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Task::class);
    }
    public function findBetweenDate($date1,$date2,$owner): ?Array
    {
        $today = new \DateTimeImmutable();
        return $this->createQueryBuilder('e')
            ->where('e.createdAt >= :startDate')
            ->andWhere('e.createdAt <= :endDate')
            ->andWhere('e.createdAt <= :today')
            ->andWhere('e.owner = :owner')
            ->setParameter('startDate', $date1->format('Y-m-d'))
            ->setParameter('endDate', $date2->format('Y-m-d'))
            ->setParameter('today', $today->format('Y-m-d'))
            ->setParameter('owner', $owner)
            ->orderBy('e.createdAt', 'ASC')
            ->getQuery()
            ->getResult();
    }
    //    /**
    //     * @return Task[] Returns an array of Task objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('t')
    //            ->andWhere('t.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('t.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Task
    //    {
    //        return $this->createQueryBuilder('t')
    //            ->andWhere('t.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
