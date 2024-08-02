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
    public function searchAcrossTables($searchTerm)
    {
        $entityManager = $this->getEntityManager();
        $projectQueryBuilder = $entityManager->createQueryBuilder();

        $projectQueryBuilder
            ->select('p')
            ->from(Project::class, 'p')
            ->innerJoin('p.client', 'c');


        $conditions = $projectQueryBuilder->expr()->orX(
            $projectQueryBuilder->expr()->like('p.name', ':searchTerm'),
            $projectQueryBuilder->expr()->like('c.firstName', ':searchTerm'),
            $projectQueryBuilder->expr()->like('c.lastName', ':searchTerm'),
            $projectQueryBuilder->expr()->like('c.job', ':searchTerm'),
            $projectQueryBuilder->expr()->like('c.location', ':searchTerm'),
            $projectQueryBuilder->expr()->like('c.mail', ':searchTerm'),
            $projectQueryBuilder->expr()->like('c.phone', ':searchTerm'),
            $projectQueryBuilder->expr()->like('c.siret', ':searchTerm')
        );

        $projectQueryBuilder->where($conditions)
            ->setParameter('searchTerm', '%' . $searchTerm . '%');


        if (is_numeric($searchTerm)) {

            $searchNumber = (int)$searchTerm;
            $projectQueryBuilder
                ->orWhere($projectQueryBuilder->expr()->eq('c.age', ':searchNumber'))
                ->setParameter('searchNumber', $searchNumber);
        }

      $searchDate = \DateTime::createFromFormat('Y-m-d', $searchTerm);
    if ($searchDate) {
        $formattedDate = $searchDate->format('Y-m-d');

        $dateConditions = $projectQueryBuilder->expr()->orX(
            $projectQueryBuilder->expr()->eq('p.startDate', ':searchDate'),
            $projectQueryBuilder->expr()->eq('p.endDate', ':searchDate'),

            $projectQueryBuilder->expr()->eq('c.createdAt', ':searchDate')
        );

        $projectQueryBuilder
            ->orWhere($dateConditions)
            ->setParameter('searchDate', $formattedDate);
    }

        return $projectQueryBuilder->getQuery()->getResult();
    }
}
