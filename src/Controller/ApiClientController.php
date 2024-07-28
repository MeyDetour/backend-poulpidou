<?php

namespace App\Controller;

use App\Entity\Client;
use App\Repository\ClientRepository;
use App\Repository\ProjectRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ApiClientController extends AbstractController
{

    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/api/client/new', name: 'new_client', methods: 'post')]
    public function index(Request $request, EntityManagerInterface $manager): Response
    {
        $data = json_decode($request->getContent(), true);

        if ($data) {
            $client = new Client();
            if (!isset($data['first_name']) || empty(trim($data['first_name']))) {
                return $this->json([
                    'state' => 'NED',
                    'value' => 'first_name'

                ]);
            }
            if (!isset($data['last_name']) || empty(trim($data['last_name']))) {
                return $this->json([
                    'state' => 'NED',
                    'value' => 'last_name'
                ]);
            }

            $client->setFirstName(ucfirst($data['first_name']));
            $client->setLastName(strtoupper($data['last_name']));

            if (isset($data['job']) && !empty(trim($data['job']))) {
                $client->setJob($data['job']);
            }
            if (isset($data['age']) && !empty(trim($data['age']))) {
                $isValid = filter_var($data['age'], FILTER_VALIDATE_INT) !== false && $data['age'] > 0;
                if (!$isValid) {
                    return $this->json([
                        'state' => 'IDT',
                        'value' => 'age'
                    ]);
                }
                $client->setAge($data['age']);
            }
            if (isset($data['location']) && !empty(trim($data['location']))) {
                $client->setLocation($data['location']);
            }
            if (isset($data['mail']) && !empty(trim($data['mail']))) {
                $client->setMail($data['mail']);
            }
            if (isset($data['phone']) && !empty(trim($data['phone']))) {
                $client->setPhone($data['phone']);
            }
            $client->setCreatedAt(new \DateTime());
            $client->setOwner($this->getUser());
            $client->setState('active');
            $manager->persist($client);
            $manager->flush();

            return $this->json([
                'state' => 'OK',
                'value' => $this->getDataClient($client)
            ]);
        }

        return $this->json(['state' => 'ND']);
    }

    #[
        Route('/api/client/edit/{id}', name: 'edit_client', methods: 'put')]
    public function edit($id, ClientRepository $repository, Request $request, EntityManagerInterface $manager): Response
    {

        $client = $repository->find($id);
        if (!$client) {
            return $this->json([
                'state' => 'NDF',
                'value' => 'client'
            ]);
        }
        if (!$client->getOwner() == $this->getUser()) {
            return $this->json([
                'state' => 'FO',
                'value' => 'client'
            ]);
        }
        $this->formatNames($client);


        $data = json_decode($request->getContent(), true);

        if ($data) {
            if (isset($data['first_name']) && !empty(trim($data['first_name']))) {
                $client->setFirstName($data['first_name']);
            }
            if (isset($data['last_name']) && !empty(trim($data['last_name']))) {
                $client->setLastName($data['last_name']);
            }
            if (isset($data['job']) && !empty(trim($data['job']))) {
                $client->setJob($data['job']);
            }
            if (isset($data['age']) && !empty(trim($data['age']))) {
                $isValid = filter_var($data['age'], FILTER_VALIDATE_INT) !== false && $data['age'] > 0;
                if (!$isValid) {
                    return $this->json([
                        'state' => 'IDT',
                        'value' => 'age'
                    ]);
                }
                $client->setAge($data['age']);
            }
            if (isset($data['location']) && !empty(trim($data['location']))) {
                $client->setLocation($data['location']);
            }
            if (isset($data['mail']) && !empty(trim($data['mail']))) {
                $client->setMail($data['mail']);
            }
            if (isset($data['phone']) && !empty(trim($data['phone']))) {
                $client->setPhone($data['phone']);
            }
            $client->setCreatedAt(new \DateTime());
            $manager->persist($client);
            $manager->flush();
            return $this->json($this->getDataClient($client));
        }
        return $this->json(['state' => 'ND']);

    }

    #[Route('/api/client/delete/{id}', name: 'delete_client', methods: 'delete')]
    public function delete($id, ClientRepository $repository, EntityManagerInterface $manager): Response
    {

        $client = $repository->find($id);
        if (!$client) {
            return $this->json([
                'state' => 'NDF',
                'value' => 'client'
            ]);
        }
        if ($client->getOwner() != $this->getUser()) {
            return $this->json([
                'state' => 'FO',
                'value' => 'client'
            ]);
        }
        $client->setState('deleted');

        $manager->persist($client);
        $manager->flush();
        return $this->json(['state' => 'OK']);

    }

    #[Route('/api/client/deleteforce/{id}', name: 'delete_force_client', methods: 'delete')]
    public function deleteforce($id, ClientRepository $repository, EntityManagerInterface $manager): Response
    {

        $client = $repository->find($id);
        if (!$client) {
            return $this->json([
                'state' => 'NDF',
                'value' => 'client'
            ]);
        }
        if (!$client->getOwner() == $this->getUser()) {
            return $this->json([
                'state' => 'FO',
                'value' => 'client'
            ]);
        }
        $manager->remove($client);
        $manager->flush();
        return $this->json(['state' => 'OK']);

    }

    #[Route('/api/client/{id}', name: 'get_client', methods: 'get')]
    public function getClient($id, ClientRepository $repository): Response
    {
        $client = $repository->find($id);
        if (!$client) {
            return $this->json([
                'state' => 'NDF',
                'value' => 'client'
            ]);
        }
        if (!$client->getOwner() == $this->getUser()) {
            return $this->json([
                'state' => 'FO',
                'value' => 'client'
            ]);
        }
        $this->formatNames($client);
        return $this->json([
            'state' => 'OK',
            'value' => $this->getDataClient($client)]);
    }

    /*   #[Route('/api/clients', name: 'get_clients', methods: 'get')]
       public function getClients(ClientRepository $repository, Request $request): Response
       {
           $datum = json_decode($request->getContent(), true);
           if ($datum) {
               if (isset($datum['display_deleted']) && !empty(trim($datum['display_deleted']))) {
                   if ($datum['display_deleted']) {
                       $data = [];
                       foreach ($repository->findBy([], ['createdAt' => 'ASC']) as $client) {
                           if ($client->getOwner() == $this->getUser()) {
                               $data[] = $this->getDataClient($client);
                           }
                       }
                       return $this->json([
                           'state' => 'OK',
                           'value' => $data]);
                   }
               }
           }

           $data = [];
           foreach ($repository->findBy([], ['createdAt' => 'ASC']) as $client) {
               if ($client->getOwner() == $this->getUser() && $client->getState() != 'deleted') {
                   $data[] = $this->getDataClient($client);
               }
           }
           return $this->json([
               'state' => 'OK',
               'value' => $data]);
       }*/

    #[Route('/api/clients', name: 'get_clients', methods: 'get')]
    public function getClients(ClientRepository $repository, Request $request, EntityManagerInterface $manager): Response
    {
        $datum = json_decode($request->getContent(), true);

        $data = [];
        $display_delete = false;
        $order_by = false;

        if ($datum) {
            if (isset($datum['display_deleted']) && !empty(trim($datum['display_deleted']))) {
                if ($datum['display_deleted']) {
                    //verify if it's  ==true
                    $display_delete = true;
                }
            }
            if (isset($datum['order_by']) && !empty(trim($datum['order_by']))) {

                $order_by = $datum['order_by'];
            }

        }

        $arrayToIterate = $repository->findBy(['owner' => $this->getUser()], ['createdAt' => 'ASC']);
        if ($order_by == 'name') {
            $arrayToIterate =      $repository->findBy(['owner' => $this->getUser()], ['lastName' => 'ASC', 'firstName' => 'ASC']);
        }
        foreach ($arrayToIterate as $client) {


            $this->formatNames($client);
            if ($display_delete && $client->getState() == 'deleted' || $client->getState() != 'deleted') {

                $data[] = $this->getDataClient($client);
            }
        }
        $manager->flush();


        return $this->json([
            'state' => 'OK',
            'value' => $data]);
    }

    #[Route('/api/client/{id}/projects', name: 'get_clients_projects', methods: 'get')]
    public function getClientProjects($id, ClientRepository $repository): Response
    {

        $client = $repository->find($id);
        if (!$client) {
            return $this->json([
                'state' => 'NDF',
                'value' => 'client'
            ]);
        }
        if (!$client->getOwner() == $this->getUser()) {
            return $this->json([
                'state' => 'FO',
                'value' => 'client'
            ]);
        }
        $this->formatNames($client);
        $data = [];
        foreach ($client->getProjects() as $project) {
            $data[] = $this->getShortDataProject($project);
        }

        return $this->json([
            'state' => 'OK',
            'value' => $data]);
    }

    #[Route('/api/client/{id}/currentProjects', name: 'get_clients_currentprojects', methods: 'post')]
    public function getClientCurrentProjects($id, ClientRepository $repository, Request $request): Response
    {
        $client = $repository->find($id);
        if (!$client) {
            return $this->json([
                'state' => 'NDF',
                'value' => 'client'
            ]);
        }
        if (!$client->getOwner() == $this->getUser()) {
            return $this->json([
                'state' => 'FO',
                'value' => 'client'
            ]);
        }
        $datum = json_decode($request->getContent(), true);

        if ($datum) {

            if (isset($datum['display_deleted']) && !empty(trim($datum['display_deleted']))) {
                if ($datum['display_deleted']) {
                    $data = [];
                    foreach ($client->getCurrentProject() as $project) {

                        if ($project->getOwner() == $this->getUser()) {
                            $data[] = $this->getShortDataProject($project);
                        }
                    }
                    return $this->json([
                        'state' => 'OK',
                        'value' => $data]);
                }
            }
        }

        $data = [];
        foreach ($client->getCurrentProject() as $project) {
            if ($project->getState() != 'deleted') {

                $data[] = $this->getShortDataProject($project);
            }
        }
        return $this->json([
            'state' => 'OK',
            'value' => $data]);
    }

    #[Route('/api/client/{id}/currentProjects/add', name: 'add_clients_currentprojects', methods: 'post')]
    public function addClientCurrentProjects($id, ClientRepository $repository, ProjectRepository $projectRepository, Request $request, EntityManagerInterface $manager): Response
    {
        $client = $repository->find($id);
        if (!$client) {
            return $this->json([
                'state' => 'NDF',
                'value' => 'client'
            ]);
        }
        if (!$client->getOwner() == $this->getUser()) {
            return $this->json([
                'state' => 'FO',
                'value' => 'client'
            ]);
        }
        $datum = json_decode($request->getContent(), true);

        if ($datum) {
            if (isset($datum['project_id']) && !empty(trim($datum['project_id']))) {
                $project = $projectRepository->find($datum['project_id']);
                if (!$project) {
                    return $this->json([
                        'state' => 'NDF',
                        'value' => 'project'
                    ]);
                }
                if (!$project->getOwner() == $this->getUser()) {
                    return $this->json([
                        'state' => 'FO',
                        'value' => 'project'
                    ]);
                }
                $client->addCurrentProject($project);
                $manager->persist($client);
                $manager->flush();
                return $this->json([
                    'state' => 'OK'
                ]);
            } else {
                return $this->json([
                    'state' => 'NED',
                    'value' => 'project_id'
                ]);
            }
        }
        return $this->json([
            'state' => 'ND'
        ]);
    }

    #[Route('/api/client/{id}/currentProjects/remove', name: 'remove_clients_currentprojects', methods: 'put')]
    public function removeClientCurrentProjects($id, ClientRepository $repository, ProjectRepository $projectRepository, Request $request, EntityManagerInterface $manager): Response
    {
        $client = $repository->find($id);
        if (!$client) {
            return $this->json([
                'state' => 'NDF',
                'value' => 'client'
            ]);
        }
        if (!$client->getOwner() == $this->getUser()) {
            return $this->json([
                'state' => 'FO',
                'value' => 'client'
            ]);
        }
        $datum = json_decode($request->getContent(), true);

        if ($datum) {
            if (!isset($datum['project_id']) || empty(trim($datum['project_id']))) {
                return $this->json([
                    'state' => 'NED',
                    'value' => 'project_id'
                ]);
            }
            $project = $projectRepository->find($datum['project_id']);
            if (!$project) {
                return $this->json([
                    'state' => 'NDF',
                    'value' => 'project'
                ]);
            }
            if (!$project->getOwner() == $this->getUser()) {
                return $this->json([
                    'state' => 'FO',
                    'value' => 'project'
                ]);
            }
            $client->removeCurrentProject($project);
            $manager->persist($client);
            $manager->flush();
            return $this->json([
                'state' => 'OK'
            ]);

        }
        return $this->json([
            'state' => 'ND'
        ]);
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

    public function formatNames($client)
    {
        $client->setFirstName(ucfirst($client->getFirstName()));
        $client->setLastName(strtoupper($client->getLastName()));
        $this->entityManager->persist($client);
        $this->entityManager->flush();
    }
}
