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
use Symfony\Component\HttpFoundation\JsonResponse;
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
                    return new JsonResponse( [
                        'state' => 'NED',
                        'value' => 'client_id',
                     ] , Response::HTTP_UNPROCESSABLE_ENTITY);
                }
                if (!is_numeric($data['identity']['client_id'])) {
                    return new JsonResponse( [
                        'state' => 'IDT',
                        'value' => 'client_id',
                     ] , Response::HTTP_UNPROCESSABLE_ENTITY);
                }

                if (!isset($data['identity']['name']) || empty(trim($data['identity']['name']))) {

                    return new JsonResponse( [
                        'state' => 'NED',
                        'value' => 'name',
                     ] , Response::HTTP_UNPROCESSABLE_ENTITY);
                }

                $client = $clientRepository->find($data['identity']['client_id']);
                if (!$client) {
                    return new JsonResponse( [
                        'state' => 'NDF',
                        'value' => 'client',
                     ] , Response::HTTP_NOT_FOUND);
                }
                if ($client->getOwner() != $this->getUser()) {

                    return new JsonResponse( [
                            'state' => 'FO',
                            'value' => 'client'
                        ]
                     , Response::HTTP_FORBIDDEN);
                }
                if ($client->getState() == 'deleted') {
                    return new JsonResponse( [
                        'state' => 'DD',
                        'value' => 'client',
                     ] , Response::HTTP_NOT_FOUND);
                }

                $project->setClient($client);
                $project->setName($data['identity']['name']);

                if (isset($data['identity']['figmaLink']) && !empty(trim($data['identity']['figmaLink']))) {
                    $project->setFigmaLink($data['identity']['figmaLink']);
                } if (isset($data['identity']['note']) && !empty(trim($data['identity']['note']))) {
                    $project->setNote($data['identity']['note']);
                }
                if (isset($data['identity']['githubLink']) && !empty(trim($data['identity']['githubLink']))) {
                    $project->setGithubLink($data['identity']['githubLink']);
                }
                if (isset($data['identity']['websiteLink']) && !empty(trim($data['identity']['websiteLink']))) {
                    $project->setWebsiteLink($data['identity']['websiteLink']);
                }
                if (isset($data['estimatedPrice']) && !empty(trim($data['estimatedPrice']))) {
                    $isValid = $data['estimatedPrice'] > 0 && is_numeric($data['estimatedPrice']);
                    if (!$isValid) {
                        return new JsonResponse( [
                            'state' => 'IDT',
                            'value' => 'estimatedPrice',
                         ] , Response::HTTP_UNPROCESSABLE_ENTITY);
                    }
                    $project->setEstimatedPrice($data['estimatedPrice']);
                }
                if (isset($data['maintenancePercentage']) && !empty(trim($data['maintenancePercentage']))) {
                    $isValid = $data['maintenancePercentage'] > 0 && is_numeric($data['maintenancePercentage']);
                    if (!$isValid) {
                        return new JsonResponse( [
                            'state' => 'IDT',
                            'value' => 'maintenancePErcentage',
                         ] , Response::HTTP_UNPROCESSABLE_ENTITY);
                    }
                    $project->setMaintenancePercentage($data['maintenancePercentage']);
                }

                if (isset($data['identity']['startDate']) && !empty(trim($data['identity']['startDate']))) {

                    $searchDate = \DateTime::createFromFormat('d/m/Y', $data['identity']['startDate']);
                    if (!$searchDate) {
                        return new JsonResponse( [
                            'state' => 'IDT',
                            'value' => 'startDate',
                         ] , Response::HTTP_UNPROCESSABLE_ENTITY);
                    }
                    $project->setStartDate($searchDate);
                }
                if (isset($data['identity']['endDate']) && !empty(trim($data['identity']['endDate']))) {
                    $searchDate = \DateTime::createFromFormat('d/m/Y', $data['identity']['endDate']);
                    if (!$searchDate) {
                        return new JsonResponse( [
                            'state' => 'IDT',
                            'value' => 'endDate',
                         ] , Response::HTTP_UNPROCESSABLE_ENTITY);
                    }
                    $project->setStartDate($searchDate);
                }
                if (isset($data['totalPrice']) && !empty(trim($data['totalPrice']))) {
                    $isValid = $data['totalPrice'] > 0 && is_numeric($data['totalPrice']);
                    if (!$isValid) {
                        return new JsonResponse( [
                            'state' => 'IDT',
                            'value' => 'totalPrice',
                         ] , Response::HTTP_UNPROCESSABLE_ENTITY);
                    }
                    $project->setTotalPrice($data['totalPrice']);
                }

                if (isset($data['composition']['isPaying'])) {
                    if (gettype($data['composition']['isPaying']) != 'string') {
                        return new JsonResponse( [
                            'state' => 'IDT',
                            'value' => 'isPaying',
                         ] , Response::HTTP_UNPROCESSABLE_ENTITY);
                    }
                    if ($data['composition']['isPaying'] != 'false' && $data['composition']['isPaying'] != 'true') {
                        return new JsonResponse( [
                            'state' => 'IDT',
                            'value' => 'isPAying',
                         ] , Response::HTTP_UNPROCESSABLE_ENTITY);
                    }

                    $project->setPaying(filter_var($data['composition']['isPaying'], FILTER_VALIDATE_BOOLEAN));
                }
                if (isset($data['composition']['database'])) {
                    if (gettype($data['composition']['database']) != 'string') {
                        return new JsonResponse( [
                            'state' => 'IDT',
                            'value' => 'database',
                         ] , Response::HTTP_UNPROCESSABLE_ENTITY);
                    }
                    if ($data['composition']['database'] != 'false' && $data['composition']['database'] != 'true') {
                        return new JsonResponse( [
                            'state' => 'IDT',
                            'value' => 'database',
                         ] , Response::HTTP_UNPROCESSABLE_ENTITY);
                    }
                       $project->setDatabase(filter_var($data['composition']['database'], FILTER_VALIDATE_BOOLEAN));

                }
                if (isset($data['composition']['maquette'])) {
                    if (gettype($data['composition']['maquette']) != 'string') {
                        return new JsonResponse( [
                            'state' => 'IDT',
                            'value' => 'maquette',
                         ] , Response::HTTP_UNPROCESSABLE_ENTITY);
                    }
                    if ($data['composition']['maquette'] != 'false' && $data['composition']['maquette'] != 'true') {
                        return new JsonResponse( [
                            'state' => 'IDT',
                            'value' => 'maquette',
                         ] , Response::HTTP_UNPROCESSABLE_ENTITY);
                    }
                    $project->setMaquette(filter_var($data['composition']['maquette'], FILTER_VALIDATE_BOOLEAN));
                }
                if (isset($data['composition']['maintenance'])) {
                    if (gettype($data['composition']['maintenance']) != 'string') {
                        return new JsonResponse( [
                            'state' => 'IDT',
                            'value' => 'maintenance',
                         ] , Response::HTTP_UNPROCESSABLE_ENTITY);
                    }
                    if ($data['composition']['maintenance'] != 'false' && $data['composition']['maintenance'] != 'true') {
                        return new JsonResponse( [
                            'state' => 'IDT',
                            'value' => 'maintenance',
                         ] , Response::HTTP_UNPROCESSABLE_ENTITY);
                    }  $project->setMaintenance(filter_var($data['composition']['maintenance'], FILTER_VALIDATE_BOOLEAN));
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
                        return new JsonResponse( [
                            'state' => 'IDT',
                            'value' => 'canEditInvoices',
                         ] , Response::HTTP_UNPROCESSABLE_ENTITY);
                    }
                    if ($data['rules']['canEditInvoices'] != 'false' && $data['rules']['canEditInvoices'] != 'true') {
                        return new JsonResponse( [
                            'state' => 'IDT',
                            'value' => 'canEditInvoices',
                         ] , Response::HTTP_UNPROCESSABLE_ENTITY);
                    }
                    $project->setOtherUserCanEditInvoices(filter_var($data['rules']['canEditInvoices'], FILTER_VALIDATE_BOOLEAN));

                }
                if (isset($data['rules']['canSeeClientProfile'])) {
                    if (gettype($data['rules']['canSeeClientProfile']) != 'string') {
                        return new JsonResponse( [
                            'state' => 'IDT',
                            'value' => 'canSeeClientProfile',
                         ] , Response::HTTP_UNPROCESSABLE_ENTITY);
                    }
                    if ($data['rules']['canSeeClientProfile'] != 'false' && $data['rules']['canSeeClientProfile'] != 'true') {
                        return new JsonResponse( [
                            'state' => 'IDT',
                            'value' => 'canSeeClientProfile',
                         ] , Response::HTTP_UNPROCESSABLE_ENTITY);
                    }
                    $project->setCanOtherUserSeeClientProfile(filter_var($data['rules']['canSeeClientProfile'], FILTER_VALIDATE_BOOLEAN));

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

                return new JsonResponse( [
                        'state' => 'OK', 'value' => $this->getDataProject($project)
                    ]
                 , Response::HTTP_OK, [], true);

            }
            return new JsonResponse( ['state' => 'ND' ] , Response::HTTP_BAD_REQUEST);
        } catch (\Exception $exception) {
            return new JsonResponse( [

                    'state' => 'ISE',
                    'value' => ' Internal Servor Error : ' . $exception->getMessage() . ' at |' . $exception->getFile() . ' | line |' . $exception->getLine()

                ]
             , Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/api/project/edit/{id}', name: 'edit_api_project',methods: 'put')]
    public function edit($id, ProjectRepository $repository, ClientRepository $clientRepository, Request $request, EntityManagerInterface $manager): Response
    {
        try {
            $project = $repository->find($id);
            if (!$project) {
                return new JsonResponse( [
                    'state' => 'NDF',
                    'value' => 'project',
                 ] , Response::HTTP_NOT_FOUND);
            }
            if ($project->getOwner() != $this->getUser() && !$project->hasUserInUserAuthorised($this->getUser())) {
                return new JsonResponse( [
                    'state' => 'FO',
                    'value' => 'project',
                 ] , Response::HTTP_FORBIDDEN);
            }
            if ($project->getState() == 'deleted') {
                return new JsonResponse( [
                    'state' => 'DD',
                    'value' => 'project',
                 ] , Response::HTTP_NOT_FOUND);
            }
            $data = json_decode($request->getContent(), true);

            if ($data) {

                {

                    $lastProject = clone $project;
                    if (isset($data['identity']['name']) && !empty(trim($data['identity']['name']))) {
                        $project->setName($data['identity']['name']);
                    }
                    if (isset($data['estimatedPrice']) && !empty(trim($data['estimatedPrice']))) {
                        $isValid = $data['estimatedPrice'] > 0 && is_numeric($data['estimatedPrice']);
                        if (!$isValid) {
                            return new JsonResponse( [
                                'state' => 'IDT',
                                'value' => 'estimatedPrice',
                             ] , Response::HTTP_UNPROCESSABLE_ENTITY);
                        }
                        $project->setEstimatedPrice($data['estimatedPrice']);
                    }


                    if (isset($data['totalPrice']) && !empty(trim($data['totalPrice']))) {

                        $isValid = $data['totalPrice'] > 0 && is_numeric($data['totalPrice']);
                        if (!$isValid) {
                            return new JsonResponse( [
                                'state' => 'IDT',
                                'value' => 'totalPrice',
                             ] , Response::HTTP_UNPROCESSABLE_ENTITY);
                        }
                        $project->setTotalPrice($data['totalPrice']);
                    }

                    if (isset($data['identity']['figmaLink']) && !empty(trim($data['identity']['figmaLink']))) {
                        $project->setFigmaLink($data['identity']['figmaLink']);
                    }
                    if (isset($data['identity']['websiteLink']) && !empty(trim($data['identity']['websiteLink']))) {
                        $project->setWebsiteLink($data['identity']['websiteLink']);
                    }
                    if (isset($data['identity']['githubLink']) && !empty(trim($data['identity']['githubLink']))) {
                        $project->setGithubLink($data['identity']['githubLink']);
                    }
                    if (isset($data['identity']['note']) && !empty(trim($data['identity']['note']))) {
                        $project->setNote($data['identity']['note']);
                    }
                    if (isset($data['identity']['startDate']) && !empty(trim($data['identity']['startDate']))) {

                        $searchDate = \DateTime::createFromFormat('d/m/Y', $data['identity']['startDate']);
                        if (!$searchDate) {
                            return new JsonResponse( [
                                'state' => 'IDT',
                                'value' => 'startDate',
                             ] , Response::HTTP_UNPROCESSABLE_ENTITY);
                        }
                        $project->setStartDate($searchDate);
                    }
                    if (isset($data['identity']['endDate']) && !empty(trim($data['identity']['endDate']))) {
                        $searchDate = \DateTime::createFromFormat('d/m/Y', $data['identity']['endDate']);
                        if (!$searchDate) {
                            return new JsonResponse( [
                                'state' => 'IDT',
                                'value' => 'endDate',
                             ] , Response::HTTP_UNPROCESSABLE_ENTITY);
                        }
                        $project->setStartDate($searchDate);
                    }

                    if (isset($data['composition']['isPaying'])) {
                        if (gettype($data['composition']['isPaying']) != 'string') {
                            return new JsonResponse( [
                                'state' => 'IDT',
                                'value' => 'isPaying',
                             ] , Response::HTTP_UNPROCESSABLE_ENTITY);
                        }
                        if ($data['composition']['isPaying'] != 'false' && $data['composition']['isPaying'] != 'true') {
                            return new JsonResponse( [
                                'state' => 'IDT',
                                'value' => 'canSeeClientProfile',
                             ] , Response::HTTP_UNPROCESSABLE_ENTITY);
                        }

                        $project->setPaying(filter_var($data['composition']['isPaying'], FILTER_VALIDATE_BOOLEAN));
                    }
                    if (isset($data['composition']['database'])) {
                        if (gettype($data['composition']['database']) != 'string') {
                            return new JsonResponse( [
                                'state' => 'IDT',
                                'value' => 'database',
                             ] , Response::HTTP_UNPROCESSABLE_ENTITY);
                        }
                        if ($data['composition']['database'] != 'false' && $data['composition']['database'] != 'true') {
                            return new JsonResponse( [
                                'state' => 'IDT',
                                'value' => 'database',
                             ] , Response::HTTP_UNPROCESSABLE_ENTITY);
                        }

                           $project->setDatabase(filter_var($data['composition']['database'], FILTER_VALIDATE_BOOLEAN));

                    }
                    if (isset($data['maintenancePercentage']) && !empty(trim($data['maintenancePercentage']))) {
                        $isValid = $data['maintenancePercentage'] > 0 && is_numeric($data['maintenancePercentage']);
                        if (!$isValid) {

                            return new JsonResponse( [
                                    'state' => 'IDT',
                                    'value' => 'maintenancePercentage'
                                ]
                             , Response::HTTP_UNPROCESSABLE_ENTITY);
                        }
                        $project->setMaintenancePercentage($data['maintenancePercentage']);
                    }
                    if (isset($data['composition']['maquette'])) {
                        if (gettype($data['composition']['maquette']) != 'string') {
                            return new JsonResponse( [
                                'state' => 'IDT',
                                'value' => 'maquette',
                             ] , Response::HTTP_UNPROCESSABLE_ENTITY);
                        }
                        if ($data['composition']['maquette'] != 'false' && $data['composition']['maquette'] != 'true') {
                            return new JsonResponse( [
                                'state' => 'IDT',
                                'value' => 'maquette',
                             ] , Response::HTTP_UNPROCESSABLE_ENTITY);
                        }
                        $project->setMaquette(filter_var($data['composition']['maquette'], FILTER_VALIDATE_BOOLEAN));
                    }
                    if (isset($data['composition']['maintenance'])) {
                        if (gettype($data['composition']['maintenance']) != 'string') {
                            return new JsonResponse( [
                                'state' => 'IDT',
                                'value' => 'maintenance',
                             ] , Response::HTTP_UNPROCESSABLE_ENTITY);
                        }
                        if ($data['composition']['maintenance'] != 'false' && $data['composition']['maintenance'] != 'true') {
                            return new JsonResponse( [
                                'state' => 'IDT',
                                'value' => 'maintenance',
                             ] , Response::HTTP_UNPROCESSABLE_ENTITY);
                        }
                        $project->setMaintenance(filter_var($data['composition']['maintenance'], FILTER_VALIDATE_BOOLEAN));
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
                            return new JsonResponse( [
                                'state' => 'IDT',
                                'value' => 'canEditInvoices',
                             ] , Response::HTTP_UNPROCESSABLE_ENTITY);
                        }
                        if ($data['rules']['canEditInvoices'] != 'false' && $data['rules']['canEditInvoices'] != 'true') {
                            return new JsonResponse( [
                                'state' => 'IDT',
                                'value' => 'canEditInvoices',
                             ] , Response::HTTP_UNPROCESSABLE_ENTITY);
                        }
                        $project->setOtherUserCanEditInvoices(filter_var($data['rules']['canEditInvoices'], FILTER_VALIDATE_BOOLEAN));

                    }
                    if (isset($data['rules']['canSeeClientProfile'])) {
                        if (gettype($data['rules']['canSeeClientProfile']) != 'string') {
                            return new JsonResponse( [
                                'state' => 'IDT',
                                'value' => 'canSeeClientProfile',
                             ] , Response::HTTP_UNPROCESSABLE_ENTITY);
                        }
                        if ($data['rules']['canSeeClientProfile'] != 'false' && $data['rules']['canSeeClientProfile'] != 'true') {
                            return new JsonResponse( [
                                'state' => 'IDT',
                                'value' => 'canSeeClientProfile',
                             ] , Response::HTTP_UNPROCESSABLE_ENTITY);
                        }
                        $project->setCanOtherUserSeeClientProfile(filter_var($data['rules']['canSeeClientProfile'], FILTER_VALIDATE_BOOLEAN));
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

                    if ($project->getName() != $lastProject->getName()) {
                        $this->logService->createLog('ACTION', " Edit Project's name ". $project->getId() . ':' . $lastProject->getName() .") to (" . $project->getId() . ':' . $project->getName() . ') for client (' . $project->getClient()->getId() . ' | ' . $project->getClient()->getFirstName() . ' ' . $project->getClient()->getLastName() . ')');

                    }else{
                        $this->logService->createLog('ACTION', ' Edit Project (' . $project->getId() . ':' . $project->getName() . ') for client (' . $project->getClient()->getId() . ' | ' . $project->getClient()->getFirstName() . ' ' . $project->getClient()->getLastName() . ')');

                    }


                    return new JsonResponse( [
                            'state' => 'OK', 'value' => $this->getDataProject($project)
                        ]
                     , Response::HTTP_OK, [], true);

                }

            }
            return new JsonResponse( ['state' => 'ND' ] , Response::HTTP_BAD_REQUEST);
        } catch (\Exception $exception) {
            return new JsonResponse( [

                    'state' => 'ISE',
                    'value' => ' Internal Servor Error : ' . $exception->getMessage() . ' at |' . $exception->getFile() . ' | line |' . $exception->getLine()

                ]
             , Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/api/project/{id}/switch/current', name: 'set_project_no_current', methods: 'put')]
    public function switchProjectToUnccurent($id, ProjectRepository $projectRepository, Request $request, EntityManagerInterface $manager): Response
    {
        try {
            $project = $projectRepository->find($id);
            if (!$project) {
                return new JsonResponse( [
                    'state' => 'NDF',
                    'value' => 'project',
                 ] , Response::HTTP_NOT_FOUND);
            }
            if ($project->getOwner() != $this->getUser() && !$project->hasUserInUserAuthorised($this->getUser())) {
                return new JsonResponse( [
                    'state' => 'FO',
                    'value' => 'project',
                 ] , Response::HTTP_FORBIDDEN);
            }
            if ($project->getState() == 'deleted') {
                return new JsonResponse( [
                    'state' => 'DD',
                    'value' => 'project',
                 ] , Response::HTTP_NOT_FOUND);
            }

            $data = json_decode($request->getContent(), true);

            if (!isset($data['isCurrent'])) {
                return new JsonResponse( [
                    'state' => 'NED',
                    'value' => 'isCurrent',
                 ] , Response::HTTP_UNPROCESSABLE_ENTITY);

            }
            if (!is_bool($data['isCurrent'])) {
                return new JsonResponse( [
                    'state' => 'IDT',
                    'value' => 'isCurrent',
                 ] , Response::HTTP_UNPROCESSABLE_ENTITY);

            }

            $project->setCurrent($data['isCurrent']);
            $manager->persist($project);
            $manager->flush();
            return new JsonResponse( [
                    'state' => 'OK',
                ]
             , Response::HTTP_OK, [], true);
        } catch (\Exception $exception) {
            $this->logService->createLog('ERROR', ' Internal Servor Error at |' . $exception->getFile() . ' | line |' . $exception->getLine());


            return new JsonResponse( [

                    'state' => 'ISE',
                    'value' => ' Internal Servor Error : ' . $exception->getMessage() . ' at |' . $exception->getFile() . ' | line |' . $exception->getLine()

                ]
             , Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }

    #[Route('/api/projects/{id}/switch/current', name: 'set_project_no_current', methods: 'put')]
    public function getCurrentsProjects($id, ProjectRepository $projectRepository, Request $request, EntityManagerInterface $manager): Response
    {
        try {
            $project = $projectRepository->find($id);
            if (!$project) {
                return new JsonResponse( [
                    'state' => 'NDF',
                    'value' => 'project',
                 ] , Response::HTTP_NOT_FOUND);
            }
            if ($project->getOwner() != $this->getUser() && !$project->hasUserInUserAuthorised($this->getUser())) {
                return new JsonResponse( [
                    'state' => 'FO',
                    'value' => 'project',
                 ] , Response::HTTP_FORBIDDEN);
            }
            if ($project->getState() == 'deleted') {
                return new JsonResponse( [
                    'state' => 'DD',
                    'value' => 'project',
                 ] , Response::HTTP_NOT_FOUND);
            }

            $data = json_decode($request->getContent(), true);

            if (!isset($data['isCurrent'])) {
                return new JsonResponse( [
                    'state' => 'NED',
                    'value' => 'isCurrent',
                 ] , Response::HTTP_UNPROCESSABLE_ENTITY);

            }
            if (!is_bool($data['isCurrent'])) {
                return new JsonResponse( [
                    'state' => 'IDT',
                    'value' => 'isCurrent',
                 ] , Response::HTTP_UNPROCESSABLE_ENTITY);

            }

            $project->setCurrent($data['isCurrent']);
            $manager->persist($project);
            $manager->flush();
            return new JsonResponse( [
                    'state' => 'OK',
                ]
             , Response::HTTP_OK, [], true);
        } catch (\Exception $exception) {
            $this->logService->createLog('ERROR', ' Internal Servor Error at |' . $exception->getFile() . ' | line |' . $exception->getLine());


            return new JsonResponse( [

                    'state' => 'ISE',
                    'value' => ' Internal Servor Error : ' . $exception->getMessage() . ' at |' . $exception->getFile() . ' | line |' . $exception->getLine()

                ]
             , Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }

    #[Route('/api/project/{id}/note', name: 'edit_note_project', methods: 'put')]
    public function editNote(Request $request, $id, EntityManagerInterface $manager, ProjectRepository $repository): Response
    {
        try {
            $project = $repository->find($id);
            if (!$project) {
                return new JsonResponse( [
                    'state' => 'NDF',
                    'value' => 'project',
                 ] , Response::HTTP_NOT_FOUND);
            }
            if ($project->getOwner() != $this->getUser() && !$project->hasUserInUserAuthorised($this->getUser())) {

                return new JsonResponse( [
                    'state' => 'FO',
                    'value' => 'project',
                 ] , Response::HTTP_FORBIDDEN);
            }
            if ($project->getState() == 'deleted') {
                return new JsonResponse( [
                    'state' => 'DD',
                    'value' => 'project',
                 ] , Response::HTTP_NOT_FOUND);
            }

            $data = json_decode($request->getContent(), true);

            if ($data) {


                if (!isset($data['names'])) {
                    return new JsonResponse( [
                        'state' => 'NED',
                        'value' => 'names',
                     ] , Response::HTTP_UNPROCESSABLE_ENTITY);
                }
                if (gettype($data['names']) != 'array') {
                    return new JsonResponse( [
                        'state' => 'IDT',
                        'value' => 'name',
                     ] , Response::HTTP_UNPROCESSABLE_ENTITY);
                }
                if (count($data['names']) !== 5) {
                    return new JsonResponse( [
                        'state' => 'NED',
                        'value' => 'names',
                     ] , Response::HTTP_UNPROCESSABLE_ENTITY);
                }
                if (!isset($data['contents'])) {

                    return new JsonResponse( [
                        'state' => 'NED',
                        'value' => 'contents',
                     ] , Response::HTTP_UNPROCESSABLE_ENTITY);
                }
                if (gettype($data['contents']) != 'array') {
                    return new JsonResponse( [
                        'state' => 'IDT',
                        'value' => 'contents',
                     ] , Response::HTTP_UNPROCESSABLE_ENTITY);
                }
                if (count($data['contents']) !== 5) {
                    return new JsonResponse( [
                        'state' => 'NED',
                        'value' => 'contents',
                     ] , Response::HTTP_UNPROCESSABLE_ENTITY);
                }


                $project->setNoteNames(implode(',', $data['names']));

                $project->setNoteContent(implode(',', $data['contents']));
                $manager->persist($project);
                $manager->flush();
                $this->logService->createLog('ACTION', ' Edit Project (' . $project->getId() . ':' . $project->getName() . ') for client (' . $project->getClient()->getId() . ' | ' . $project->getClient()->getFirstName() . ' ' . $project->getClient()->getLastName() . ')');


                return new JsonResponse( [
                        'state' => 'OK', 'value' => $this->getDataProject($project)
                    ]
                 , Response::HTTP_OK, [], true);
            }

            return new JsonResponse( ['state' => 'ND' ] , Response::HTTP_BAD_REQUEST);
        } catch (\Exception $exception) {
            return new JsonResponse( [

                    'state' => 'ISE',
                    'value' => ' Internal Servor Error : ' . $exception->getMessage() . ' at |' . $exception->getFile() . ' | line |' . $exception->getLine()

                ]
             , Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }

    #[Route('/api/project/{id}/remove/user/{userId}', name: 'remove_user_to_project', methods: 'delete')]
    #[Route('/api/project/{id}/add/user/{userId}', name: 'add_user_to_project', methods: 'put')]
    public function addUserToProject($id, ProjectRepository $repository, $userId, UserRepository $userRepository, EntityManagerInterface $manager, Request $request): Response
    {


        try {
            $project = $repository->find($id);
            if (!$project) {
                return new JsonResponse( [
                    'state' => 'NDF',
                    'value' => 'project',
                 ] , Response::HTTP_NOT_FOUND);
            }
            if ($project->getOwner() != $this->getUser()) {
                return new JsonResponse( [
                    'state' => 'FO',
                    'value' => 'project',
                 ] , Response::HTTP_FORBIDDEN);
            }
            if ($project->getState() == 'deleted') {
                return new JsonResponse( [
                    'state' => 'DD',
                    'value' => 'project',
                 ] , Response::HTTP_NOT_FOUND);
            }
            $user = $userRepository->find($userId);
            if (!$user) {
                return new JsonResponse( [
                    'state' => 'NDF',
                    'value' => 'user',
                 ] , Response::HTTP_NOT_FOUND);
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
            return new JsonResponse( [
                    'state' => 'OK',
                ]
             , Response::HTTP_OK, [], true);

        } catch (\Exception $exception) {
            $this->logService->createLog('ERROR', ' Internal Servor Error at |' . $exception->getFile() . ' | line |' . $exception->getLine());


            return new JsonResponse( [

                    'state' => 'ISE',
                    'value' => ' Internal Servor Error : ' . $exception->getMessage() . ' at |' . $exception->getFile() . ' | line |' . $exception->getLine()

                ]
             , Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/api/project/{id}/left', name: 'left_user_to_project', methods: 'delete')]
    public function leftProject($id, ProjectRepository $repository, EntityManagerInterface $manager): Response
    {
        try {
            $project = $repository->find($id);
            if (!$project) {
                return new JsonResponse( [
                    'state' => 'NDF',
                    'value' => 'project',
                 ] , Response::HTTP_NOT_FOUND);
            }
            if ($project->getOwner() != $this->getUser()) {
                return new JsonResponse( [
                    'state' => 'FO',
                    'value' => 'project',
                 ] , Response::HTTP_FORBIDDEN);
            }
            if ($project->getState() == 'deleted') {
                return new JsonResponse( [
                    'state' => 'DD',
                    'value' => 'project',
                 ] , Response::HTTP_NOT_FOUND);
            }
            if ($project->hasUserInUserAuthorised($this->getUser())) {
                $project->removeUserAuthorised($this->getUser());
                $project->getChat()->removeUser($this->getUser());
            }


            $manager->persist($project);
            $manager->persist($project->getChat());
            $manager->flush();
            return new JsonResponse( [
                    'state' => 'OK',
                ]
             , Response::HTTP_OK, [], true);

        } catch (\Exception $exception) {
            $this->logService->createLog('ERROR', ' Internal Servor Error at |' . $exception->getFile() . ' | line |' . $exception->getLine());


            return new JsonResponse( [

                    'state' => 'ISE',
                    'value' => ' Internal Servor Error : ' . $exception->getMessage() . ' at |' . $exception->getFile() . ' | line |' . $exception->getLine()

                ]
             , Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    #[
        Route('/api/project/delete/{id}', name: 'delete_project', methods: 'delete')]
    public function delete($id, ProjectRepository $repository, EntityManagerInterface $manager): Response
    {
        try {
            $project = $repository->find($id);
            if (!$project) {
                return new JsonResponse( [
                    'state' => 'NDF',
                    'value' => 'project',
                 ] , Response::HTTP_NOT_FOUND);
            }
            if ($project->getOwner() != $this->getUser() && !$project->hasUserInUserAuthorised($this->getUser())) {
                return new JsonResponse( [
                    'state' => 'FO',
                    'value' => 'project',
                 ] , Response::HTTP_FORBIDDEN);
            }
            if ($project->getState() == 'deleted') {
                return new JsonResponse( [
                    'state' => 'DD',
                    'value' => 'project',
                 ] , Response::HTTP_NOT_FOUND);
            }
            $project->setState('deleted');
            $manager->persist($project);
            $manager->flush();
            $this->logService->createLog('DELETE', ' Delete Project (' . $project->getId() . ':' . $project->getName() . ') for client (' . $project->getClient()->getId() . ' | ' . $project->getClient()->getFirstName() . ' ' . $project->getClient()->getLastName() . ')');
            return new JsonResponse( [
                    'state' => 'OK',
                ]
             , Response::HTTP_OK, [], true);
        } catch (\Exception $exception) {
            $this->logService->createLog('ERROR', ' Internal Servor Error at |' . $exception->getFile() . ' | line |' . $exception->getLine());


            return new JsonResponse( [

                    'state' => 'ISE',
                    'value' => ' Internal Servor Error : ' . $exception->getMessage() . ' at |' . $exception->getFile() . ' | line |' . $exception->getLine()

                ]
             , Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/api/project/deleteforce/{id}', name: 'delete_force_project', methods: 'delete')]
    public function deleteforce($id, ProjectRepository $repository, EntityManagerInterface $manager): Response
    {
        try {
            $project = $repository->find($id);
            if (!$project) {
                return new JsonResponse( [
                    'state' => 'NDF',
                    'value' => 'project',
                 ] , Response::HTTP_NOT_FOUND);
            }
            if ($project->getOwner() != $this->getUser() && !$project->hasUserInUserAuthorised($this->getUser())) {
                return new JsonResponse( [
                    'state' => 'FO',
                    'value' => 'project',
                 ] , Response::HTTP_FORBIDDEN);
            }
            if ($project->getState() == 'deleted') {
                return new JsonResponse( [
                    'state' => 'DD',
                    'value' => 'project',
                 ] , Response::HTTP_NOT_FOUND);
            }
            $message = ' Delete force Project (' . $project->getId() . ':' . $project->getName() . ') for client (' . $project->getClient()->getId() . ' | ' . $project->getClient()->getFirstName() . ' ' . $project->getClient()->getLastName() . ')';


            foreach ($project->getTasks() as $task) {
                $manager->remove($task);
            }
            foreach ($project->getInvoices() as $invoice) {
                $manager->remove($invoice);
            }
            foreach ($project->getPdfs() as $pdf) {
                $filePath = $this->getParameter('upload_directory') . '/' . $pdf->getFileName();


                if (!file_exists($filePath)) {
                    return new JsonResponse( [
                        'state' => 'NDF',
                        'value' => 'pdf',
                     ] , Response::HTTP_NOT_FOUND);
                }

                $filePath = 'pdf/' . $pdf->getFileName();
                if (unlink($filePath)) {
                    $manager->remove($pdf);

                } else {

                    return new JsonResponse( [
                            'state' => 'ISE',
                            'value' => 'Failed to remove pdf'
                        ]
                     , Response::HTTP_INTERNAL_SERVER_ERROR);
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

            return new JsonResponse( [
                    'state' => 'OK',
                ]
             , Response::HTTP_OK, [], true);
        } catch (\Exception $exception) {
            $this->logService->createLog('ERROR', ' Internal Servor Error at |' . $exception->getFile() . ' | line |' . $exception->getLine());


            return new JsonResponse( [

                    'state' => 'ISE',
                    'value' => ' Internal Servor Error : ' . $exception->getMessage() . ' at |' . $exception->getFile() . ' | line |' . $exception->getLine()

                ]
             , Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/api/project/{id}', name: 'get_project', methods: 'get')]
    public function getProject($id, ProjectRepository $repository): Response
    {
        try {
            $project = $repository->find($id);
            if (!$project) {
                return new JsonResponse( [
                    'state' => 'NDF',
                    'value' => 'project',
                 ] , Response::HTTP_NOT_FOUND);
            }
            if ($project->getOwner() != $this->getUser() && !$project->hasUserInUserAuthorised($this->getUser())) {
                return new JsonResponse( [
                    'state' => 'FO',
                    'value' => 'project',
                 ] , Response::HTTP_FORBIDDEN);
            }
            if ($project->getState() == 'deleted') {
                return new JsonResponse( [
                    'state' => 'DD',
                    'value' => 'project',
                 ] , Response::HTTP_NOT_FOUND);
            }
            return new JsonResponse( [
                    'state' => 'OK', 'value' => $this->getDataProject($project)
                ]
             , Response::HTTP_OK, [], true);
        } catch (\Exception $exception) {
            $this->logService->createLog('ERROR', ' Internal Servor Error at |' . $exception->getFile() . ' | line |' . $exception->getLine());


            return new JsonResponse( [

                    'state' => 'ISE',
                    'value' => ' Internal Servor Error : ' . $exception->getMessage() . ' at |' . $exception->getFile() . ' | line |' . $exception->getLine()

                ]
             , Response::HTTP_INTERNAL_SERVER_ERROR);
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


            return new JsonResponse( [
                    'state' => 'OK', 'value' => $data
                ]
             , Response::HTTP_OK, [], true);
        } catch (\Exception $exception) {
            $this->logService->createLog('ERROR', ' Internal Servor Error at |' . $exception->getFile() . ' | line |' . $exception->getLine());


            return new JsonResponse( [

                    'state' => 'ISE',
                    'value' => ' Internal Servor Error : ' . $exception->getMessage() . ' at |' . $exception->getFile() . ' | line |' . $exception->getLine()

                ]
             , Response::HTTP_INTERNAL_SERVER_ERROR);
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

            return new JsonResponse([
                    'state' => 'OK',
                    'value' => $data
                ]
            , Response::HTTP_OK, [], true);
        } catch (\Exception $exception) {
            $this->logService->createLog('ERROR', ' Internal Servor Error at |' . $exception->getFile() . ' | line |' . $exception->getLine());


            return new JsonResponse( [

                    'state' => 'ISE',
                    'value' => ' Internal Servor Error : ' . $exception->getMessage() . ' at |' . $exception->getFile() . ' | line |' . $exception->getLine()

                ]
             , Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/api/project/{id}/get/client', name: 'get_project_client', methods: 'get')]
    public function getClientOfProject($id, ProjectRepository $repository): Response
    {
        try {
            $project = $repository->find($id);
            if (!$project) {
                return new JsonResponse( [
                    'state' => 'NDF',
                    'value' => 'project',
                 ] , Response::HTTP_NOT_FOUND);
            }
            if ($project->getOwner() != $this->getUser() && !$project->hasUserInUserAuthorised($this->getUser())) {
                return new JsonResponse( [
                    'state' => 'FO',
                    'value' => 'project',
                 ] , Response::HTTP_FORBIDDEN);
            }
            if (!$project->isCanOtherUserSeeClientProfile() && $project->getOwner() != $this->getUser()) {

                return new JsonResponse( [
                        'state' => 'ASFO',
                        'value' => 'project'
                    ]
                 , Response::HTTP_OK, [], true);
            }

            if ($project->getState() == 'deleted') {
                return new JsonResponse( [
                    'state' => 'DD',
                    'value' => 'project',
                 ] , Response::HTTP_NOT_FOUND);
            }

            return new JsonResponse( [
                    'state' => 'OK', 'value' => [
                        'id' => $project->getClient()->getId(),
                        'firstName' => $project->getClient()->getFirstName(),
                        'lastName' => $project->getClient()->getLastName(),
                        'date' => $this->dateService->formateDate($project->getClient()->getCreatedAt()),
                    ]
                ]
             , Response::HTTP_OK, [], true);
        } catch (\Exception $exception) {
            $this->logService->createLog('ERROR', ' Internal Servor Error at |' . $exception->getFile() . ' | line |' . $exception->getLine());


            return new JsonResponse( [

                    'state' => 'ISE',
                    'value' => ' Internal Servor Error : ' . $exception->getMessage() . ' at |' . $exception->getFile() . ' | line |' . $exception->getLine()

                ]
             , Response::HTTP_INTERNAL_SERVER_ERROR);
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

                    return new JsonResponse( [
                            'state' => 'OK', 'value' => $dataToReturn
                        ]
                     , Response::HTTP_OK, [], true);
                }
            }

            return new JsonResponse( ['state' => 'ND' ] , Response::HTTP_BAD_REQUEST);
        } catch (\Exception $exception) {
            $this->logService->createLog('ERROR', ' Internal Servor Error at |' . $exception->getFile() . ' | line |' . $exception->getLine());


            return new JsonResponse( [

                    'state' => 'ISE',
                    'value' => ' Internal Servor Error : ' . $exception->getMessage() . ' at |' . $exception->getFile() . ' | line |' . $exception->getLine()

                ]
             , Response::HTTP_INTERNAL_SERVER_ERROR);
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
                    "note" => $project->getNote(),
                    "figmaLink" => $project->getFigmaLink(),
                    "githubLink" => $project->getGithubLink(),
                    "websiteLink" => $project->getWebsiteLink(),
                    "startDateBaseFormat" => $this->dateService->baseFormateDate($project->getStartDate()),
                    "startDate" => $this->dateService->formateDate($project->getStartDate()),
                    "endDate" => $this->dateService->formateDate($project->getEndDate()),
                    "endDateBaseFormat" => $this->dateService->baseFormateDate($project->getEndDate()),
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


            return new JsonResponse( [

                    'state' => 'ISE',
                    'value' => ' Internal Servor Error : ' . $exception->getMessage() . ' at |' . $exception->getFile() . ' | line |' . $exception->getLine()

                ]
             , Response::HTTP_INTERNAL_SERVER_ERROR);
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


            return new JsonResponse( [

                    'state' => 'ISE',
                    'value' => ' Internal Servor Error : ' . $exception->getMessage() . ' at |' . $exception->getFile() . ' | line |' . $exception->getLine()

                ]
             , Response::HTTP_INTERNAL_SERVER_ERROR);
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
                    'email' => $project->getClient()->getMail(),
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
            return new JsonResponse( [

                    'state' => 'ISE',
                    'value' => ' Internal Servor Error : ' . $exception->getMessage() . ' at |' . $exception->getFile() . ' | line |' . $exception->getLine()

                ]
             , Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
