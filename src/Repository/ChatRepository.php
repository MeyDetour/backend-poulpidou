<?php

namespace App\Repository;

use App\Entity\Chat;
use App\Entity\Client;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Chat>
 */
class ChatRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Chat::class);
    }

    //    /**
    //     * @return Chat[] Returns an array of Chat objects
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

    //    public function findOneBySomeField($value): ?Chat
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
            ->from(Chat::class, 'c')
            ->leftJoin('c.project', 'p')
            ->leftJoin('c.client', 'cl')
            ->where($queryBuilder->expr()->neq('cl.state', ':deletedStates'))
            ->andWhere($queryBuilder->expr()->neq('p.state', ':deletedStates'))
            ->setParameter('deletedStates', 'deleted');



        $chatConditions = $queryBuilder->expr()->orX(
            $queryBuilder->expr()->like('c.name', ':searchTerm'),
        );
        $clientConditions = $queryBuilder->expr()->orX(
            $queryBuilder->expr()->like('cl.firstName', ':searchTerm'),
            $queryBuilder->expr()->like('cl.lastName', ':searchTerm'),
            $queryBuilder->expr()->like('cl.job', ':searchTerm'),
            $queryBuilder->expr()->like('cl.location', ':searchTerm'),
            $queryBuilder->expr()->like('cl.mail', ':searchTerm'),
            $queryBuilder->expr()->like('cl.phone', ':searchTerm'),
            $queryBuilder->expr()->like('cl.siret', ':searchTerm')
        );
        $projectConditions = $queryBuilder->expr()->orX(
            $queryBuilder->expr()->like('p.name', ':searchTerm')
        );

        $queryBuilder->andWhere(
            $queryBuilder->expr()->orX($chatConditions, $projectConditions, $clientConditions)
        )
            ->setParameter('searchTerm', '%' . $searchTerm . '%');

        if (is_numeric($searchTerm)) {

            $searchNumber = (int)$searchTerm;
            $queryBuilder
                ->orWhere($queryBuilder->expr()->eq('cl.age', ':searchNumber'))
                ->setParameter('searchNumber', $searchNumber);
        }

        $searchDate = \DateTime::createFromFormat('Y-m-d', $searchTerm);
        if ($searchDate) {
            $formattedDate = $searchDate->format('Y-m-d');

            // Add date conditions
            $queryBuilder
                ->orWhere($queryBuilder->expr()->eq('cl.createdAt', ':searchDate'))
                ->orWhere($queryBuilder->expr()->eq('p.startDate', ':searchDate'))
                ->orWhere($queryBuilder->expr()->eq('p.endDate', ':searchDate'))
                ->setParameter('searchDate', $formattedDate);
        }

        return $queryBuilder->getQuery()->getResult();
    }
}
