<?php

namespace App\Service;

use App\Entity\Logs;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;

class LogService
{
    private $entityManager;
    private $security;



    public function __construct(EntityManagerInterface $entityManager, Security $security)
    {
        $this->entityManager = $entityManager;
        $this->security = $security;
    }

    public function createLog($type,$message, $error)
    {
        $log = new Logs();
        $log->setType($type);
        $log->setAuthor($this->security->getUser());
        $log->setDate(new \DateTime());
        $log->setError($error);
        $log->setMessage($message);
        $log->setPatch(false);
        $this->entityManager->persist($log);
        $this->entityManager->flush();
    }
}