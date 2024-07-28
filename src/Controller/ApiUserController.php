<?php

namespace App\Controller;

use App\Entity\Client;
use App\Repository\ClientRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ApiUserController extends AbstractController
{
    #[Route('/api/me', name: 'user_me')]
    public function index(): Response
    {
        return $this->json([
            'state' => 'OK',
            'value' =>[
                'id' => $this->getUser()->getId(),
                'mail' => $this->getUser()->getEmail()]
        ]);
    }

    #[Route('/api/user/{id}', name: 'user')]
    public function getOneUser($id, UserRepository $repository): Response
    {
        $user = $repository->find($id);
        if (!$user) {
            return $this->json([
                'state' => 'NDF',
                'value' => 'user'
            ]);  }

        return $this->json([
            'state' => 'OK',
            'value' => [
                'id' => $this->getUser()->getId(),
                'mail' => $this->getUser()->getEmail()]
        ]);

    }
}
