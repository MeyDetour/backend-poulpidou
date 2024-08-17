<?php

namespace App\Controller;

use App\Entity\Message;
use App\Repository\ChatRepository;
use App\Repository\ClientRepository;
use App\Repository\MessageRepository;
use App\Repository\ProjectRepository;
use App\Service\DateService;
use App\Service\LogService;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ApiMessageController extends AbstractController
{
    private LogService $logService;
    private EntityManagerInterface $entityManager;
    private DateService $dateService;

    public function __construct(LogService $logService, DateService $dateService, EntityManagerInterface $entityManager)
    {
        $this->logService = $logService;
        $this->entityManager = $entityManager;
        $this->dateService = $dateService;
    }

    #[Route('/api/chats', name: 'api_get_chats', methods: ['get'])]
    public function getChats(Request $request, EntityManagerInterface $manager, ChatRepository $chatRepository): Response
    {
        try {

            $data = [];
            foreach ($this->getUser()->getChats() as $chat) {

                if (($chat->getProject()->getOwner() == $this->getUser() || $chat->getProject()->hasUserInUserAuthorised($this->getUser())) && $chat->getProject()->getState() != 'deleted') {
                    $data[] = $this->chatDataShortData($chat);
                }

            }
            return $this->json([
                "state" => 'OK',
                "value" =>
                    $data]);

        } catch
        (\Exception $exception) {
            $this->logService->createLog('ERROR', ' Internal Servor Error at |' . $exception->getFile() . ' | line |' . $exception->getLine() );


            return $this->json([
                'state' => 'ISE',
                'value' => ' Internal Servor Error : ' . $exception->getMessage() . ' at |' . $exception->getFile() . ' | line |' . $exception->getLine()

            ]);
        }
    }

    #[Route('/api/chat/{id}', name: 'api_get_one_chat', methods: ['get'])]
    public function getChat(Request $request, EntityManagerInterface $manager, $id, ChatRepository $chatRepository): Response
    {
        try {
            $chat = $chatRepository->find($id);
            if (!$chat) {
                return $this->json([
                    'state' => 'NDF',
                    'value' => 'chat'
                ]);
            }
            if ($chat->getProject()->getOwner() != $this->getUser() && !$chat->getProject()->hasUserInUserAuthorised($this->getUser())) {
                return $this->json([
                    'state' => 'FO',
                    'value' => 'project'
                ]);
            }
            if ($chat->getProject()->getState() == 'deleted') {
                return $this->json([
                    'state' => 'DD',
                    'value' => 'project'
                ]);
            }
            if ($chat->getProject()->getState() == 'deleted') {
                return $this->json([
                    'state' => 'DD',
                    'value' => 'project'
                ]);
            }
            return $this->json([
                "state" => 'OK',
                "value" =>
                    $this->chatData($chat)]);
        } catch
        (\Exception $exception) {
            $this->logService->createLog('ERROR', ' Internal Servor Error at |' . $exception->getFile() . ' | line |' . $exception->getLine() );


            return $this->json([
                'state' => 'ISE',
                'value' => ' Internal Servor Error : ' . $exception->getMessage() . ' at |' . $exception->getFile() . ' | line |' . $exception->getLine()

            ]);
        }
    }

    #[Route('/api/client/{id}/chat', name: 'api_get_chats_of_client', methods: ['get'])]
    public function getChatClient($id, ClientRepository $clientRepository, MessageRepository $messageRepository): Response
    {
        try {
            $client = $clientRepository->find($id);
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
            if ($client->getState() == 'deleted') {
                return $this->json([
                    'state' => 'DD',
                    'value' => 'client'
                ]);
            }
            $data = [];
            foreach ($client->getChats() as $chat) {
                if ($chat->getProject()->getState() != 'deleted') {
                    $data[] = [
                        'id' => $chat->getId(),
                        'name' => $chat->getName(),
                        'lastMessage' => [
                            "content" => $chat->getMessages()[0]->getContent(),
                            "date" => $this->dateService->formateDateWithHour($chat->getMessages()[0]->getCreatedAt()),
                        ]
                    ];
                }
            }
            return $this->json([
                "state" => 'OK',
                "value" =>
                    $data]);

        } catch
        (\Exception $exception) {
            $this->logService->createLog('ERROR', ' Internal Servor Error at |' . $exception->getFile() . ' | line |' . $exception->getLine() );


            return $this->json([
                'state' => 'ISE',
                'value' => ' Internal Servor Error : ' . $exception->getMessage() . ' at |' . $exception->getFile() . ' | line |' . $exception->getLine()

            ]);
        }
    }

    public function chatData($chat)
    {
        $client = $chat->getClient();
        $formattedMessages = [];
        foreach ($chat->getMessages() as $message) {
            $authorData = [];
            $type = null;
            if ($message->getClient()) {
                $author = $message->getClient();
                $type = 'client';
            }
            if ($message->getAuthorUser()) {
                $author = $message->getAuthorUser();
                $type = 'user';
            }

            $authorData = [
                'id' => $author->getId(),
                'firstname' => $author->getFirstname(),
                'lastname' => $author->getLastname(),
                'email' => $author->getMail(),
            ];

            $formattedMessages[] = [
                'id' => $message->getId(),
                'content' => $message->getContent(),
                'datetime' => $this->dateService->formateDateWithHour($message->getCreatedAt()),
                'author' => $authorData,
                'type' => $type
            ];
        }

        return [
            'state' => 'OK',
            'value' => [
                'id' => $chat->getId(),
                'name' => $chat->getName(),
                'date' => $this->dateService->formateDate($chat->getCreatedAt()),
                'project_id' => $chat->getProject()->getId(),
                'project_uuid' => $chat->getProject()->getUuid(),
                'client' => [
                    'id' => $client->getId(),
                    'firstName' => $client->getFirstName(),
                    'lastName' => $client->getLastName(),
                    'online' => $client->isOnline(),
                    'date' => $this->dateService->formateDate($client->getCreatedAt()),
                    'projectNumber' => count($client->getProjects()),
                ],
                'messages' => $formattedMessages

            ]

        ];
    }

    public function chatDataShortData($chat)
    {
        $users = [];
        foreach ($chat->getUsers() as $user) {
            $users[] = [
                'id' => $user->getId(),
                'firstName' => $user->getFirstName(),
                'lastName' => $user->getLastName(),
                'email' => $user->getMail(),
            ];
        }

        return [
            'state' => 'OK',
            'value' => [
                'id' => $chat->getId(),
                'name' => $chat->getName(),
                'date' => $this->dateService->formateDate($chat->getCreatedAt()),
                'client' => [
                    'id' => $chat->getClient()->getId(),
                    'firstName' => $chat->getClient()->getFirstName(),
                    'lastName' => $chat->getClient()->getLastName(),

                ],
                'users' => $users

            ]

        ];
    }

    #[Route('/message', name: 'new_message', methods: ['post'])]
    public function index(Request $request, ProjectRepository $projectRepository): Response
    {
        try {
            $data = json_decode($request->getContent(), true);
            if ($data) {

                // id can be uuid of project
                if (!isset($data['id']) || empty(trim($data['id']))) {
                    return $this->json([
                        'state' => 'NED',
                        'value' => 'id'
                    ]);
                }

                if (!isset($data['content']) || empty(trim($data['content']))) {
                    return $this->json([
                        'state' => 'NED',
                        'value' => 'content'
                    ]);
                }

                //get the project from the id (from user messagerie) or uuid (from interface client)
                $project = $projectRepository->findOneBy(["uuid" => $data['id']]);

                if (!$project) {
                    return $this->json([
                        'state' => 'NDF',
                        'value' => 'project'
                    ]);
                }
                if ($project->getState() == 'deleted') {
                    return $this->json([
                        'state' => 'DD',
                        'value' => 'project'
                    ]);
                }
                $message = new Message();


                $message->setClient($project->getClient());
                if ($this->newMessage($message, $data['content'], $project)) {
                    return $this->json([
                        'state' => 'OK',
                    ]);
                }

                return $this->json([
                    'state' => 'ISE',
                    'value' => 'failed to send message'
                ]);


            }
            return $this->json([
                'state' => 'ND',

            ]);

        } catch
        (\Exception $exception) {
            $this->logService->createLog('ERROR', ' Internal Servor Error at |' . $exception->getFile() . ' | line |' . $exception->getLine() );


            return $this->json([
                'state' => 'ISE',
                'value' => ' Internal Servor Error : ' . $exception->getMessage() . ' at |' . $exception->getFile() . ' | line |' . $exception->getLine()

            ]);
        }

    }

    #[Route('/api/message', name: 'api_new_message', methods: 'post')]
    public function message(Request $request, EntityManagerInterface $manager, ClientRepository $clientRepository, ProjectRepository $projectRepository): Response
    {
        try {
            $data = json_decode($request->getContent(), true);
            if ($data) {

                //verifying if needed data are set
                if (!isset($data['id'])) {
                    return $this->json([
                        'state' => 'NED',
                        'value' => 'id'
                    ]);
                }
                if (!is_numeric($data['id'])) {
                    return $this->json([
                        'state' => 'IDT',
                        'value' => 'id'
                    ]);
                }
                if (!isset($data['content']) || empty(trim($data['content']))) {
                    return $this->json([
                        'state' => 'NED',
                        'value' => 'content'
                    ]);
                }

                //get the project from the id (from user messagerie) or uuid (from interface client)
                $project = $projectRepository->find($data['id']);
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
                $message = new Message();
                $message->setAuthorUser($this->getUser());
                if ($this->newMessage($message, $data['content'], $project)) {
                    return $this->json([
                        'state' => 'OK',
                    ]);
                }
                return $this->json([
                    'state' => 'ISE',
                    'value' => 'failed to send message'
                ]);

            }
            return $this->json([
                'state' => 'ND',
            ]);

        } catch (\Exception $exception) {
            $this->logService->createLog('ERROR', ' Internal Servor Error at |' . $exception->getFile() . ' | line |' . $exception->getLine() );
            return $this->json([
                'state' => 'ISE',
                'value' => ' Internal Servor Error : ' . $exception->getMessage() . ' at |' . $exception->getFile() . ' | line |' . $exception->getLine()

            ]);
        }

    }

    #[Route('/api/delete/{id}/message', name: 'api_delete_message', methods: 'get')]
    public function removeMessage(Request $request, EntityManagerInterface $manager, $id, MessageRepository $messageRepository): Response
    {
        try {
            $data = json_decode($request->getContent(), true);

            $message = $messageRepository->find($id);
            if (!$message) {
                return $this->json([
                    'state' => 'NDF',
                    'value' => 'message'
                ]);
            }
            if ($message->getChat()->getProject()->getOwner() != $this->getUser() && !$message->getChat()->getProject()->hasUserInUserAuthorised($this->getUser())) {
                return $this->json([
                    'state' => 'FO',
                    'value' => 'project'
                ]);
            }
            if ($message->getProject()->getState() == 'deleted') {
                return $this->json([
                    'state' => 'DD',
                    'value' => 'project'
                ]);
            }
            if ($message->getAuthorUser() != $this->getUser()) {
                return $this->json([
                    'state' => 'FO',
                    'value' => 'message'
                ]);
            }
            $array = $message->getChat()->getMessages();
            $messagesArray = $array->toArray();
            if (end($messagesArray) != $message) {
                return $this->json([
                    'state' => 'ASFO',
                    'value' => 'message'
                ]);
            }
            $manager->remove($message);
            $manager->flush();
            return $this->json([
                'state' => 'OK',
            ]);


        } catch (\Exception $exception) {
            $this->logService->createLog('ERROR', ' Internal Servor Error at |' . $exception->getFile() . ' | line |' . $exception->getLine() );
            return $this->json(['state' => 'ISE',
                'value' => ' Internal Servor Error : ' . $exception->getMessage() . ' at |' . $exception->getFile() . ' | line |' . $exception->getLine()]);
        }

    }

    #[Route('/api/search/chat', name: 'search_chat', methods: 'get')]
    public function searchChat(Request $request, ChatRepository $chatRepository): Response
    {
        try {
            $datum = json_decode($request->getContent(), true);

            $data = [];
            if ($datum) {
                if (isset($datum['searchTerm']) && !empty(trim($datum['searchTerm']))) {
                    $chats = $chatRepository->searchAcrossTables($datum['searchTerm']);
                    $dataToReturn = [];
                    foreach ($chats as $chat) {
                        $dataToReturn[] = [
                            "id" => $chat->getId(),
                            "name" => $chat->getName()
                        ];


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
            $this->logService->createLog('ERROR', ' Internal Servor Error at |' . $exception->getFile() . ' | line |' . $exception->getLine() );


            return $this->json([
                'state' => 'ISE',
                'value' => ' Internal Servor Error : ' . $exception->getMessage() . ' at |' . $exception->getFile() . ' | line |' . $exception->getLine()

            ]);
        }
    }

    public
    function newMessage($message, $content, $project)
    {
        try {
            $message->setCreatedAt(new \DateTimeImmutable());
            $message->setContent($content);
            $message->setChat($project->getChat());
            $this->entityManager->persist($message);
            $this->entityManager->flush();
            return true;
        } catch (\Exception $exception) {
            $this->logService->createLog('ERROR', ' Internal Servor Error at |' . $exception->getFile() . ' | line |' . $exception->getLine() );
            return false;
        }

    }
}
