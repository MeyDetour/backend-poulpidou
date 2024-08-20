<?php

namespace App\Controller;

use App\Entity\Chat;
use App\Entity\Project;
use App\Repository\ClientRepository;
use App\Repository\ProjectRepository;
use App\Repository\UserRepository;
use App\Service\DateService;
use App\Service\LogService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ApiProjectController extends AbstractController
{
    private LogService $logService;
    private DateService $dateService;
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


    public function __construct(LogService $logService, DateService $dateService)
    {
        $this->logService = $logService;
        $this->dateService = $dateService;
    }

    #[Route('/api/project/new', name: 'app_api_project', methods: 'post')]
    public function index(Request $request, EntityManagerInterface $manager, ClientRepository $clientRepository): Response
    {
        try {
            $data = json_decode($request->getContent(), true);

            if ($data) {

                $project = new Project();
                if (!isset($data['identity']['client_id'])) {
                    return $this->json([
                        'state' => 'NED',
                        'value' => 'client_id'
                    ]);
                }
                if (!is_numeric($data['identity']['client_id'])) {
                    return $this->json([
                        'state' => 'IDT',
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
                if ($client->getState() == 'deleted') {
                    return $this->json([
                        'state' => 'DD',
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
                if (isset($data['identity']['websiteLink']) && !empty(trim($data['identity']['websiteLink']))) {
                    $project->setFigmaLink($data['identity']['websiteLink']);
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

                    $searchDate = \DateTime::createFromFormat('d/m/Y', $data['identity']['startDate']);
                    if (!$searchDate) {
                        return $this->json([
                            'state' => 'IDT',
                            'value' => 'startDate'
                        ]);
                    }
                    $project->setStartDate($searchDate);
                }
                if (isset($data['identity']['endDate']) && !empty(trim($data['identity']['endDate']))) {
                    $searchDate = \DateTime::createFromFormat('d/m/Y', $data['identity']['endDate']);
                    if (!$searchDate) {
                        return $this->json([
                            'state' => 'IDT',
                            'value' => 'endDate'
                        ]);
                    }
                    $project->setStartDate($searchDate);
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
                if (isset($data['rules']['canEditInvoices'])) {
                    if (gettype($data['rules']['canEditInvoices']) != 'string') {
                        return $this->json([
                            'state' => 'IDT',
                            'value' => 'canEditInvoices'
                        ]);
                    }
                    if ($data['rules']['canEditInvoices'] != 'false' && $data['rules']['canEditInvoices'] != 'true') {
                        return $this->json([
                            'state' => 'IDT',
                            'value' => 'canEditInvoices'
                        ]);
                    }
                    $project->setOtherUserCanEditInvoices($data['rules']['canEditInvoices']);
                }
                if (isset($data['rules']['canSeeClientProfile'])) {
                    if (gettype($data['rules']['canSeeClientProfile']) != 'string') {
                        return $this->json([
                            'state' => 'IDT',
                            'value' => 'canSeeClientProfile'
                        ]);
                    }
                    if ($data['rules']['canSeeClientProfile'] != 'false' && $data['rules']['canSeeClientProfile'] != 'true') {
                        return $this->json([
                            'state' => 'IDT',
                            'value' => 'canSeeClientProfile'
                        ]);
                    }
                    $project->setCanOtherUserSeeClientProfile($data['rules']['canEditInvoices']);
                }
                $project->setUuid(uniqid());
                $project->setCreatedAt(new \DateTimeImmutable());
                $project->setOwner($this->getUser());
                $project->setState('active');
                $project->setNoteNames('Note 1,Note 2,Note 3,Note 4,Note 5');
                $project->setNoteContent(' , , , , ');
                $project->setCurrent(true);
                $manager->persist($project);
                $manager->flush();

                $chat = new Chat();
                $chat->setProject($project);
                $chat->setClient($project->getClient());
                $chat->setCreatedAt(new \DateTimeImmutable());
                $chat->setName($project->getName() . ' Chat');
                $chat->addUser($this->getUser());
                $manager->persist($chat);
                $manager->flush();
                $this->logService->createLog('ACTION', ' Create Project (' . $project->getId() . ':' . $project->getName() . ') for client (' . $client->getId() . ' | ' . $client->getFirstName() . ' ' . $client->getLastName() . ')');

                return $this->json([
                    'state' => 'OK',
                    'value' => $this->getDataProject($project)
                ]);

            }
            return $this->json([
                'state' => 'ND'
            ]);
        } catch (\Exception $exception) {
            return $this->json([
                'state' => 'ISE',
                'value' => ' Internal Servor Error : ' . $exception->getMessage() . ' at |' . $exception->getFile() . ' | line |' . $exception->getLine()

            ]);
        }
    }

    #[Route('/api/project/edit/{id}', name: 'edit_api_project')]
    public function edit($id, ProjectRepository $repository, ClientRepository $clientRepository, Request $request, EntityManagerInterface $manager): Response
    {
        try {
            $project = $repository->find($id);
            if (!$project) {
                return $this->json([
                    'state' => 'NDF',
                    'value' => 'project'
                ]);
            }
            if ($project->getOwner() != $this->getUser() && !$project->hasUserInUserAuthorised($this->getUser())) {
                return $this->json([
                    'state' => 'FO',
                    'value' => 'project'
                ]);
            }
            if ($project->getState() == 'deleted') {
                return $this->json([
                    'state' => 'DD',
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
                    if (isset($data['identity']['websiteLink']) && !empty(trim($data['identity']['websiteLink']))) {
                        $project->setFigmaLink($data['identity']['websiteLink']);
                    }
                    if (isset($data['identity']['githubLink']) && !empty(trim($data['identity']['githubLink']))) {
                        $project->setGithubLink($data['identity']['githubLink']);
                    }

                    if (isset($data['identity']['startDate']) && !empty(trim($data['identity']['startDate']))) {

                        $searchDate = \DateTime::createFromFormat('d/m/Y', $data['identity']['startDate']);
                        if (!$searchDate) {
                            return $this->json([
                                'state' => 'IDT',
                                'value' => 'startDate'
                            ]);
                        }
                        $project->setStartDate($searchDate);
                    }
                    if (isset($data['identity']['endDate']) && !empty(trim($data['identity']['endDate']))) {
                        $searchDate = \DateTime::createFromFormat('d/m/Y', $data['identity']['endDate']);
                        if (!$searchDate) {
                            return $this->json([
                                'state' => 'IDT',
                                'value' => 'endDate'
                            ]);
                        }
                        $project->setStartDate($searchDate);
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
                    if (isset($data['rules']['canEditInvoices'])) {
                        if (gettype($data['rules']['canEditInvoices']) != 'string') {
                            return $this->json([
                                'state' => 'IDT',
                                'value' => 'canEditInvoices'
                            ]);
                        }
                        if ($data['rules']['canEditInvoices'] != 'false' && $data['rules']['canEditInvoices'] != 'true') {
                            return $this->json([
                                'state' => 'IDT',
                                'value' => 'canEditInvoices'
                            ]);
                        }
                        $project->setOtherUserCanEditInvoices($data['rules']['canEditInvoices']);
                    }
                    if (isset($data['rules']['canSeeClientProfile'])) {
                        if (gettype($data['rules']['canSeeClientProfile']) != 'string') {
                            return $this->json([
                                'state' => 'IDT',
                                'value' => 'canSeeClientProfile'
                            ]);
                        }
                        if ($data['rules']['canSeeClientProfile'] != 'false' && $data['rules']['canSeeClientProfile'] != 'true') {
                            return $this->json([
                                'state' => 'IDT',
                                'value' => 'canSeeClientProfile'
                            ]);
                        }
                        $project->setCanOtherUserSeeClientProfile($data['rules']['canEditInvoices']);
                    }

                    $project->setCreatedAt(new \DateTimeImmutable());
                    $project->setOwner($this->getUser());
                    $project->setState('active');
                    $manager->persist($project);
                    $manager->flush();
                    $chat = $project->getChat();
                    $chat->setName($project->getName() . ' Chat');
                    $manager->persist($chat);
                    $manager->flush();
                    $this->logService->createLog('ACTION', ' Edit Project (' . $project->getId() . ':' . $project->getName() . ') for client (' . $project->getClient()->getId() . ' | ' . $project->getClient()->getFirstName() . ' ' . $project->getClient()->getLastName() . ')');

                    return $this->json([
                        'state' => 'OK',
                        'value' => $this->getDataProject($project)
                    ]);

                }

            }
            return $this->json([
                'state' => 'ND'
            ]);
        } catch (\Exception $exception) {
            return $this->json([
                'state' => 'ISE',
                'value' => ' Internal Servor Error : ' . $exception->getMessage() . ' at |' . $exception->getFile() . ' | line |' . $exception->getLine()

            ]);
        }
    }

    #[Route('/api/project/{id}/switch/current', name: 'set_project_no_current', methods: 'put')]
    public function switchProjectToUnccurent($id, ProjectRepository $projectRepository, Request $request, EntityManagerInterface $manager): Response
    {
        try {
            $project = $projectRepository->find($id);
            if (!$project) {
                return $this->json([
                    'state' => 'NDF',
                    'value' => 'project'
                ]);
            }
            if ($project->getOwner() != $this->getUser() && !$project->hasUserInUserAuthorised($this->getUser())) {
                return $this->json([
                    'state' => 'FO',
                    'value' => 'project'
                ]);
            }
            if ($project->getState() == 'deleted') {
                return $this->json([
                    'state' => 'DD',
                    'value' => 'project'
                ]);
            }

            $data = json_decode($request->getContent(), true);

            if (!isset($data['isCurrent'])) {
                return $this->json([
                    'state' => 'NED',
                    'value' => 'isCurrent'
                ]);

            }
            if (!is_bool($data['isCurrent'])) {
                return $this->json([
                    'state' => 'IDT',
                    'value' => 'isCurrent'
                ]);

            }

            $project->setCurrent($data['isCurrent']);
            $manager->persist($project);
            $manager->flush();
            return $this->json([
                'state' => 'OK',
            ]);
        } catch (\Exception $exception) {
            $this->logService->createLog('ERROR', ' Internal Servor Error at |' . $exception->getFile() . ' | line |' . $exception->getLine());


            return $this->json([
                'state' => 'ISE',
                'value' => ' Internal Servor Error : ' . $exception->getMessage() . ' at |' . $exception->getFile() . ' | line |' . $exception->getLine()

            ]);
        }

    }

    #[Route('/api/projects/{id}/switch/current', name: 'set_project_no_current', methods: 'put')]
    public function getCurrentsProjects($id, ProjectRepository $projectRepository, Request $request, EntityManagerInterface $manager): Response
    {
        try {
            $project = $projectRepository->find($id);
            if (!$project) {
                return $this->json([
                    'state' => 'NDF',
                    'value' => 'project'
                ]);
            }
            if ($project->getOwner() != $this->getUser() && !$project->hasUserInUserAuthorised($this->getUser())) {
                return $this->json([
                    'state' => 'FO',
                    'value' => 'project'
                ]);
            }
            if ($project->getState() == 'deleted') {
                return $this->json([
                    'state' => 'DD',
                    'value' => 'project'
                ]);
            }

            $data = json_decode($request->getContent(), true);

            if (!isset($data['isCurrent'])) {
                return $this->json([
                    'state' => 'NED',
                    'value' => 'isCurrent'
                ]);

            }
            if (!is_bool($data['isCurrent'])) {
                return $this->json([
                    'state' => 'IDT',
                    'value' => 'isCurrent'
                ]);

            }

            $project->setCurrent($data['isCurrent']);
            $manager->persist($project);
            $manager->flush();
            return $this->json([
                'state' => 'OK',
            ]);
        } catch (\Exception $exception) {
            $this->logService->createLog('ERROR', ' Internal Servor Error at |' . $exception->getFile() . ' | line |' . $exception->getLine());


            return $this->json([
                'state' => 'ISE',
                'value' => ' Internal Servor Error : ' . $exception->getMessage() . ' at |' . $exception->getFile() . ' | line |' . $exception->getLine()

            ]);
        }

    }

    #[Route('/api/project/{id}/note', name: 'edit_note_project', methods: 'put')]
    public function editNote(Request $request, $id, EntityManagerInterface $manager, ProjectRepository $repository): Response
    {
        try {
            $project = $repository->find($id);
            if (!$project) {
                return $this->json([
                    'state' => 'NDF',
                    'value' => 'project'
                ]);
            }
            if ($project->getOwner() != $this->getUser() && !$project->hasUserInUserAuthorised($this->getUser())) {

                return $this->json([
                    'state' => 'FO',
                    'value' => 'project'
                ]);
            }
            if ($project->getState() == 'deleted') {
                return $this->json([
                    'state' => 'DD',
                    'value' => 'project'
                ]);
            }

            $data = json_decode($request->getContent(), true);

            if ($data) {


                if (!isset($data['names'])) {

                    return $this->json([
                        'state' => 'NED',
                        'value' => 'names'
                    ]);
                }
                if (gettype($data['names']) != 'array') {
                    return $this->json([
                        'state' => 'IDT',
                        'value' => 'names'
                    ]);
                }
                if (count($data['names']) !== 5) {
                    return $this->json([
                        'state' => 'NED',
                        'value' => 'names'
                    ]);
                }
                if (!isset($data['contents'])) {

                    return $this->json([
                        'state' => 'NED',
                        'value' => 'contents'
                    ]);
                }
                if (gettype($data['contents']) != 'array') {
                    return $this->json([
                        'state' => 'IDT',
                        'value' => 'contents'
                    ]);
                }
                if (count($data['contents']) !== 5) {
                    return $this->json([
                        'state' => 'NED',
                        'value' => 'contents'
                    ]);
                }


                $project->setNoteNames(implode(',', $data['names']));

                $project->setNoteContent(implode(',', $data['contents']));
                $manager->persist($project);
                $manager->flush();
                $this->logService->createLog('ACTION', ' Edit Project (' . $project->getId() . ':' . $project->getName() . ') for client (' . $project->getClient()->getId() . ' | ' . $project->getClient()->getFirstName() . ' ' . $project->getClient()->getLastName() . ')');

                return $this->json([
                    'state' => 'OK',
                    'value' => $this->getDataProject($project)
                ]);

            }

            return $this->json([
                'state' => 'ND'
            ]);
        } catch (\Exception $exception) {
            return $this->json(['state' => 'ISE',
                'value' => ' Internal Servor Error : ' . $exception->getMessage() . ' at |' . $exception->getFile() . ' | line |' . $exception->getLine()]);
        }

    }

    #[Route('/api/project/{id}/remove/user/{userId}', name: 'remove_user_to_project', methods: 'delete')]
    #[Route('/api/project/{id}/add/user/{userId}', name: 'add_user_to_project', methods: 'put')]
    public function addUserToProject($id, ProjectRepository $repository, $userId, UserRepository $userRepository, EntityManagerInterface $manager, Request $request): Response
    {


        try {
            $project = $repository->find($id);
            if (!$project) {
                return $this->json([
                    'state' => 'NDF',
                    'value' => 'project'
                ]);
            }
            if ($project->getOwner() != $this->getUser()) {
                return $this->json([
                    'state' => 'FO',
                    'value' => 'project'
                ]);
            }
            if ($project->getState() == 'deleted') {
                return $this->json([
                    'state' => 'DD',
                    'value' => 'project'
                ]);
            }
            $user = $userRepository->find($userId);
            if (!$user) {
                return $this->json([
                    'state' => 'NDF',
                    'value' => 'user'
                ]);
            }
            $route = $request->attributes->get('_route');
            if ($route == 'add_user_to_project') {

                $project->addUserAuthorised($user);

                $project->getChat()->addUser($user);
            }
            if ($route == 'remove_user_to_project') {

                $project->removeUserAuthorised($user);

                $project->getChat()->removeUser($user);

            }
            $manager->persist($project);
            $manager->persist($project->getChat());
            $manager->flush();
            if ($route == 'add_user_to_project') {
                $this->logService->createLog('ACTION', ' Add User (' . $user->getId() . ' | ' . $user->getEmail() . ') to Project (' . $project->getId() . ':' . $project->getName() . ') for client (' . $project->getClient()->getId() . ' | ' . $project->getClient()->getFirstName() . ' ' . $project->getClient()->getLastName() . ')');
            }
            if ($route == 'remove_user_to_project') {
                $this->logService->createLog('ACTION', ' remove User (' . $user->getId() . ' | ' . $user->getEmail() . ') to Project (' . $project->getId() . ':' . $project->getName() . ') for client (' . $project->getClient()->getId() . ' | ' . $project->getClient()->getFirstName() . ' ' . $project->getClient()->getLastName() . ')');

            }
            return $this->json([
                'state' => 'OK'
            ]);

        } catch (\Exception $exception) {
            $this->logService->createLog('ERROR', ' Internal Servor Error at |' . $exception->getFile() . ' | line |' . $exception->getLine());


            return $this->json([
                'state' => 'ISE',
                'value' => ' Internal Servor Error : ' . $exception->getMessage() . ' at |' . $exception->getFile() . ' | line |' . $exception->getLine()

            ]);
        }
    }

    #[Route('/api/project/{id}/left', name: 'left_user_to_project', methods: 'delete')]
    public function leftProject($id, ProjectRepository $repository, EntityManagerInterface $manager): Response
    {
        try {
            $project = $repository->find($id);
            if (!$project) {
                return $this->json([
                    'state' => 'NDF',
                    'value' => 'project'
                ]);
            }
            if ($project->getOwner() != $this->getUser()) {
                return $this->json([
                    'state' => 'FO',
                    'value' => 'project'
                ]);
            }
            if ($project->getState() == 'deleted') {
                return $this->json([
                    'state' => 'DD',
                    'value' => 'project'
                ]);
            }
            if ($project->hasUserInUserAuthorised($this->getUser())) {
                $project->removeUserAuthorised($this->getUser());
                $project->getChat()->removeUser($this->getUser());
            }


            $manager->persist($project);
            $manager->persist($project->getChat());
            $manager->flush();

            return $this->json([
                'state' => 'OK'
            ]);

        } catch (\Exception $exception) {
            $this->logService->createLog('ERROR', ' Internal Servor Error at |' . $exception->getFile() . ' | line |' . $exception->getLine());


            return $this->json([
                'state' => 'ISE',
                'value' => ' Internal Servor Error : ' . $exception->getMessage() . ' at |' . $exception->getFile() . ' | line |' . $exception->getLine()

            ]);
        }
    }


    #[
        Route('/api/project/delete/{id}', name: 'delete_project', methods: 'delete')]
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
            if ($project->getOwner() != $this->getUser() && !$project->hasUserInUserAuthorised($this->getUser())) {
                return $this->json([
                    'state' => 'FO',
                    'value' => 'project'
                ]);
            }
            if ($project->getState() == 'deleted') {
                return $this->json([
                    'state' => 'DD',
                    'value' => 'project'
                ]);
            }
            $project->setState('deleted');
            $manager->persist($project);
            $manager->flush();
            $this->logService->createLog('DELETE', ' Delete Project (' . $project->getId() . ':' . $project->getName() . ') for client (' . $project->getClient()->getId() . ' | ' . $project->getClient()->getFirstName() . ' ' . $project->getClient()->getLastName() . ')');

            return $this->json([
                'state' => 'OK'
            ]);

        } catch (\Exception $exception) {
            $this->logService->createLog('ERROR', ' Internal Servor Error at |' . $exception->getFile() . ' | line |' . $exception->getLine());


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
            if ($project->getOwner() != $this->getUser() && !$project->hasUserInUserAuthorised($this->getUser())) {
                return $this->json([
                    'state' => 'FO',
                    'value' => 'project'
                ]);
            }
            if ($project->getState() == 'deleted') {
                return $this->json([
                    'state' => 'DD',
                    'value' => 'project'
                ]);
            }
            $message = ' Delete force Project (' . $project->getId() . ':' . $project->getName() . ') for client (' . $project->getClient()->getId() . ' | ' . $project->getClient()->getFirstName() . ' ' . $project->getClient()->getLastName() . ')';


            foreach ($project->getCategories() as $category) {

                $manager->remove($category);
            }
            foreach ($project->getTasks() as $task) {
                $manager->remove($task);
            }
            foreach ($project->getInvoices() as $invoice) {
                $manager->remove($invoice);
            }
            foreach ($project->getPdfs() as $pdf) {
                $filePath = $this->getParameter('upload_directory') . '/' . $pdf->getFileName();


                if (!file_exists($filePath)) {
                    return $this->json([
                        'state' => 'NDF',
                        'value' => 'pdf'
                    ]);
                }

                $filePath = 'pdf/' . $pdf->getFileName();
                if (unlink($filePath)) {
                    $manager->remove($pdf);

                } else {
                    return $this->json([
                        'state' => 'ISE',
                        'value' => 'Failed to remove pdf'
                    ]);
                }

            }
            $chat = $project->getChat();
            foreach ($chat->getMessages() as $message) {
                $manager->remove($message);
            }
            $manager->remove($chat);
            $manager->remove($project);
            $manager->flush();
            $this->logService->createLog('DELETE', $message);

            return $this->json([
                'state' => 'OK'
            ]);
        } catch (\Exception $exception) {
            $this->logService->createLog('ERROR', ' Internal Servor Error at |' . $exception->getFile() . ' | line |' . $exception->getLine());


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
            if ($project->getOwner() != $this->getUser() && !$project->hasUserInUserAuthorised($this->getUser())) {
                return $this->json([
                    'state' => 'FO',
                    'value' => 'project'
                ]);
            }
            if ($project->getState() == 'deleted') {
                return $this->json([
                    'state' => 'DD',
                    'value' => 'project'
                ]);
            }
            return $this->json([
                'state' => 'OK',
                'value' => $this->getDataProject($project)
            ]);
        } catch (\Exception $exception) {
            $this->logService->createLog('ERROR', ' Internal Servor Error at |' . $exception->getFile() . ' | line |' . $exception->getLine());


            return $this->json([
                'state' => 'ISE',
                'value' => ' Internal Servor Error : ' . $exception->getMessage() . ' at |' . $exception->getFile() . ' | line |' . $exception->getLine()

            ]);
        }
    }

    #[Route('/api/projects', name: 'get_projects', methods: 'get')]
    public function getProjects(ProjectRepository $repository, Request $request, EntityManagerInterface $manager): Response
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
                if ($display_delete && $project->getState() == 'deleted' || $project->getState() != 'deleted') {
                    $data[] = $this->getDataProject($project);
                }


            }

            return $this->json([
                'state' => 'OK',
                'value' => $data
            ]);
        } catch (\Exception $exception) {
            $this->logService->createLog('ERROR', ' Internal Servor Error at |' . $exception->getFile() . ' | line |' . $exception->getLine());


            return $this->json([
                'state' => 'ISE',
                'value' => ' Internal Servor Error : ' . $exception->getMessage() . ' at |' . $exception->getFile() . ' | line |' . $exception->getLine()

            ]);
        }
    }

    #[Route('/api/your/projects', name: 'get_projects_current_and_other', methods: 'get')]
    public function getProjectsToRender(ProjectRepository $repository, Request $request, EntityManagerInterface $manager): Response
    {
        try {
            $projects = $this->getUser()->getProjects()->toArray();
            $authorizedProjects = $this->getUser()->getAutorisedInProjects()->toArray();
            $projects = array_merge($projects, $authorizedProjects);  // Fusionner les projets
            $data = [
                'currents' => [],
                'others' => [],
            ];
            foreach ($projects as $project) {


                if ($project->getState() != 'deleted') {
                    if ($project->isCurrent() == true) {
                        $data['currents'][] = $this->getDataProjectForMiniature($project);
                    } else {
                        $data['others'][] = $this->getDataProjectForMiniature($project);
                    }
                }


            }

            return $this->json([
                'state' => 'OK',
                'value' => $data
            ]);
        } catch (\Exception $exception) {
            $this->logService->createLog('ERROR', ' Internal Servor Error at |' . $exception->getFile() . ' | line |' . $exception->getLine());


            return $this->json([
                'state' => 'ISE',
                'value' => ' Internal Servor Error : ' . $exception->getMessage() . ' at |' . $exception->getFile() . ' | line |' . $exception->getLine()

            ]);
        }
    }

    #[Route('/api/project/{id}/get/client', name: 'get_project_client', methods: 'get')]
    public function getClientOfProject($id, ProjectRepository $repository): Response
    {
        try {
            $project = $repository->find($id);
            if (!$project) {
                return $this->json([
                    'state' => 'NDF',
                    'value' => 'project'
                ]);
            }
            if ($project->getOwner() != $this->getUser() && !$project->hasUserInUserAuthorised($this->getUser())) {
                return $this->json([
                    'state' => 'FO',
                    'value' => 'project'
                ]);
            }
            if (!$project->isCanOtherUserSeeClientProfile() && $project->getOwner() != $this->getUser()) {
                return $this->json([
                    'state' => 'ASFO',
                    'value' => 'project'
                ]);
            }

            if ($project->getState() == 'deleted') {
                return $this->json([
                    'state' => 'DD',
                    'value' => 'project'
                ]);
            }
            return $this->json([
                'state' => 'OK',
                'value' => [
                    'id' => $project->getClient()->getId(),
                    'firstName' => $project->getClient()->getFirstName(),
                    'lastName' => $project->getClient()->getLastName(),
                    'date' => $this->dateService->formateDate($project->getClient()->getCreatedAt()),
                ]
            ]);
        } catch (\Exception $exception) {
            $this->logService->createLog('ERROR', ' Internal Servor Error at |' . $exception->getFile() . ' | line |' . $exception->getLine());


            return $this->json([
                'state' => 'ISE',
                'value' => ' Internal Servor Error : ' . $exception->getMessage() . ' at |' . $exception->getFile() . ' | line |' . $exception->getLine()

            ]);
        }
    }

    #[Route('/api/search/project', name: 'search_project', methods: 'get')]
    public function searchProject(Request $request, ProjectRepository $projectRepository): Response
    {
        try {
            $datum = json_decode($request->getContent(), true);

            if ($datum) {
                if (isset($datum['searchTerm']) && !empty(trim($datum['searchTerm']))) {
                    $projects = $projectRepository->searchAcrossTables($datum['searchTerm']);
                    $dataToReturn = [];
                    foreach ($projects as $project) {
                        $dataToReturn[] = $this->getDataProjectForSearch($project);
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
            $this->logService->createLog('ERROR', ' Internal Servor Error at |' . $exception->getFile() . ' | line |' . $exception->getLine());


            return $this->json([
                'state' => 'ISE',
                'value' => ' Internal Servor Error : ' . $exception->getMessage() . ' at |' . $exception->getFile() . ' | line |' . $exception->getLine()

            ]);
        }
    }

    public function getDataProject($project)
    {
        try {

            $client = null;
            $chat = null;
            if ($project->getClient()) {
                $client = [
                    'id' => $project->getClient()->getId(),
                    'firstName' => $project->getClient()->getFirstName(),
                    'lastName' => $project->getClient()->getLastName(),
                    'online' => $project->getClient()->isOnline()
                ];

            }

            if ($project->getChat()) {
                $chat = $project->getChat()->getName();
            }

            $notesNames = explode(',', $project->getNoteNames());
            $notesContent = explode(',', $project->getNoteContent());


            $userAutorised = [];
            foreach ($project->getUserAuthorised() as $user) {
                if ($user != $project->getOwner()) {
                    $userAutorised[] = [
                        'email' => $user->getEmail(),
                        'firstName' => $user->getFirstName(),
                        'lastName' => $user->getLastName(),
                    ];
                }

            }

            return [
                "totalPrice" => $project->getTotalPrice(),
                "estimatedPrice" => $project->getEstimatedPrice(),
                "maintenancePercentage" => $project->getMaintenancePercentage(),
                "members" => $userAutorised,

                'identity' => [
                    "id" => $project->getId(),
                    'uuid' => $project->getUuid(),
                    "name" => $project->getName(),
                    "figmaLink" => $project->getFigmaLink(),
                    "githubLink" => $project->getGithubLink(),
                    "websiteLink" => $project->getWebsiteLink(),
                    "startDate" => $this->dateService->formateDate($project->getStartDate()),
                    "endDate" => $this->dateService->formateDate($project->getEndDate()),
                    "client" => $client,
                    "chatName" => $chat,
                    "state" => $project->getState(),
                    "isCurrent" => $project->isCurrent(),
                    "cratedAt" => $this->dateService->formateDate($project->getCreatedAt()),
                    "owner" => [
                        'email' => $project->getOWner()->getEmail(),
                        'firstName' => $project->getOWner()->getFirstName(),
                        'lastName' => $project->getOWner()->getLastName(),
                    ]
                ],

                "note" => [
                    [$notesNames[0], $notesContent[0]],
                    [$notesNames[1], $notesContent[1]],
                    [$notesNames[2], $notesContent[2]],
                    [$notesNames[3], $notesContent[3]],
                    [$notesNames[4], $notesContent[4]],
                ],
                "rules" => [
                    'canEditInvoices' => $project->isOtherUserCanEditInvoices(),
                    'canSeeClientProfile' => $project->isCanOtherUserSeeClientProfile(),
                ],
                'composition' => [
                    'isPaying' => $project->isPaying(),
                    'database' => $project->isDatabase(),
                    'maquette' => $project->isMaquette(),
                    'maintenance' => $project->isMaintenance(),
                    'type' => !empty($project->getType()) ? explode(',', $project->getType()) : [],
                    'framework' => !empty($project->getFramework()) ? explode(',', $project->getFramework()) : [],
                    'options' => !empty($project->getOptions()) ? explode(',', $project->getOptions()) : [],
                    'devices' => !empty($project->getDevice()) ? explode(',', $project->getDevice()) : [],

                ]
            ];
        } catch (\Exception $exception) {
            $this->logService->createLog('ERROR', ' Internal Servor Error at |' . $exception->getFile() . ' | line |' . $exception->getLine());


            return $this->json([
                'state' => 'ISE',
                'value' => ' Internal Servor Error : ' . $exception->getMessage() . ' at |' . $exception->getFile() . ' | line |' . $exception->getLine()

            ]);
        }
    }

    public function getDataProjectForMiniature($project)
    {
        try {

            $count = 0;
            foreach ($project->getTasks() as $task) {
                if ($task->getCol() == 'done') {
                    $count++;
                }
            }
            return [

                "id" => $project->getId(),
                "name" => $project->getName(),
                'uuid' => $project->getUuid(),
                "cratedAt" => $this->dateService->formateDate($project->getCreatedAt()),
                'totalTasks' => count($project->getTasks()),
                'doneTasks' => $count,
            ];
        } catch (\Exception $exception) {
            $this->logService->createLog('ERROR', ' Internal Servor Error at |' . $exception->getFile() . ' | line |' . $exception->getLine());


            return $this->json([
                'state' => 'ISE',
                'value' => ' Internal Servor Error : ' . $exception->getMessage() . ' at |' . $exception->getFile() . ' | line |' . $exception->getLine()

            ]);
        }
    }

    public function getDataProjectForSearch($project)
    {
        try {
            $client = null;
            if ($project->getClient()) {
                $client = [
                    'id' => $project->getClient()->getId(),
                    'firstName' => $project->getClient()->getFirstName(),
                    'lastName' => $project->getClient()->getLastName(),
                    'date' => $this->dateService->formateDate($project->getClient()->getCreatedAt()),
                ];

            }

            return [

                "id" => $project->getId(),
                "name" => $project->getName(),
                "client" => $client,

            ];
        } catch (\Exception $exception) {
            $this->logService->createLog('ERROR', ' Internal Servor Error at |' . $exception->getFile() . ' | line |' . $exception->getLine());


            return $this->json([
                'state' => 'ISE',
                'value' => ' Internal Servor Error : ' . $exception->getMessage() . ' at |' . $exception->getFile() . ' | line |' . $exception->getLine()

            ]);
        }
    }
}
