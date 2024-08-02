<?php

namespace App\Repository;

use App\Entity\Client;
use App\Entity\Project;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Client>
 */
class ClientRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Client::class);
    }

    //    /**
    //     * @return Client[] Returns an array of Client objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('c.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Client
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
    public function searchAcrossTables($searchTerm)
    {
        $entityManager = $this->getEntityManager();
        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder
            ->select('c')
            ->from(Client::class, 'c')
            ->leftJoin('c.projects', 'p');


        $clientConditions = $queryBuilder->expr()->orX(
            $queryBuilder->expr()->like('c.firstName', ':searchTerm'),
            $queryBuilder->expr()->like('c.lastName', ':searchTerm'),
            $queryBuilder->expr()->like('c.job', ':searchTerm'),
            $queryBuilder->expr()->like('c.location', ':searchTerm'),
            $queryBuilder->expr()->like('c.mail', ':searchTerm'),
            $queryBuilder->expr()->like('c.phone', ':searchTerm'),
            $queryBuilder->expr()->like('c.siret', ':searchTerm')
        );
        $projectConditions = $queryBuilder->expr()->orX(
            $queryBuilder->expr()->like('p.name', ':searchTerm')
        );
        $queryBuilder->where($clientConditions)
            ->orWhere($projectConditions)
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

            // Add date conditions
            $queryBuilder
                ->orWhere($queryBuilder->expr()->eq('c.createdAt', ':searchDate'))
                ->orWhere($queryBuilder->expr()->eq('p.startDate', ':searchDate'))
                ->orWhere($queryBuilder->expr()->eq('p.endDate', ':searchDate'))
                ->setParameter('searchDate', $formattedDate);
        }

        return $queryBuilder->getQuery()->getResult();
    }
}