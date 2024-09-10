<?php

namespace App\Repository;

use App\Entity\Project;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Project>
 */
class ProjectRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Project::class);
    }

    //    /**
    //     * @return Project[] Returns an array of Project objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('p.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Project
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }

    public function findBetweenDate($date1,$date2,$owner): ?Array
    {
dd($date1,$date2);
        return $this->createQueryBuilder('p')
            ->select('COUNT(p)')
            ->where('p.createdAt >= :startDate')
            ->andWhere('p.createdAt <= :endDate')
            ->andWhere('p.owner = :owner')
            ->setParameter('startDate', $date1->format('Y-m-d H:i:s'))
            ->setParameter('endDate', $date2->format('Y-m-d H:i:s'))
            ->setParameter('owner', $owner)
            ->orderBy('p.createdAt', 'ASC')
            ->getQuery()
            ->getSingleScalarResult();
    }
    public function searchAcrossTables($searchTerm)
    {
        $entityManager = $this->getEntityManager();
        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder
            ->select('p')
            ->from(Project::class, 'p')
            ->innerJoin('p.client', 'c')
            ->innerJoin('p.chat', 'ch')
            ->where($queryBuilder->expr()->neq('c.state', ':deletedStates'))
            ->andWhere($queryBuilder->expr()->neq('p.state', ':deletedStates'))
            ->setParameter('deletedStates', 'deleted');

        $chatConditions = $queryBuilder->expr()->orX(
            $queryBuilder->expr()->like('ch.name', ':searchTerm'),
        );
        $projectConditions = $queryBuilder->expr()->orX(
            $queryBuilder->expr()->like('p.name', ':searchTerm'),
            $queryBuilder->expr()->like('p.note', ':searchTerm'),
          );
        $clientConditions = $queryBuilder->expr()->orX(
            $queryBuilder->expr()->like('c.firstName', ':searchTerm'),
            $queryBuilder->expr()->like('c.lastName', ':searchTerm'),
            $queryBuilder->expr()->like('c.job', ':searchTerm'),
            $queryBuilder->expr()->like('c.location', ':searchTerm'),
            $queryBuilder->expr()->like('c.mail', ':searchTerm'),
            $queryBuilder->expr()->like('c.phone', ':searchTerm'),
            $queryBuilder->expr()->like('c.siret', ':searchTerm')
        );
         $queryBuilder->andWhere(
            $queryBuilder->expr()->orX($chatConditions, $projectConditions, $clientConditions)
        )
            ->setParameter('searchTerm', '%' . $searchTerm . '%');

        if (is_numeric($searchTerm)) {

            $searchNumber = (int)$searchTerm;
            $queryBuilder
                ->orWhere($queryBuilder->expr()->eq('c.age', ':searchNumber'))
                ->setParameter('searchNumber', $searchNumber);
        }

        $searchDate = \DateTime::createFromFormat('Y-m-d', $searchTerm);
        if ($searchDate) {
            $formattedDate = $searchDate->format('Y-m-d');

            $dateConditions = $queryBuilder->expr()->orX(
                $queryBuilder->expr()->eq('p.startDate', ':searchDate'),
                $queryBuilder->expr()->eq('p.endDate', ':searchDate'),

                $queryBuilder->expr()->eq('c.createdAt', ':searchDate')
            );

            $queryBuilder
                ->orWhere($dateConditions)
                ->setParameter('searchDate', $formattedDate);
        }

        return $queryBuilder->getQuery()->getResult();
    }
}
