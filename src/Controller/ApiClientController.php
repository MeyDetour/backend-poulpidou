<?php

namespace App\Controller;

use App\Entity\Client;
use App\Repository\ClientRepository;
use App\Repository\ProjectRepository;
use App\Service\DateService;
use App\Service\LogService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ApiClientController extends AbstractController
{

    private EntityManagerInterface $entityManager;
    private LogService $logService;
    private DateService $dateService;

    public function __construct(EntityManagerInterface $entityManager, DateService $dateService ,LogService $logService)
    {
        $this->entityManager = $entityManager;
        $this->logService = $logService;
        $this->dateService = $dateService;
    }

    #[Route('/api/client/new', name: 'new_client', methods: 'post')]
    public function index(Request $request, EntityManagerInterface $manager,): Response
    {
        try {

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
                if (isset($data['siret']) && !empty(trim($data['siret']))) {
                    $client->setSiret($data['siret']);
                }
                if (isset($data['phone']) && !empty(trim($data['phone']))) {
                    $client->setPhone($data['phone']);
                }
                $client->setCreatedAt(new \DateTime());
                $client->setOwner($this->getUser());
                $client->setState('active');
                $manager->persist($client);
                $manager->flush();
                $this->logService->createLog('ACTION', 'create new client (' . $client->getId() . ' | ' . $client->getFirstName() . ' ' . $client->getLastName() . ')', null);
                return $this->json([
                    'state' => 'OK',
                    'value' => $this->getDataClient($client)
                ]);
            }

            return $this->json(['state' => 'ND']);
        } catch (\Exception $exception) {
            $this->logService->createLog('ERROR', ' Internal Servor Error at |' . $exception->getFile() . ' | line |' . $exception->getLine(), $exception->getMessage());


            return $this->json([
                'state' => 'ISE',
                'value' => ' Internal Servor Error : ' . $exception->getMessage() . ' at |' . $exception->getFile() . ' | line |' . $exception->getLine()

            ]);
        }

    }

    #[Route('/api/client/edit/{id}', name: 'edit_client', methods: 'put')]
    public function edit($id, ClientRepository $repository, Request $request, EntityManagerInterface $manager): Response
    {
        try {
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
                if (isset($data['siret']) && !empty(trim($data['siret']))) {
                    $client->setSiret($data['siret']);
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
                $this->logService->createLog('ACTION', 'Edit client (' . $client->getId() . ' | ' . $client->getFirstName() . ' ' . $client->getLastName() . ')', null);
                return $this->json($this->getDataClient($client));
            }
            return $this->json(['state' => 'ND']);
        } catch (\Exception $exception) {
            $this->logService->createLog('ERROR', ' Internal Servor Error at |' . $exception->getFile() . ' | line |' . $exception->getLine(), $exception->getMessage());


            return $this->json([
                'state' => 'ISE',
                'value' => ' Internal Servor Error : ' . $exception->getMessage() . ' at |' . $exception->getFile() . ' | line |' . $exception->getLine()

            ]);
        }
    }

    #[Route('/api/client/delete/{id}', name: 'delete_client', methods: 'delete')]
    public function delete($id, ClientRepository $repository, EntityManagerInterface $manager): Response
    {
        try {
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
            $this->logService->createLog('DELETE', 'delete client (' . $client->getId() . ' | ' . $client->getFirstName() . ' ' . $client->getLastName() . ')', null);
            return $this->json(['state' => 'OK']);
        } catch (\Exception $exception) {
            $this->logService->createLog('ERROR', ' Internal Servor Error at |' . $exception->getFile() . ' | line |' . $exception->getLine(), $exception->getMessage());


            return $this->json([
                'state' => 'ISE',
                'value' => ' Internal Servor Error : ' . $exception->getMessage() . ' at |' . $exception->getFile() . ' | line |' . $exception->getLine()

            ]);
        }

    }

    #[Route('/api/client/deleteforce/{id}', name: 'delete_force_client', methods: 'delete')]
    public function deleteforce($id, ClientRepository $repository, EntityManagerInterface $manager): Response
    {
        try {
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
            $cpForLog = '(' . $client->getId() . ' | ' . $client->getFirstName() . ' ' . $client->getLastName() . ')';
            $manager->remove($client);
            $manager->flush();

            $this->logService->createLog('DELETE', 'delete force client ' . $cpForLog, null);
            return $this->json(['state' => 'OK']);

        } catch (\Exception $exception) {
            $this->logService->createLog('ERROR', ' Internal Servor Error at |' . $exception->getFile() . ' | line |' . $exception->getLine(), $exception->getMessage());


            return $this->json([
                'state' => 'ISE',
                'value' => ' Internal Servor Error : ' . $exception->getMessage() . ' at |' . $exception->getFile() . ' | line |' . $exception->getLine()

            ]);
        }

    }

    #[Route('/api/client/{id}', name: 'get_client', methods: 'get')]
    public function getClient($id, ClientRepository $repository): Response
    {
        try {

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
        } catch (\Exception $exception) {
            $this->logService->createLog('ERROR', ' Internal Servor Error at |' . $exception->getFile() . ' | line |' . $exception->getLine(), $exception->getMessage());


            return $this->json([
                'state' => 'ISE',
                'value' => ' Internal Servor Error : ' . $exception->getMessage() . ' at |' . $exception->getFile() . ' | line |' . $exception->getLine()

            ]);
        }

    }


    #[Route('/api/clients', name: 'get_clients', methods: 'get')]
    public function getClients(ClientRepository $repository, Request $request, EntityManagerInterface $manager): Response
    {
        try {
            $datum = json_decode($request->getContent(), true);

            $data = [];
            $display_delete = false;
            $order_by = false;
            if ($datum) {

                if (isset($datum['displayDeleted'])) {
                    if ($datum['displayDeleted'] == true) {
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
                $arrayToIterate = $repository->findBy(['owner' => $this->getUser()], ['lastName' => 'ASC', 'firstName' => 'ASC']);
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
        } catch (\Exception $exception) {
            $this->logService->createLog('ERROR', ' Internal Servor Error at |' . $exception->getFile() . ' | line |' . $exception->getLine(), $exception->getMessage());


            return $this->json([
                'state' => 'ISE',
                'value' => ' Internal Servor Error : ' . $exception->getMessage() . ' at |' . $exception->getFile() . ' | line |' . $exception->getLine()

            ]);
        }

    }

    #[Route('/api/client/{id}/projects', name: 'get_clients_projects', methods: 'get')]
    public function getClientProjects($id, ClientRepository $repository): Response
    {
        try {
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
        } catch (\Exception $exception) {
            $this->logService->createLog('ERROR', ' Internal Servor Error at |' . $exception->getFile() . ' | line |' . $exception->getLine(), $exception->getMessage());


            return $this->json([
                'state' => 'ISE',
                'value' => ' Internal Servor Error : ' . $exception->getMessage() . ' at |' . $exception->getFile() . ' | line |' . $exception->getLine()

            ]);
        }

    }

    #[Route('/api/client/{id}/currentProjects', name: 'get_clients_currentprojects', methods: 'post')]
    public function getClientCurrentProjects($id, ClientRepository $repository, Request $request): Response
    {
        try {
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

                if (isset($datum['displayDeleted']) && !empty(trim($datum['displayDeleted']))) {
                    if ($datum['displayDeleted']) {
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
        } catch (\Exception $exception) {
            $this->logService->createLog('ERROR', ' Internal Servor Error at |' . $exception->getFile() . ' | line |' . $exception->getLine(), $exception->getMessage());


            return $this->json([
                'state' => 'ISE',
                'value' => ' Internal Servor Error : ' . $exception->getMessage() . ' at |' . $exception->getFile() . ' | line |' . $exception->getLine()

            ]);
        }
    }

    #[Route('/api/client/{id}/currentProjects/add', name: 'add_clients_currentprojects', methods: 'post')]
    public function addClientCurrentProjects($id, ClientRepository $repository, ProjectRepository $projectRepository, Request $request, EntityManagerInterface $manager): Response
    {
        try {
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
                    $this->logService->createLog('ACTION', ' add current Project (' . $project->getId() . ':' . $project->getName() . ') to client (' . $client->getId() . ' | ' . $client->getFirstName() . ' ' . $client->getLastName() . ')', null);

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
        } catch (\Exception $exception) {
            $this->logService->createLog('ERROR', ' Internal Servor Error at |' . $exception->getFile() . ' | line |' . $exception->getLine(), $exception->getMessage());


            return $this->json([
                'state' => 'ISE',
                'value' => ' Internal Servor Error : ' . $exception->getMessage() . ' at |' . $exception->getFile() . ' | line |' . $exception->getLine()

            ]);
        }
    }

    #[Route('/api/client/{id}/currentProjects/remove', name: 'remove_clients_currentprojects', methods: 'put')]
    public function removeClientCurrentProjects($id, ClientRepository $repository, ProjectRepository $projectRepository, Request $request, EntityManagerInterface $manager): Response
    {
        try {

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
                $this->logService->createLog('ACTION', ' remove current Project (' . $project->getId() . ':' . $project->getName() . ') to client (' . $client->getId() . ' | ' . $client->getFirstName() . ' ' . $client->getLastName() . ')', null);

                $manager->flush();
                return $this->json([
                    'state' => 'OK'
                ]);

            }
            return $this->json([
                'state' => 'ND'
            ]);
        } catch (\Exception $exception) {
            $this->logService->createLog('ERROR', ' Internal Servor Error at |' . $exception->getFile() . ' | line |' . $exception->getLine(), $exception->getMessage());


            return $this->json([
                'state' => 'ISE',
                'value' => ' Internal Servor Error : ' . $exception->getMessage() . ' at |' . $exception->getFile() . ' | line |' . $exception->getLine()

            ]);
        }

    }

    #[Route('/interface/{uuid}', name: 'interface_client', methods: 'get')]
    public function getDataForClientInterface($uuid, ProjectRepository $projectRepository, Request $request, EntityManagerInterface $manager): Response
    {
        try {

            $project = $projectRepository->findOneBy(['uuid'=>$uuid]);
            if (!$project) {
                return $this->json([
                    'state' => 'NDF',
                    'value' => 'project'
                ]);
            }

            $client = $project->getClient();

            return $this->json([
                'state' => 'OK',
                'value' => [
                    'project' => [
                        'startDate' =>$this->dateService->formateDate( $project->getStartDate()),
                        'endDate' => $this->dateService->formateDate( $project->getEndDate()),
                        'price' => $project->getTotalPrice(),
                        'maintenancePercentage' => $project->getMaintenancePercentage()

                    ],
                    'client' => [
                        'id' => $client->getId(),
                        "firstName" => $client->getFirstName(),
                        "lastName" => $client->getLastName(),

                    ]


                ]

            ]);
        } catch (\Exception $exception) {
            $this->logService->createLog('ERROR', ' Internal Servor Error at |' . $exception->getFile() . ' | line |' . $exception->getLine(), $exception->getMessage());


            return $this->json([
                'state' => 'ISE',
                'value' => ' Internal Servor Error : ' . $exception->getMessage() . ' at |' . $exception->getFile() . ' | line |' . $exception->getLine()

            ]);
        }

    }

    #[Route('/interface/project/{id}/online', name: 'online_client', methods: 'post')]
    public function setClientOfflineOrOnline($id, ProjectRepository $projectRepository, Request $request, EntityManagerInterface $manager): Response
    {
        try {
            $data = json_decode($request->getContent(), true);
            if (!$data) {
                return $this->json([
                    'state' => 'ND',
                    'value' => 'project'
                ]);
            }
            if (!isset($data['online'])) {
                return $this->json([
                    'state' => 'NED',
                    'value' => 'online'
                ]);

            }
            if (!is_bool($data['online'])) {
                return $this->json([
                    'state' => 'IDT',
                    'value' => 'online'
                ]);

            }

            $project = $projectRepository->find($id);
            if (!$project) {
                return $this->json([
                    'state' => 'NDF',
                    'value' => 'project'
                ]);
            }

            $client = $project->getClient();
            $client->setOnline($data['online']);
            $manager->persist($client);
            $manager->flush();
            return $this->json([
                'state' => 'OK',
                'value' => $this->getDataClient($client)
            ]);
        } catch (\Exception $exception) {
            $this->logService->createLog('ERROR', ' Internal Servor Error at |' . $exception->getFile() . ' | line |' . $exception->getLine(), $exception->getMessage());


            return $this->json([
                'state' => 'ISE',
                'value' => ' Internal Servor Error : ' . $exception->getMessage() . ' at |' . $exception->getFile() . ' | line |' . $exception->getLine()

            ]);
        }

    }

    #[Route('/api/search/client', name: 'search_client', methods: 'get')]
    public function searchClients(Request $request, ClientRepository $clientRepository): Response
    {
        try {
            $datum = json_decode($request->getContent(), true);

            $data = [];
            if ($datum) {
                if (isset($datum['searchTerm']) && !empty(trim($datum['searchTerm']))) {
                    $clients = $clientRepository->searchAcrossTables($datum['searchTerm']);
                    $dataToReturn = [];
                    foreach ($clients as $client) {
                        if ($client->getState() != 'deleted') {
                            $dataToReturn[] = [
                                "id" => $client->getId(),
                                "firstName" => $client->getFirstName(),
                                "lastName" => $client->getLastName(),
                                "online" => $client->isOnline(),
                                "projectsNumber"=>count($client->getProjects()),
                                'date'=>$this->dateService->formateDate( $client->getCreatedAt()),
                            ];
                        }

                    }
                    return $this->json([
                        'state' => 'OK',
                        'value' => $dataToReturn
                    ]);
                }
            }

            return $this->json([
                'state' => 'ND'
            ]);
        } catch (\Exception $exception) {
            $this->logService->createLog('ERROR', ' Internal Servor Error at |' . $exception->getFile() . ' | line |' . $exception->getLine(), $exception->getMessage());


            return $this->json([
                'state' => 'ISE',
                'value' => ' Internal Servor Error : ' . $exception->getMessage() . ' at |' . $exception->getFile() . ' | line |' . $exception->getLine()

            ]);
        }
    }

    public function getDataClient($client)
    {
$links = [];
foreach ($client->getProjects() as $project) {
    $links[] = 'interface/'.$project->getUuid();
}
        return [
            "id" => $client->getId(),
            "firstName" => $client->getFirstName(),
            "lastName" => $client->getLastName(),
            "job" => $client->getJob(),
            "age" => $client->getAge(),
            "location" => $client->getLocation(),
            "mail" => $client->getMail(),
            "phone" => $client->getPhone(),
            "createdAt" => $this->dateService->formateDate( $client->getCreatedAt()),
            "state" => $client->getState(),
            "online" => $client->isOnline(),
            'links'=>$links

        ];
    }

    public function getShortDataProject($project)
    {

        return [
            'id' => $project->getId(),
            'name' => $project->getName(),
            'startDate' => $this->dateService->formateDate( $project->getStartDate()),
            'endDate' =>$this->dateService->formateDate(  $project->getEndDate()),
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
