<?php

namespace App\Repository;

use App\Entity\Chat;
use App\Entity\Client;
use App\Entity\Invoice;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Invoice>
 */
class InvoiceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Invoice::class);
    }
    public function findBetweenDate($date1,$date2,$owner): ?Array
    {
        return $this->createQueryBuilder('e')
            ->join('e.project', 'p')
            ->where('e.createdAt >= :startDate')
            ->andWhere('e.createdAt <= :endDate')
            ->andWhere('p.owner = :owner')
            ->setParameter('startDate', $date1->format('Y-m-d'))
            ->setParameter('endDate', $date2->format('Y-m-d'))
            ->setParameter('owner', $owner)
            ->orderBy('e.createdAt', 'ASC')
            ->getQuery()
            ->getResult();
    }
    //     * @return Invoice[] Returns an array of Invoice objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('i')
    //            ->andWhere('i.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('i.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Invoice
    //    {
    //        return $this->createQueryBuilder('i')
    //            ->andWhere('i.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }

    public function findInvoicesOfClient(Client $client): ?array
    {
        $entityManager = $this->getEntityManager();
        $queryBuilder = $entityManager->createQueryBuilder();
          $queryBuilder
            ->select('i')
            ->from(Invoice::class, 'i')
            ->leftJoin('i.project', 'p')
            ->where($queryBuilder->expr()->eq('p.client', ':client'))
            ->setParameter('client', $client)
            ->orderBy('i.createdAt', 'ASC');

        // Execute the query and return the result
        return $queryBuilder->getQuery()->getResult();
    }
}
