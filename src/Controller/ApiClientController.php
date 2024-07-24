<?php

namespace App\Controller;

use App\Entity\Client;
use App\Repository\ClientRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ApiClientController extends AbstractController
{
    #[Route('/api/client/new', name: 'new_client', methods: 'post')]
    public function index(Request $request, EntityManagerInterface $manager): Response
    {
        $data = json_decode($request->getContent(), true);

        if ($data) {
            $client = new Client();
            if (isset($data['first_name']) && !empty($data['first_name'])) {
                $client->setFirstName($data['first_name']);
            }
            if (isset($data['last_name']) && !empty($data['last_name'])) {
                $client->setLastName($data['last_name']);
            }
            if (isset($data['job']) && !empty($data['job'])) {
                $client->setJob($data['job']);
            }
            if (isset($data['age']) && !empty($data['age'])) {
                $client->setAge($data['age']);
            }
            if (isset($data['location']) && !empty($data['location'])) {
                $client->setLocation($data['location']);
            }
            if (isset($data['mail']) && !empty($data['mail'])) {
                $client->setMail($data['mail']);
            }
            if (isset($data['phone']) && !empty($data['phone'])) {
                $client->setPhone($data['phone']);
            }
            $client->setCreatedAt(new \DateTime());
            $manager->persist($client);
            $manager->flush();
            return $this->json([
                'client' => $client

            ]);
        }
        return $this->json(['message' => 'no data' ]);
    }
    #[Route('/api/client/edit/{id}', name: 'edit_client', methods: 'post')]
    public function edit( $id,ClientRepository $repository ,Request $request, EntityManagerInterface $manager): Response
    {

        $client = $repository->find($id);
        if(!$client){
            return $this->json(['message'=>'no client found']);
        }


        $data = json_decode($request->getContent(), true);

        if ($data) {
            if (isset($data['first_name']) && !empty($data['first_name'])) {
                $client->setFirstName($data['first_name']);
            }
            if (isset($data['last_name']) && !empty($data['last_name'])) {
                $client->setLastName($data['last_name']);
            }
            if (isset($data['job']) && !empty($data['job'])) {
                $client->setJob($data['job']);
            }
            if (isset($data['age']) && !empty($data['age'])) {
                $client->setAge($data['age']);
            }
            if (isset($data['location']) && !empty($data['location'])) {
                $client->setLocation($data['location']);
            }
            if (isset($data['mail']) && !empty($data['mail'])) {
                $client->setMail($data['mail']);
            }
            if (isset($data['phone']) && !empty($data['phone'])) {
                $client->setPhone($data['phone']);
            }
            $client->setCreatedAt(new \DateTime());
            $manager->persist($client);
            $manager->flush();
            return $this->json([
                'client' => $client
            ]);
        }
        return $this->json(['message' => 'no data' ]); return $this->json(['client'=>$client]);

    }
    #[Route('/api/client/{id}', name: 'get_client', methods: 'get')]
    public function getClient($id, ClientRepository $repository): Response
    {
        $client = $repository->find($id);
        if(!$client){
            return $this->json(['message'=>'no client found']);
        }
        return $this->json(['client'=>$client]);

    }
}
