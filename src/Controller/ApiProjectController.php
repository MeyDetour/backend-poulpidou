<?php

namespace App\Controller;

use App\Entity\Project;
use App\Repository\ClientRepository;
use App\Repository\ProjectRepository;
use App\Service\LogService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ApiProjectController extends AbstractController
{
    private LogService $logService;
    private array $listeOfFrameworks = ["symfony",
        "django",
        "react",
        "vue",
        "angular"];
    private array $listeOfOptions = ["mailServer",
        "phoneServer",
        "payingMethods",
        "account",
        "images"];
    private array $listeOfType = ["showcase",
        "eCommerce",
        "software",
        "app",
        "forum",
        "blog",
        "videoGame",
        "api"];
    private array $listeOfDevices = ["mobile",
        "computer",
        "television",
        "printer"];


    public function __construct(LogService $logService)
    {
        $this->logService = $logService;
    }

    #[Route('/api/project/new', name: 'app_api_project', methods: 'post')]
    public function index(Request $request, EntityManagerInterface $manager, ClientRepository $clientRepository): Response
    {

        $data = json_decode($request->getContent(), true);

        if ($data) {
            try {
                $project = new Project();


                if (!isset($data['identity']['client_id']) || empty(trim($data['identity']['client_id']))) {
                    return $this->json([
                        'state' => 'NED',
                        'value' => 'client_id'
                    ]);
                }

                if (!isset($data['identity']['name']) || empty(trim($data['identity']['name']))) {

                    return $this->json([
                        'state' => 'NED',
                        'value' => 'name'
                    ]);
                }

                $client = $clientRepository->find($data['identity']['client_id']);
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

                $project->setClient($client);
                $project->setName($data['identity']['name']);

                if (isset($data['identity']['figmaLink']) && !empty(trim($data['identity']['figmaLink']))) {
                    $project->setFigmaLink($data['identity']['figmaLink']);
                }
                if (isset($data['identity']['githubLink']) && !empty(trim($data['identity']['githubLink']))) {
                    $project->setGithubLink($data['identity']['githubLink']);
                }
                if (isset($data['estimatedPrice']) && !empty(trim($data['estimatedPrice']))) {
                    $isValid = $data['estimatedPrice'] > 0 && is_numeric($data['estimatedPrice']);
                    if (!$isValid) {
                        return $this->json([
                            'state' => 'IDT',
                            'value' => 'estimatedPrice'
                        ]);
                    }
                    $project->setEstimatedPrice($data['estimatedPrice']);
                }
                if (isset($data['maintenancePercentage']) && !empty(trim($data['maintenancePercentage']))) {
                    $isValid = $data['maintenancePercentage'] > 0 && is_numeric($data['maintenancePercentage']);
                    if (!$isValid) {
                        return $this->json([
                            'state' => 'IDT',
                            'value' => 'maintenancePercentage'
                        ]);
                    }
                    $project->setMaintenancePercentage($data['maintenancePercentage']);
                }

                if (isset($data['identity']['startDate']) && !empty(trim($data['identity']['startDate']))) {
                    $project->setStartDate($data['identity']['startDate']);
                }
                if (isset($data['end_date']) && !empty(trim($data['end_date']))) {
                    $project->setEndDate($data['end_date']);
                }
                if (isset($data['totalPrice']) && !empty(trim($data['totalPrice']))) {
                    $isValid = $data['totalPrice'] > 0 && is_numeric($data['total_price']);
                    if (!$isValid) {
                        return $this->json([
                            'state' => 'IDT',
                            'value' => 'total_price'
                        ]);
                    }
                    $project->setTotalPrice($data['total_price']);
                }

                if (isset($data['composition']['isPaying'])) {
                    if (gettype($data['composition']['isPaying']) != 'string') {
                        return $this->json([
                            'state' => 'IDT',
                            'value' => 'isPaying'
                        ]);
                    }
                    if ($data['composition']['isPaying'] != 'false' && $data['composition']['isPaying'] != 'true') {
                        return $this->json([
                            'state' => 'IDT',
                            'value' => 'isPaying'
                        ]);
                    }
                    $project->setPaying($data['composition']['isPaying']);
                }
                if (isset($data['composition']['database'])) {
                    if (gettype($data['composition']['database']) != 'string') {
                        return $this->json([
                            'state' => 'IDT',
                            'value' => 'database'
                        ]);
                    }
                    if ($data['composition']['database'] != 'false' && $data['composition']['database'] != 'true') {
                        return $this->json([
                            'state' => 'IDT',
                            'value' => 'database'
                        ]);
                    }
                    $project->setDatabase($data['composition']['database']);
                }
                if (isset($data['composition']['maquette'])) {
                    if (gettype($data['composition']['maquette']) != 'string') {
                        return $this->json([
                            'state' => 'IDT',
                            'value' => 'maquette'
                        ]);
                    }
                    if ($data['composition']['maquette'] != 'false' && $data['composition']['maquette'] != 'true') {
                        return $this->json([
                            'state' => 'IDT',
                            'value' => 'maquette'
                        ]);
                    }
                    $project->setMaquette($data['composition']['maquette']);
                }
                if (isset($data['composition']['maintenance'])) {
                    if (gettype($data['composition']['maintenance']) != 'string') {
                        return $this->json([
                            'state' => 'IDT',
                            'value' => 'maintenance'
                        ]);
                    }
                    if ($data['composition']['maintenance'] != 'false' && $data['composition']['maintenance'] != 'true') {
                        return $this->json([
                            'state' => 'IDT',
                            'value' => 'maintenance'
                        ]);
                    }
                    $project->setMaintenance($data['composition']['maintenance']);
                }
                if (isset($data['composition']['type']) && !empty($data['composition']['type'])) {
                    $liste = [];
                    foreach ($data['composition']['type'] as $thing) {
                        if (in_array($thing, $this->listeOfType)) {
                            $liste[] = $thing;
                        }
                    }
                    $project->setType(implode(',', $liste));

                }
                if (isset($data['composition']['framework']) && !empty($data['composition']['framework'])) {
                    $liste = [];
                    foreach ($data['composition']['framework'] as $thing) {
                        if (in_array($thing, $this->listeOfFrameworks)) {
                            $liste[] = $thing;
                        }
                    }
                    $project->setFramework(implode(',', $liste));

                }
                if (isset($data['composition']['options']) && !empty($data['composition']['options'])) {
                    $liste = [];
                    foreach ($data['composition']['options'] as $thing) {
                        if (in_array($thing, $this->listeOfOptions)) {
                            $liste[] = $thing;
                        }
                    }
                    $project->setOptions(implode(',', $liste));

                }
                if (isset($data['composition']['devices']) && !empty($data['composition']['devices'])) {
                    $liste = [];
                    foreach ($data['composition']['devices'] as $thing) {
                        if (in_array($thing, $this->listeOfDevices)) {
                            $liste[] = $thing;
                        }
                    }
                    $project->setDevice(implode(',', $liste));

                }
                if (isset($data['composition']['needTemplate'])) {
                    if (!is_bool($data['composition']['needTemplate'])) {
                        return $this->json([
                            'state' => 'IDT',
                            'value' => 'needTemplate'
                        ]);

                    }
                    $project->setNeedTemplate($data['composition']['needTemplate']);

                }

                $project->setCreatedAt(new \DateTimeImmutable());
                $project->setOwner($this->getUser());
                $project->setState('active');
                $manager->persist($project);
                $manager->flush();
                $this->logService->createLog('ACTION', ' Create Project (' . $project->getId() . ':' . $project->getName() . ') for client (' . $client->getId() . ' | ' . $client->getFirstName() . ' ' . $client->getLastName() . ')', null);

                return $this->json([
                    'state' => 'OK',
                    'value' => $this->getDataProject($project)
                ]);
            } catch (\Exception $exception) {
                return $this->json([
                    'state' => 'ISE',
                    'value' => ' Internal Servor Error : ' . $exception->getMessage() . ' at |' . $exception->getFile() . ' | line |' . $exception->getLine()

                ]);
            }
        }
        return $this->json([
            'state' => 'ND'
        ]);

    }

    #[Route('/api/project/edit/{id}', name: 'edit_api_project')]
    public function edit($id, ProjectRepository $repository, ClientRepository $clientRepository, Request $request, EntityManagerInterface $manager): Response
    {
        $project = $repository->find($id);
        if (!$project) {
            return $this->json([
                'state' => 'NDF',
                'value' => 'project'
            ]);
        }
        if (
            $project->getOwner() != $this->getUser()
        ) {
            return $this->json([
                'state' => 'FO',
                'value' => 'project'
            ]);
        }

        $data = json_decode($request->getContent(), true);

        if ($data) {

            {
                if (isset($data['identity']['name']) && !empty(trim($data['identity']['name']))) {
                    $project->setName($data['identity']['name']);
                }
                if (isset($data['estimatedPrice']) && !empty(trim($data['estimatedPrice']))) {
                    $isValid = $data['estimatedPrice'] > 0 && is_numeric($data['estimatedPrice']);
                    if (!$isValid) {
                        return $this->json([
                            'state' => 'IDT',
                            'value' => 'estimatedPrice'
                        ]);
                    }
                    $project->setEstimatedPrice($data['estimatedPrice']);
                }

                if (isset($data['identity']['client_id']) && !empty(trim($data['identity']['client_id']))) {
                    $client = $clientRepository->find($data['identity']['client_id']);
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
                    $project->setClient($client);
                }
                if (isset($data['total_price']) && !empty(trim($data['total_price']))) {

                    $isValid = $data['total_price'] > 0 && is_numeric($data['total_price']);
                    if (!$isValid) {
                        return $this->json([
                            'state' => 'IDT',
                            'value' => 'total_price'
                        ]);
                    }
                    $project->setTotalPrice($data['total_price']);
                }

                if (isset($data['identity']['figmaLink']) && !empty(trim($data['identity']['figmaLink']))) {
                    $project->setFigmaLink($data['identity']['figmaLink']);
                }
                if (isset($data['identity']['githubLink']) && !empty(trim($data['identity']['githubLink']))) {
                    $project->setGithubLink($data['identity']['githubLink']);
                }

                if (isset($data['identity']['startDate']) && !empty(trim($data['identity']['startDate']))) {
                    $project->setStartDate($data['identity']['startDate']);
                }
                if (isset($data['end_date']) && !empty(trim($data['end_date']))) {
                    $project->setEndDate($data['end_date']);
                }
                if (isset($data['composition']['isPaying'])) {
                    if (gettype($data['composition']['isPaying']) != 'string') {
                        return $this->json([
                            'state' => 'IDT',
                            'value' => 'isPaying'
                        ]);
                    }
                    if ($data['composition']['isPaying'] != 'false' && $data['composition']['isPaying'] != 'true') {
                        return $this->json([
                            'state' => 'IDT',
                            'value' => 'isPaying'
                        ]);
                    }
                    $project->setPaying($data['composition']['isPaying']);
                }
                if (isset($data['composition']['database'])) {
                    if (gettype($data['composition']['database']) != 'string') {
                        return $this->json([
                            'state' => 'IDT',
                            'value' => 'database'
                        ]);
                    }
                    if ($data['composition']['database'] != 'false' && $data['composition']['database'] != 'true') {
                        return $this->json([
                            'state' => 'IDT',
                            'value' => 'database'
                        ]);
                    }
                    $project->setDatabase($data['composition']['database']);
                }
                if (isset($data['maintenancePercentage']) && !empty(trim($data['maintenancePercentage']))) {
                    $isValid = $data['maintenancePercentage'] > 0 && is_numeric($data['maintenancePercentage']);
                    if (!$isValid) {
                        return $this->json([
                            'state' => 'IDT',
                            'value' => 'maintenancePercentage'
                        ]);
                    }
                    $project->setMaintenancePercentage($data['maintenancePercentage']);
                }
                if (isset($data['composition']['maquette'])) {
                    if (gettype($data['composition']['maquette']) != 'string') {
                        return $this->json([
                            'state' => 'IDT',
                            'value' => 'maquette'
                        ]);
                    }
                    if ($data['composition']['maquette'] != 'false' && $data['composition']['maquette'] != 'true') {
                        return $this->json([
                            'state' => 'IDT',
                            'value' => 'maquette'
                        ]);
                    }
                    $project->setMaquette($data['composition']['maquette']);
                }
                if (isset($data['composition']['maintenance'])) {
                    if (gettype($data['composition']['maintenance']) != 'string') {
                        return $this->json([
                            'state' => 'IDT',
                            'value' => 'maintenance'
                        ]);
                    }
                    if ($data['composition']['maintenance'] != 'false' && $data['composition']['maintenance'] != 'true') {
                        return $this->json([
                            'state' => 'IDT',
                            'value' => 'maintenance'
                        ]);
                    }
                    $project->setMaintenance($data['composition']['maintenance']);
                }
                if (isset($data['composition']['type']) && !empty($data['composition']['type'])) {
                    $liste = [];
                    foreach ($data['composition']['type'] as $thing) {
                        if (in_array($thing, $this->listeOfType)) {
                            $liste[] = $thing;
                        }
                    }
                    $project->setType(implode(',', $liste));

                }
                if (isset($data['composition']['framework']) && !empty($data['composition']['framework'])) {
                    $liste = [];
                    foreach ($data['composition']['framework'] as $thing) {
                        if (in_array($thing, $this->listeOfFrameworks)) {
                            $liste[] = $thing;
                        }
                    }
                    $project->setFramework(implode(',', $liste));

                }
                if (isset($data['composition']['options']) && !empty($data['composition']['options'])) {
                    $liste = [];
                    foreach ($data['composition']['options'] as $thing) {
                        if (in_array($thing, $this->listeOfOptions)) {
                            $liste[] = $thing;
                        }
                    }
                    $project->setOptions(implode(',', $liste));

                }
                if (isset($data['composition']['devices']) && !empty($data['composition']['devices'])) {

                    $liste = [];
                    foreach ($data['composition']['devices'] as $thing) {
                        if (in_array($thing, $this->listeOfDevices)) {
                            $liste[] = $thing;
                        }
                    }
                    $project->setDevice(implode(',', $liste));

                }

                if (isset($data['composition']['needTemplate'])) {
                    if (!is_bool($data['composition']['needTemplate'])) {
                        return $this->json([
                            'state' => 'IDT',
                            'value' => 'needTemplate'
                        ]);
                    }
                    $project->setNeedTemplate($data['composition']['needTemplate']);

                }

                $project->setCreatedAt(new \DateTimeImmutable());
                $project->setOwner($this->getUser());
                $project->setState('active');
                $manager->persist($project);
                $manager->flush();
                $this->logService->createLog('ACTION', ' Edit Project (' . $project->getId() . ':' . $project->getName() . ') for client (' . $project->getClient()->getId() . ' | ' . $project->getClient()->getFirstName() . ' ' . $project->getClient()->getLastName() . ')', null);

                return $this->json([
                    'state' => 'OK',
                    'value' => $this->getDataProject($project)
                ]);

            }

        }
        return $this->json([
            'state' => 'ND'
        ]);

    }

    public function getDataProject($project)
    {
        try {
            $client = null;
            if ($project->getClient()) {
                $client = [
                    'id' => $project->getClient()->getId(),
                    'firstName' => $project->getClient()->getFirstName(),
                    'lastName' => $project->getClient()->getLastName()];

            }
            return [
                "totalPrice" => $project->getTotalPrice(),
                "estimatedPrice" => $project->getEstimatedPrice(),
                "maintenancePercentage" => $project->getMaintenancePercentage(),

                'identity' => [
                    "id" => $project->getId(),
                    "name" => $project->getName(),
                    "figmaLink" => $project->getFigmaLink(),
                    "githubLink" => $project->getGithubLink(),
                    "state" => $project->getState(),
                    "startDate" => $project->getStartDate(),
                    "endDate" => $project->getEndDate(),
                    "client" => $client,
                    'owner' => $project->getOwner()->getEmail(),
                ],

                'composition' => [!
                'isPaying' => $project->isPaying(),
                    'database' => $project->isDatabase(),
                    'maquette' => $project->isMaquette(),
                    'maintenance' => $project->isMaintenance(),
                    'type' => !empty($project->getType()) ? explode(',', $project->getType()) : [],
                    'framework' => !empty($project->getFramework()) ? explode(',', $project->getFramework()) : [],
                    'options' => !empty($project->getOptions()) ? explode(',', $project->getOptions()) : [],
                    'devices' => !empty($project->getDevice()) ? explode(',', $project->getDevice()) : [],
                    'needTemplate' => $project->isNeedTemplate(),
                ]
            ];
        } catch (\Exception $exception) {
            $this->logService->createLog('ERROR', ' Internal Servor Error at |' . $exception->getFile() . ' | line |' . $exception->getLine(), $exception->getMessage());


            return $this->json([
                'state' => 'ISE',
                'value' => ' Internal Servor Error : ' . $exception->getMessage() . ' at |' . $exception->getFile() . ' | line |' . $exception->getLine()

            ]);
        }
    }

    #[Route('/api/project/delete/{id}', name: 'delete_project', methods: 'delete')]
    public function delete($id, ProjectRepository $repository, EntityManagerInterface $manager): Response
    {
        try {
            $project = $repository->find($id);
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
            $project->setState('deleted');
            $manager->persist($project);
            $manager->flush();
            $this->logService->createLog('DELETE', ' Delete Project (' . $project->getId() . ':' . $project->getName() . ') for client (' . $project->getClient()->getId() . ' | ' . $project->getClient()->getFirstName() . ' ' . $project->getClient()->getLastName() . ')', null);

            return $this->json([
                'state' => 'OK'
            ]);

        } catch (\Exception $exception) {
            $this->logService->createLog('ERROR', ' Internal Servor Error at |' . $exception->getFile() . ' | line |' . $exception->getLine(), $exception->getMessage());


            return $this->json([
                'state' => 'ISE',
                'value' => ' Internal Servor Error : ' . $exception->getMessage() . ' at |' . $exception->getFile() . ' | line |' . $exception->getLine()

            ]);
        }
    }

    #[Route('/api/project/deleteforce/{id}', name: 'delete_force_project', methods: 'delete')]
    public function deleteforce($id, ProjectRepository $repository, EntityManagerInterface $manager): Response
    {
        try {
            $project = $repository->find($id);
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
            $message = ' Delete force Project (' . $project->getId() . ':' . $project->getName() . ') for client (' . $project->getClient()->getId() . ' | ' . $project->getClient()->getFirstName() . ' ' . $project->getClient()->getLastName() . ')';
            $manager->remove($project);
            $manager->flush();
            $this->logService->createLog('DELETE', $message, null);

            return $this->json([
                'state' => 'OK'
            ]);
        } catch (\Exception $exception) {
            $this->logService->createLog('ERROR', ' Internal Servor Error at |' . $exception->getFile() . ' | line |' . $exception->getLine(), $exception->getMessage());


            return $this->json([
                'state' => 'ISE',
                'value' => ' Internal Servor Error : ' . $exception->getMessage() . ' at |' . $exception->getFile() . ' | line |' . $exception->getLine()

            ]);
        }
    }

    #[Route('/api/project/{id}', name: 'get_project', methods: 'get')]
    public function getProject($id, ProjectRepository $repository): Response
    {
        try {
            $project = $repository->find($id);
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
            return $this->json([
                'state' => 'OK',
                'value' => $this->json($this->getDataProject($project))
            ]);
        } catch (\Exception $exception) {
            $this->logService->createLog('ERROR', ' Internal Servor Error at |' . $exception->getFile() . ' | line |' . $exception->getLine(), $exception->getMessage());


            return $this->json([
                'state' => 'ISE',
                'value' => ' Internal Servor Error : ' . $exception->getMessage() . ' at |' . $exception->getFile() . ' | line |' . $exception->getLine()

            ]);
        }
    }

    #[Route('/api/projects', name: 'get_projects', methods: 'get')]
    public function getProjects(ProjectRepository $repository, Request $request): Response
    {
        try {
            $datum = json_decode($request->getContent(), true);
            $arrayToIterate = $repository->findBy([], ['createdAt' => 'ASC']);

            $display_delete = false;
            $data = [];
            if ($datum) {
                if (isset($datum['displayDeleted']) && !empty(trim($datum['displayDeleted']))) {
                    if ($datum['displayDeleted'] == true) {
                        $display_delete = true;
                    }
                }
            }
            foreach ($arrayToIterate as $project) {
                if ($project->getOwner() == $this->getUser()) {
                    if ($display_delete && $project->getState() == 'deleted' || $project->getState() != 'deleted') {
                        $data[] = $this->getDataProject($project);
                    }
                }


            }
            return $this->json([
                'state' => 'OK',
                'value' => $data
            ]);
        } catch (\Exception $exception) {
            $this->logService->createLog('ERROR', ' Internal Servor Error at |' . $exception->getFile() . ' | line |' . $exception->getLine(), $exception->getMessage());


            return $this->json([
                'state' => 'ISE',
                'value' => ' Internal Servor Error : ' . $exception->getMessage() . ' at |' . $exception->getFile() . ' | line |' . $exception->getLine()

            ]);
        }
    }


}
