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
            } else {
                return $this->json(['message' => 'no first_name send']);
            }
            if (isset($data['last_name']) && !empty($data['last_name'])) {
                $client->setLastName($data['last_name']);
            } else {
                return $this->json(['message' => 'no last_name send']);
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
            $client->setOwner($this->getUser());
            $client->setState('active');
            $manager->persist($client);
            $manager->flush();


            return $this->json($this->getDataClient($client));
        }
        return $this->json(['message' => 'no data']);
    }

    #[Route('/api/client/edit/{id}', name: 'edit_client', methods: 'put')]
    public function edit($id, ClientRepository $repository, Request $request, EntityManagerInterface $manager): Response
    {

        $client = $repository->find($id);
        if (!$client) {
            return $this->json(['message' => 'no client found']);
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
            return $this->json($this->getDataClient($client));
        }
        return $this->json(['message' => 'no data']);

    }

    #[Route('/api/client/delete/{id}', name: 'delete_client', methods: 'delete')]
    public function delete($id, ClientRepository $repository, EntityManagerInterface $manager): Response
    {

        $client = $repository->find($id);
        if (!$client) {
            return $this->json(['message' => 'no client found']);
        }
        if (!$client->getOwner() == $this->getUser()) {
            return $this->json(['message' => 'access denied to this client, not yours']);
        }
        $client->setState('deleted');

        $manager->persist($client);
        $manager->flush();
        return $this->json(['message' => 'ok']);

    }

    #[Route('/api/client/deleteforce/{id}', name: 'delete_force_client', methods: 'delete')]
    public function deleteforce($id, ClientRepository $repository, EntityManagerInterface $manager): Response
    {

        $client = $repository->find($id);
        if (!$client) {
            return $this->json(['message' => 'no client found']);
        }
        if (!$client->getOwner() == $this->getUser()) {
            return $this->json(['message' => 'access denied to this client, not yours']);
        }
        $manager->remove($client);
        $manager->flush();
        return $this->json(['message' => 'ok']);

    }

    #[Route('/api/client/{id}', name: 'get_client', methods: 'get')]
    public function getClient($id, ClientRepository $repository): Response
    {
        $client = $repository->find($id);
        if (!$client) {
            return $this->json(['message' => 'no client found']);
        }
        return $this->json($this->getDataClient($client));
    }

    #[Route('/api/clients', name: 'get_clients', methods: 'get')]
    public function getClients(ClientRepository $repository, Request $request): Response
    {
        $datum = json_decode($request->getContent(), true);
        if ($datum) {
            if (isset($datum['display_deleted']) && !empty($datum['display_deleted'])) {
                if ($datum['display_deleted']) {
                    $data = [];
                    foreach ($repository->findBy([], ['createdAt' => 'ASC']) as $client) {
                        if ($client->getOwner() == $this->getUser()) {
                            $data[] = $this->getDataClient($client);
                        }
                    }
                    return $this->json($data);
                }
            }
        }

        $data = [];
        foreach ($repository->findBy([], ['createdAt' => 'ASC']) as $client) {
            if ($client->getOwner() == $this->getUser() && $client->getState() != 'deleted') {
                $data[] = $this->getDataClient($client);
            }

        }
        return $this->json($data);
    }

    #[Route('/api/client/{id}/projects', name: 'get_clients_projects', methods: 'get')]
    public function getClientProjects($id, ClientRepository $repository): Response
    {
        $client = $repository->find($id);
        if (!$client) {
            return $this->json(['message' => 'no client found']);
        }
        if (!$client->getOwner() == $this->getUser()) {
            return $this->json(['message' => 'access denied to this client, not yours']);
        }
        $data = [];
        foreach ($client->getProjects() as $project) {
            $data[] = $this->getShortDataProject($project);
        }


        return $this->json($data);
    }

    #[Route('/api/client/{id}/currentProjects', name: 'get_clients_currentprojects', methods: 'get')]
    public function getClientCurrentProjects($id, ClientRepository $repository): Response
    {
        $client = $repository->find($id);
        if (!$client) {
            return $this->json(['message' => 'no client found']);
        }
        if (!$client->getOwner() == $this->getUser()) {
            return $this->json(['message' => 'access denied to this client, not yours']);
        }
        $data = [];
        foreach ($client->getCurrentProject() as $project) {
            $data[] = $this->getShortDataProject($project);
        }

        return $this->json($data);
    }

    public function getDataClient($client)
    {

        return [
            "id" => $client->getId(),
            "firstName" => $client->getFirstName(),
            "lastName" => $client->getLastName(),
            "job" => $client->getJob(),
            "age" => $client->getAge(),
            "location" => $client->getLocation(),
            "mail" => $client->getMail(),
            "phone" => $client->getPhone(),
            "createdAt" => $client->getCreatedAt(),
            "state" => $client->getState(),
            "owner" => $client->getOwner()->getEmail(),

        ];
    }

    public function getShortDataProject($project)
    {

        return [
            'id' => $project->getId(),
            'name' => $project->getName(),
            'startDate' => $project->getStartDate(),
            'endDate' => $project->getEndDate(),
        ];
    }
}
