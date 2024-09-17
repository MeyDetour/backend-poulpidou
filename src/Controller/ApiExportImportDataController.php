<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Chat;
use App\Entity\Client;
use App\Entity\Message;
use App\Entity\Project;
use App\Entity\Task;
use App\Entity\User;
use App\Repository\ClientRepository;
use App\Repository\UserRepository;
use App\Service\DateService;
use App\Service\LogService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Attribute\Route;

class ApiExportImportDataController extends AbstractController
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
    private array $states = ['active', 'deleted'];

    public function __construct(LogService $logService, DateService $dateService)
    {
        $this->logService = $logService;
        $this->dateService = $dateService;

    }

    #[Route('/api/export/data', name: 'app_api_export_data', methods: 'get')]
    public function export(): Response
    {
        try {
            $fileSystem = new Filesystem();

            try {

                $user = $this->getUser();

                $clients = [];
                foreach ($user->getClients() as $client) {
                    $clients[] = [
                        "firstName" => $client->getFirstName(),
                        "lastName" => $client->getLastName(),
                        "phone" => $client->getPhone(),
                        "job" => $client->getJob(),
                        "age" => $client->getAge(),
                        "siret" => $client->getSiret(),
                        "location" => $client->getLocation(),
                        "mail" => $client->getMail(),
                        "createdAt" => $this->dateService->baseFormateDateWithHour($client->getCreatedAt())  ,
                        "state" => $client->getState(),

                    ];
                }
                $projects = [];
                $userProjects = $user->getProjects();
                foreach ($userProjects as $project) {
                    $chat = $project->getChat();
                    $formattedMessages = [];
                    foreach ($chat->getMessages() as $message) {

                        $type = null;
                        if ($message->getClient()) {
                            $author = $message->getClient()->getMail();
                            $type = 'client';
                        }
                        if ($message->getAuthorUser()) {
                            $author = $message->getAuthorUser()->getMail();
                            $type = 'user';
                        }

                        $formattedMessages[] = [
                            'content' => $message->getContent(),
                            'createdAt' => $this->dateService->baseFormateDateWithHour($message->getCreatedAt()),
                            'author' => $author,
                            'type' => $type
                        ];
                    }
                    $userAutorised = [];
                    foreach ($project->getUserAuthorised() as $user) {
                        if ($user != $project->getOwner()) {
                            $userAutorised[] = [
                                'email' => $user->getEmail(),
                            ];
                        }

                    }


                    $tasks = [];
                    foreach ($project->getTasks() as $task) {
                        $tasks[] = ['name' => $task->getName(),
                            'content' => $task->getDescription(),
                            'status' => $task->getCol(),
                            'dueDate' => $this->dateService->baseFormateDateWithHour($task->getDueDate()),
                            'author' => $task->getOwner()->getEmail(),
                            'category' => $task->getCategory()
                        ];
                    }

                    $projects[] = [
                        "totalPrice" => $project->getTotalPrice(),
                        "estimatedPrice" => $project->getEstimatedPrice(),
                        "maintenancePercentage" => $project->getMaintenancePercentage(),
                        'members' => $userAutorised,
                        'identity' => [
                            "id" => $project->getId(),
                            'uuid' => $project->getUuid(),
                            "name" => $project->getName(),
                            "figmaLink" => $project->getFigmaLink(),
                            "githubLink" => $project->getGithubLink(),
                            "websiteLink" => $project->getWebsiteLink(),
                            "startDate" => $this->dateService->baseFormateDateWithHour($project->getStartDate()),
                            "endDate" => $this->dateService->baseFormateDateWithHour($project->getEndDate()),
                            "clientMail" => $project->getClient()->getMail(),

                            "state" => $project->getState(),
                            "isCurrent" => $project->isCurrent(),
                            "createdAt" => $this->dateService->baseFormateDateWithHour($project->getCreatedAt()),
                            "chat" => [
                                'name' => $chat->getName(),
                                'createdAt' => $this->dateService->baseFormateDateWithHour($chat->getCreatedAt()),
                                'messages' => $formattedMessages
                            ]
                        ],

                        "noteNames" => $project->getNoteNames(),
                        "noteContents" => $project->getNoteContent(),
                        "rules" => [
                            'canEditInvoices' => $project->isOtherUserCanEditInvoices(),
                            'canSeeClientProfile' => $project->isCanOtherUserSeeClientProfile(),
                        ],
                        'composition' => [
                            'isPaying' => $project->isPaying(),
                            'database' => $project->isDatabase(),
                            'maquette' => $project->isMaquette(),
                            'maintenance' => $project->isMaintenance(),
                            'type' => $project->getType(),
                            'framework' => !empty($project->getFramework()) ? explode(',', $project->getFramework()) : [],
                            'options' => !empty($project->getOptions()) ? explode(',', $project->getOptions()) : [],
                            'devices' => !empty($project->getDevice()) ? explode(',', $project->getDevice()) : [],

                        ],
                        "tasks" => $tasks,
                    ];
                }


                $json = [
                    'user' => [
                        'phone' => $user->getPhone(),
                        'siret' => $user->getSiret(),
                        'address' => $user->getAdresse(),
                        'firstName' => $user->getFirstName(),
                        'lastName' => $user->getLastName(),
                        'notes' => $user->getNote(),
                        'remembers' => $user->getRemember(),
                    ],
                    'clients' => $clients
                    , 'projects' => $projects


                ];

                $todayDate = new \DateTime();
                $fileName = 'exportFile/' . $todayDate->format('YmdHis') . '.poulpidou';
                $fileSystem->dumpFile($fileName, $json);
                return $this->json($json);
                return $this->file($fileName, $todayDate->format('YmdHis') . '.poulpidou', ResponseHeaderBag::DISPOSITION_ATTACHMENT);


            } catch (IOException $e) {
                $this->logService->createLog('ERROR', ' Internal Servor Error ~'.$e->getMessage().'~ at |' . $e->getFile() . ' | line |' . $e->getLine());
                return new JsonResponse([

                        'state' => 'ISE',
                        'value' => ' Internal Servor Error : ' . $e->getMessage() . ' at |' . $e->getFile() . ' | line |' . $e->getLine()

                    ]
                    , Response::HTTP_INTERNAL_SERVER_ERROR);
            }

        } catch (\Exception $exception) {
            $this->logService->createLog('ERROR', ' Internal Servor Error ~'.$exception->getMessage().'~ at |' . $exception->getFile() . ' | line |' . $exception->getLine());
            return new JsonResponse([

                    'state' => 'ISE',
                    'value' => ' Internal Servor Error : ' . $exception->getMessage() . ' at |' . $exception->getFile() . ' | line |' . $exception->getLine()

                ]
                , Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/api/import/data', name: 'app_api_import_data')]
    public function import(EntityManagerInterface $entityManager, $data, ClientRepository $clientRepository, UserRepository $userRepository): Response
    {
        try {
            $unknowUser = $userRepository->findOneBy(['email' => 'larbin@gmail.com']);
            if (!$unknowUser) {
                $unknowUser = new User();
                $unknowUser->setPassword('$2y$13$1zFD2rA5vmlRDdx8asgaH.K.3iyaIvlS8HmYMASbcpJB.mNzEnchS');
                $unknowUser->setEmail('larbin@gmail.com');
                $unknowUser->setVerified(false);
                $unknowUser->setFirstName('Mr');
                $unknowUser->setLastName('LARBIN');
                $entityManager->persist($unknowUser);
                $entityManager->flush();
            }

            if (!isset($data['user']) || !isset($data['clients']) || !isset($data['projects'])) {
                return $this->error();
            }


            $currentUser = $this->getUser();

            $user = $data['user'];
            $clients = $data['clients'];
            $projects = $data['projects'];

            if ($this->verifyType($user, "firstName", 'string')) {
                $currentUser->setFirstName($user['firstName']);
            }
            if ($this->verifyType($user, "lastName", 'string')) {
                $currentUser->setLastName($user['lastName']);
            }
            if ($this->verifyType($user, "phone", 'string')) {
                $currentUser->setPhone($user['phone']);
            }
            if ($this->verifyType($user, "address", 'string')) {
                $currentUser->setAdresse($user['address']);
            }
            if ($this->verifyType($user, "siret", 'string')) {
                $currentUser->setSiret($user['siret']);
            }
            if ($this->verifyType($user, "notes", 'string')) {
                $currentUser->setNote($currentUser->getNote() . "\n \n\n" . $user['notes']);
            }
            if ($this->verifyType($user, "remembers", 'string')) {
                $currentUser->setRemember($currentUser->getRemember() . "\n \n \n" . $user['remembers']);
            }

            $entityManager->persist($user);

            foreach ($clients as $clientData) {
                try {
                    $client = new Client();
                    $client->setOnline(false);
                    $client->setOwner($this->getUser());

                    $date = $this->verifyType($clientData, "createdAt", 'datetime');

                    if (!$this->verifyType($clientData, "mail", 'string') || !$date) {
                        return $this->error();
                    }
                    $client->setMail($clientData['mail']);
                    $client->setCreatedAt($date->format('Y-m-d H:i'));

                    if ($this->verifyType($clientData, "firstName", 'string')) {
                        $client->setFirstName($clientData['firstName']);
                    }
                    if ($this->verifyType($clientData, "lastName", 'string')) {
                        $client->setLastName($clientData['lastName']);
                    }
                    if ($this->verifyType($clientData, "phone", 'string')) {
                        $client->setPhone($clientData['phone']);
                    }

                    if ($this->verifyType($clientData, "job", 'string')) {
                        $client->setJob($clientData['job']);
                    }
                    if ($this->verifyType($clientData, "age", 'int')) {

                        $client->setAge($clientData['age']);
                    }
                    if ($this->verifyType($clientData, "siret", 'string')) {
                        $client->setSiret($clientData['siret']);
                    }
                    if ($this->verifyType($clientData, "location", 'string')) {
                        $client->setLocation($clientData['location']);
                    }
                    if ($this->verifyType($clientData, "state", 'string') && in_array($clientData['state'], $this->states)) {
                        $client->setState($clientData['state']);
                    } else {
                        $client->setState('active');
                    }

                    $entityManager->persist($client);
                } catch (\Exception $exception) {

                }
            }
            $entityManager->flush();


            foreach ($projects as $projectData) {
                try {
                    // == basic condition
                    if (
                        !isset($projectData['identity']) ||
                        !isset($projectData['rules']) ||
                        !isset($projectData['composition']) ||
                        !isset($projectData['tasks']) ||
                        !is_array($projectData['tasks'])

                    ) {
                        return $this->error();
                    }

                    //==== verifying Tasks ====
                    foreach ($projectData['tasks'] as $taskData) {

                        if (
                            !$this->verifyType($taskData, "name", 'string') ||
                            !$this->verifyType($taskData, "content", 'string') ||
                            !$this->verifyType($taskData, "status", 'string') ||
                            !$this->verifyType($taskData, "author", 'string') ||
                            !$this->verifyType($taskData, "dueDate", 'datetime')
                        ) {
                            return $this->error();
                        }
                        if (!in_array($taskData['status'], ['waiting', 'progress', 'done'])) {
                            return $this->error();
                        }

                    }


                    //==== verifying chat ====
                    if (!isset($projectData['identity']) || !$this->verifyType($projectData['identity']['chat'])) {
                        return $this->error();
                    }
                    if (!$this->verifyType($projectData['identity']['chat'], 'name', 'string') || !$this->verifyType($projectData['identity']['chat'], 'createdAt', 'datetime') || !is_array($projectData['identity']['chat']['messages'])) {
                        return $this->error();
                    }
                    $chatData = $projectData['identity']['chat'];

                    //========================

                    //verifying messages of chats
                    foreach ($projectData['identity']['chat']['messages'] as $messageData) {
                        if (!$this->verifyType($messageData, 'content', 'string') || !$this->verifyType($messageData, 'createdAt', 'datetime') || !$this->verifyType($messageData, 'author', 'datetime') || !$this->verifyType($messageData, 'type', 'datetime') || !in_array($messageData['type'], ['client', 'user'])) {
                            return $this->error();
                        }
                        if ($messageData['type'] == 'client') {
                            if ($messageData['author'] != $projectData['identity']['clientMail']) {
                            }
                        }
                    }
                    //=======================


                    //import project
                    $project = new Project();
                    $project->setOwner($this->getUser());


                    //verifying not null field
                    $date = $this->verifyType($projectData['identity'], "createdAt", 'datetime');
                    if (

                        count(explode(',', $projectData['noteNames'])) != 5 ||
                        count(explode(',', $projectData['noteContents'])) != 5 ||

                        !$this->verifyType($projectData['identity'], "name", 'string') ||
                        !$date ||
                        !$this->verifyType($projectData['identity'], "clientMail", 'string')
                    ) {
                        return $this->error();
                    }


                    //attribut value verified precedently
                    $project->setNoteContent($projectData['noteContents']);
                    $project->setNoteNames($projectData['noteNames']);

                    $project->setName($projectData['identity']['name']);
                    $project->setCreatedAt($date->format('Y-m-d H:i'));
                    $client = $clientRepository->findOneBy(['mail' => $projectData['identity']['clientMail']]);
                    if (!$client || $client->getOwner() != $this->getUser()) {
                        return $this->error();
                    }
                    $project->setClient($client);

                    //import associed chat
                    $chat = new Chat();
                    $chat->setCreatedAt($date->format('Y-m-d H:i'));
                    $chat->setName($projectData['name']);
                    $chat->setClient($client);
                    if (is_array($projectData['members'])) {
                        foreach ($projectData['members'] as $email) {
                            $user = $userRepository->findOneBy(['email' => $email]);
                            if ($user) {
                                $project->addUserAuthorised($user);
                                $chat->addUser($user);
                            }

                        }
                    }


                    //===========   IMPORT ELEMENT OF PROJECT
                    if ($this->verifyType($projectData, "totalPrice", 'numeric')) {
                        $project->setTotalPrice($projectData['totalPrice']);
                    }
                    if ($this->verifyType($projectData, "estimatedPrice", 'numeric')) {
                        $project->setEstimatedPrice($projectData['estimatedPrice']);
                    }
                    if ($this->verifyType($projectData, "maintenancePercentage", 'int')) {
                        $project->setMaintenanceProject($projectData['maintenancePercentage']);
                    }
                    if ($this->verifyType($projectData['identity'], "figmaLink", 'string')) {
                        $project->setFigmaLink($projectData['identity']['figmaLink']);
                    }
                    if ($this->verifyType($projectData['identity'], "uuid", 'string')) {
                        $project->setUuid($projectData['identity']['uuid']);
                    }
                    if ($this->verifyType($projectData['identity'], "githubLink", 'string')) {
                        $project->setGithubLink($projectData['identity']['githubLink']);
                    }
                    if ($this->verifyType($projectData['identity'], "websiteLink", 'string')) {
                        $project->setWebsiteLink($projectData['identity']['websiteLink']);
                    }
                    if ($this->verifyType($projectData, "state", 'string') && in_array($projectData['identity']['state'], $this->states)) {
                        $project->setState($projectData['identity']['state']);
                    } else {
                        $project->setState('active');
                    }
                    if ($this->verifyType($projectData['identity'], "isCurrent", 'bool')) {
                        $project->setCurrent($projectData['identity']['isCurrent']);
                    }
                    $date = $this->verifyType($projectData['identity'], "startDate", 'datetime');
                    if ($date) {
                        $project->setStartDate($projectData['identity']['startDate']);
                    }
                    $date = $this->verifyType($projectData['identity'], "endDate", 'datetime');
                    if ($date) {
                        $project->setEndDate($projectData['identity']['endDate']);
                    }
                    if ($this->verifyType($projectData['rules'], "canEditInvoices", 'bool')) {
                        $project->setOtherUserCanEditInvoices($projectData['rules']['canEditInvoices']);
                    }
                    if ($this->verifyType($projectData['rules'], "canSeeClientProfile", 'bool')) {
                        $project->setCanOtherUserSeeClientProfile($projectData['rules']['canSeeClientProfile']);
                    }
                    if ($this->verifyType($projectData['composition'], "isPaying", 'bool')) {
                        $project->setPaying($projectData['composition']['isPaying']);
                    }
                    if ($this->verifyType($projectData['composition'], "database", 'bool')) {
                        $project->setDatabase($projectData['composition']['database']);
                    }
                    if ($this->verifyType($projectData['composition'], "maquette", 'bool')) {
                        $project->setMaquette($projectData['composition']['maquette']);
                    }
                    if ($this->verifyType($projectData['composition'], "maintenance", 'bool')) {
                        $project->setMaintenance($projectData['composition']['maintenance']);
                    }
                    if ($this->verifyType($projectData['composition'], "type", 'string')) {
                        foreach (explode(',', $projectData['composition']['type']) as $type) {
                            if (!in_array($type, $this->listeOfType)) {
                                return $this->error();
                            }
                        }
                        $project->setType($projectData['composition']['framework']);
                    }
                    if ($this->verifyType($projectData['composition'], "framework", 'string')) {
                        foreach (explode(',', $projectData['composition']['framework']) as $type) {
                            if (!in_array($type, $this->listeOfFrameworks)) {
                                return $this->error();
                            }
                        }
                        $project->setFramework($projectData['composition']['options']);
                    }
                    if ($this->verifyType($projectData['composition'], "options", 'string')) {
                        foreach (explode(',', $projectData['composition']['options']) as $type) {
                            if (!in_array($type, $this->listeOfOptions)) {
                                return $this->error();
                            }
                        }
                        $project->setFramework($projectData['composition']['options']);
                    }
                    if ($this->verifyType($projectData['composition'], "devices", 'string')) {
                        foreach (explode(',', $projectData['composition']['devices']) as $type) {
                            if (!in_array($type, $this->listeOfDevices)) {
                                return $this->error();
                            }
                        }
                        $project->setFramework($projectData['composition']['devices']);
                    }
                    $entityManager->persist($project);
                    $entityManager->flush();
                    //================================================================ PROJECT IS CREATED

                    //finish to import chat
                    $chat->setProject($project);
                    $entityManager->persist($chat);
                    $entityManager->flush();


                    //finish to import messages
                    $messages = $chatData['messages'];
                    foreach ($messages as $message) {
                        $mess = new Message();
                        $mess->setChat($chat);
                        $mess->setCreatedAt($this->verifyType($message, "createdAt", 'datetime'));
                        $mess->setContent($message['content']);
                        if ($mess['type'] == 'client') {
                            $mess->setClient($project->getClient());
                        }
                        if ($mess['type'] == 'user') {
                            $userToAdd = $userRepository->findOneBy(['mail' => $message['author']]);
                            if (!$userToAdd) {
                                $mess->setAuthorUser($unknowUser);
                            } else {
                                $mess->setAuthorUser($userToAdd);
                            }
                        }
                        $entityManager->persist($mess);
                    }

                    //  IMPORT TASKS

                    foreach ($projectData['tasks'] as $taskData) {
                        $task = new Task();
                        $task->setName($taskData['name']);
                        $task->setCol($taskData['status']);
                        $task->setProject($project);
                        $task->setDescription($taskData['content']);
                        $task->setCategory($taskData['category']);
                        $task->setDueDate($this->verifyType($taskData, "dueDate", 'datetime'));
                        $userToAdd = $userRepository->findOneBy(['mail' => $taskData['author']]);
                        if (!$userToAdd) {
                            $task->setOwner($unknowUser);
                        } else {
                            $task->setOwner($userToAdd);
                        }
                        $entityManager->persist($task);

                    }
                    $entityManager->flush();


                } catch
                (\Exception $exception) {

                }


            }
            return new JsonResponse([
                    'state' => 'OK',
                ]
                , Response::HTTP_OK);
        } catch
        (\Exception $exception) {
            $this->logService->createLog('ERROR', ' Internal Servor Error ~'.$exception->getMessage().'~ at |' . $exception->getFile() . ' | line |' . $exception->getLine());

            return new JsonResponse([
                    'state' => 'ISE', 'value' => ' Internal Servor Error : ' . $exception->getMessage() . ' at |' . $exception->getFile() . ' | line |' . $exception->getLine()
                ]
                , Response::HTTP_INTERNAL_SERVER_ERROR);

        }
    }

    public function error()
    {
        return new JsonResponse([
                'state' => 'OK', 'value' => "fichier corrompu"
            ]
            , Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    private function verifyType($data, $field, $type)
    {
        if (!isset($data[$field])) {
            return false;
        }
        $value = $data[$field];
        if ($type == 'string') {
            if (empty(trim($value))) {
                return false;
            }
        }
        if ($type == 'int') {
            if (filter_var($value, FILTER_VALIDATE_INT) === false || $value <= 0) {
                return false;
            }
        }
        if ($type == 'array') {
            if (!is_array($value)) {
                return false;
            }
        }
        if ($type == 'bool') {
            if (!is_bool($value)) {
                return false;
            }
        }
        if ($type == 'numeric') {
            $isValid = $value > 0 && is_numeric($value);
            if (!$isValid) {
                return false;
            }
        }
        if ($type == 'datetime') {
            $searchDate = \DateTime::createFromFormat('Y-m-d H:i', $value);
            if (!$searchDate) {
                return false;
            }
            return $searchDate;

        }
        return true;

    }
}
