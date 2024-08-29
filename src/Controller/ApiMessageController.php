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
use Symfony\Component\HttpFoundation\JsonResponse;
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

            $data = [
                'read' => [],
                'unread' => []
            ];
            foreach ($this->getUser()->getChats() as $chat) {

                if (($chat->getProject()->getOwner() == $this->getUser() || $chat->getProject()->hasUserInUserAuthorised($this->getUser())) && $chat->getProject()->getState() != 'deleted') {

                    if ($chat->isRead()) {

                        $data['read'][] = $this->chatDataShortData($chat);
                    } else {
                        $data['unread'][] = $this->chatDataShortData($chat);
                    }
                }

            }
            return new JsonResponse([
                    'state' => 'OK',
                    "value" =>
                        $data
                ]
                , Response::HTTP_OK);

        } catch
        (\Exception $exception) {
            $this->logService->createLog('ERROR', ' Internal Servor Error at |' . $exception->getFile() . ' | line |' . $exception->getLine());


            return new JsonResponse([

                    'state' => 'ISE',
                    'value' => ' Internal Servor Error : ' . $exception->getMessage() . ' at |' . $exception->getFile() . ' | line |' . $exception->getLine()

                ]
                , Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/api/chat/{id}', name: 'api_get_one_chat', methods: ['get'])]
    public function getChat(Request $request, EntityManagerInterface $manager, $id, ChatRepository $chatRepository): Response
    {
        try {
            $chat = $chatRepository->find($id);
            if (!$chat) {
                return new JsonResponse([
                    'state' => 'NDF',
                    'value' => 'chat',
                ], Response::HTTP_NOT_FOUND);
            }
            if ($chat->getProject()->getOwner() != $this->getUser() && !$chat->getProject()->hasUserInUserAuthorised($this->getUser())) {
                return new JsonResponse([
                    'state' => 'FO',
                    'value' => 'project',
                ], Response::HTTP_FORBIDDEN);
            }
            if ($chat->getProject()->getState() == 'deleted') {
                return new JsonResponse([
                    'state' => 'DD',
                    'value' => 'project',
                ], Response::HTTP_NOT_FOUND);
            }
            if ($chat->getProject()->getState() == 'deleted') {
                return new JsonResponse([
                    'state' => 'DD',
                    'value' => 'project',
                ], Response::HTTP_NOT_FOUND);
            }
            $chat->setRead(true);
            $manager->persist($chat);
            $manager->flush();
            return new JsonResponse([
                    'state' => 'OK', "value" => $this->chatData($chat)
                ]
                , Response::HTTP_OK);
        } catch
        (\Exception $exception) {
            $this->logService->createLog('ERROR', ' Internal Servor Error at |' . $exception->getFile() . ' | line |' . $exception->getLine());


            return new JsonResponse([

                    'state' => 'ISE',
                    'value' => ' Internal Servor Error : ' . $exception->getMessage() . ' at |' . $exception->getFile() . ' | line |' . $exception->getLine()

                ]
                , Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/api/client/{id}/chats', name: 'api_get_chats_of_client', methods: ['get'])]
    public function getChatClient($id, ClientRepository $clientRepository, MessageRepository $messageRepository): Response
    {
        try {
            $client = $clientRepository->find($id);
            if (!$client) {
                return new JsonResponse([
                    'state' => 'NDF',
                    'value' => 'client',
                ], Response::HTTP_NOT_FOUND);
            }
            if (!$client->getOwner() == $this->getUser()) {
                return new JsonResponse([
                    'state' => 'FO',
                    'value' => 'client',
                ], Response::HTTP_FORBIDDEN);
            }
            if ($client->getState() == 'deleted') {
                return new JsonResponse([
                    'state' => 'DD',
                    'value' => 'client',
                ], Response::HTTP_NOT_FOUND);
            }
            $data = [];
            foreach ($client->getChats() as $chat) {
                $lastMessage = null;
                $createdAt = null;
                if (count($chat->getMessages()) == 0 ) {
                    $lastMessage = end($chat->getMessages())->getContent() ;
                    $createdAt =  end($chat->getMessages())->getCreatedAt() ;
                }
                if ($chat->getProject()->getState() != 'deleted') {
                    $data[] = [
                        'id' => $chat->getId(),
                        'name' => $chat->getName(),
                        'lastMessage' => [
                            "content" => $lastMessage,
                            "date" => $createdAt,
                        ]
                    ];
                }
            }

            return new JsonResponse([
                    'state' => 'OK', "value" => $data
                ]
                , Response::HTTP_OK);

        } catch
        (\Exception $exception) {
            $this->logService->createLog('ERROR', ' Internal Servor Error at |' . $exception->getFile() . ' | line |' . $exception->getLine());


            return new JsonResponse([

                    'state' => 'ISE',
                    'value' => ' Internal Servor Error : ' . $exception->getMessage() . ' at |' . $exception->getFile() . ' | line |' . $exception->getLine()

                ]
                , Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function chatData($chat)
    {
        $client = $chat->getClient();
        $formattedMessages = [];
        foreach ($chat->getMessages() as $message) {

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
                "chat"=>[
                    'id' => $chat->getId(),
                    'name' => $chat->getName(),
                    'date' => $this->dateService->formateDate($chat->getCreatedAt()),
                    'project_id' => $chat->getProject()->getId(),
                    'project_uuid' => $chat->getProject()->getUuid(),
                ],
                'client' => [
                    'id' => $client->getId(),
                    'firstName' => $client->getFirstName(),
                    'lastName' => $client->getLastName(),
                    'online' => $client->isOnline(),
                    'date' => $this->dateService->formateDate($client->getCreatedAt()),
                    'projectNumber' => count($client->getProjects())
                ],
                'messages' => $formattedMessages



        ];
    }

    public function chatDataShortData($chat)
    {   $lastMessage = null;
        if (count($chat->getMessages()) == 0) {
            $lastMessage =  end($chat->getMessages())->getContent() ;
        }
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

                'id' => $chat->getId(),
                'name' => $chat->getName(),
                'date' => $this->dateService->formateDate($chat->getCreatedAt()),
            "lastMessage"=> $lastMessage,
                'client' => [
                    'id' => $chat->getClient()->getId(),
                    'firstName' => $chat->getClient()->getFirstName(),
                    'lastName' => $chat->getClient()->getLastName(),
                    'online'=>$chat->getClient()->isOnline(),
                ],
                'users' => $users



        ];
    }

    #[Route('/message', name: 'new_message', methods: ['post'])]
    public function index(Request $request, ProjectRepository $projectRepository, EntityManagerInterface $entityManager): Response
    {
        try {
            $data = json_decode($request->getContent(), true);
            if ($data) {

                // id can be uuid of project
                if (!isset($data['id']) || empty(trim($data['id']))) {
                    return new JsonResponse([
                        'state' => 'NED',
                        'value' => 'id',
                    ], Response::HTTP_UNPROCESSABLE_ENTITY);
                }

                if (!isset($data['content']) || empty(trim($data['content']))) {
                    return new JsonResponse([
                        'state' => 'NED',
                        'value' => 'content',
                    ], Response::HTTP_UNPROCESSABLE_ENTITY);
                }

                //get the project from the id (from user messagerie) or uuid (from interface client)
                $project = $projectRepository->findOneBy(["uuid" => $data['id']]);

                if (!$project) {
                    return new JsonResponse([
                        'state' => 'NDF',
                        'value' => 'project',
                    ], Response::HTTP_NOT_FOUND);
                }
                if ($project->getState() == 'deleted') {
                    return new JsonResponse([
                        'state' => 'DD',
                        'value' => 'project',
                    ], Response::HTTP_NOT_FOUND);
                }
                $message = new Message();

                $chat = $message->getChat();
                $chat->setRead(false);
                $entityManager->persist($chat);
                $entityManager->flush();
                $message->setClient($project->getClient());

                if ($this->newMessage($message, $data['content'], $project)) {
                    return new JsonResponse([
                            'state' => 'OK',
                        ]
                        , Response::HTTP_OK);
                }
                return new JsonResponse([
                        'state' => 'ASFO', 'value' => 'failed to send message'
                    ]
                    , Response::HTTP_INTERNAL_SERVER_ERROR);


            }
            return new JsonResponse(['state' => 'ND'], Response::HTTP_BAD_REQUEST);

        } catch
        (\Exception $exception) {
            $this->logService->createLog('ERROR', ' Internal Servor Error at |' . $exception->getFile() . ' | line |' . $exception->getLine());


            return new JsonResponse([

                    'state' => 'ISE',
                    'value' => ' Internal Servor Error : ' . $exception->getMessage() . ' at |' . $exception->getFile() . ' | line |' . $exception->getLine()

                ]
                , Response::HTTP_INTERNAL_SERVER_ERROR);
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
                    return new JsonResponse([
                        'state' => 'NED',
                        'value' => 'id',
                    ], Response::HTTP_UNPROCESSABLE_ENTITY);
                }
                if (!is_numeric($data['id'])) {
                    return new JsonResponse([
                        'state' => 'IDT',
                        'value' => 'id',
                    ], Response::HTTP_UNPROCESSABLE_ENTITY);
                }
                if (!isset($data['content']) || empty(trim($data['content']))) {
                    return new JsonResponse([
                        'state' => 'NED',
                        'value' => 'content',
                    ], Response::HTTP_UNPROCESSABLE_ENTITY);
                }

                //get the project from the id (from user messagerie) or uuid (from interface client)
                $project = $projectRepository->find($data['id']);
                if (!$project) {
                    return new JsonResponse([
                        'state' => 'NDF',
                        'value' => 'project',
                    ], Response::HTTP_NOT_FOUND);
                }
                if ($project->getOwner() != $this->getUser() && !$project->hasUserInUserAuthorised($this->getUser())) {
                    return new JsonResponse([
                        'state' => 'FO',
                        'value' => 'project',
                    ], Response::HTTP_FORBIDDEN);
                }
                if ($project->getState() == 'deleted') {
                    return new JsonResponse([
                        'state' => 'DD',
                        'value' => 'project',
                    ], Response::HTTP_NOT_FOUND);
                }
                $message = new Message();
                $message->setAuthorUser($this->getUser());

                if ($this->newMessage($message, $data['content'], $project)) {
                    return new JsonResponse([
                            'state' => 'OK',
                        ]
                        , Response::HTTP_OK);
                }
                return new JsonResponse([
                        'state' => 'ISE',
                        'value' => 'failed to send message'
                    ]
                    , Response::HTTP_OK);


            }
            return new JsonResponse(['state' => 'ND'], Response::HTTP_BAD_REQUEST);

        } catch (\Exception $exception) {
            $this->logService->createLog('ERROR', ' Internal Servor Error at |' . $exception->getFile() . ' | line |' . $exception->getLine());
            return new JsonResponse([

                    'state' => 'ISE',
                    'value' => ' Internal Servor Error : ' . $exception->getMessage() . ' at |' . $exception->getFile() . ' | line |' . $exception->getLine()

                ]
                , Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }

    #[Route('/api/delete/{id}/message', name: 'api_delete_message', methods: 'delete')]
    public function removeMessage(Request $request, EntityManagerInterface $manager, $id, MessageRepository $messageRepository): Response
    {
        try {
            $data = json_decode($request->getContent(), true);

            $message = $messageRepository->find($id);
            if (!$message) {
                return new JsonResponse([
                    'state' => 'NDF',
                    'value' => 'message',
                ], Response::HTTP_NOT_FOUND);
            }
            if ($message->getChat()->getProject()->getOwner() != $this->getUser() && !$message->getChat()->getProject()->hasUserInUserAuthorised($this->getUser())) {
                return new JsonResponse([
                    'state' => 'FO',
                    'value' => 'project',
                ], Response::HTTP_FORBIDDEN);
            }
            if ($message->getProject()->getState() == 'deleted') {
                return new JsonResponse([
                    'state' => 'DD',
                    'value' => 'project',
                ], Response::HTTP_NOT_FOUND);
            }
            if ($message->getAuthorUser() != $this->getUser()) {
                return new JsonResponse([
                    'state' => 'FO',
                    'value' => 'message',
                ], Response::HTTP_FORBIDDEN);
            }
            $array = $message->getChat()->getMessages();
            $messagesArray = $array->toArray();
            if (end($messagesArray)== $message) {
                return new JsonResponse([
                        'state' => 'ASFO',
                        'value' => 'message'
                    ]
                    , Response::HTTP_FORBIDDEN);
            }

            $manager->remove($message);
            $manager->flush();
            return new JsonResponse([
                    'state' => 'OK',
                ]
                , Response::HTTP_OK);


        } catch (\Exception $exception) {
            $this->logService->createLog('ERROR', ' Internal Servor Error at |' . $exception->getFile() . ' | line |' . $exception->getLine());
            return new JsonResponse([

                    'state' => 'ISE',
                    'value' => ' Internal Servor Error : ' . $exception->getMessage() . ' at |' . $exception->getFile() . ' | line |' . $exception->getLine()

                ]
                , Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }

    #[Route('/api/search/chat', name: 'search_chat', methods: 'get')]
    public function searchChat(Request $request, ChatRepository $chatRepository)
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
                    return new JsonResponse([
                            'state' => 'OK', 'value' => $dataToReturn
                        ]
                        , Response::HTTP_OK);
                }
            }

            return new JsonResponse(['state' => 'ND'], Response::HTTP_BAD_REQUEST);
        } catch (\Exception $exception) {
            $this->logService->createLog('ERROR', ' Internal Servor Error at |' . $exception->getFile() . ' | line |' . $exception->getLine());


            return new JsonResponse([

                    'state' => 'ISE',
                    'value' => ' Internal Servor Error : ' . $exception->getMessage() . ' at |' . $exception->getFile() . ' | line |' . $exception->getLine()

                ]
                , Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public
    function newMessage($message, $content, $project)
    {
        try {
            $message->setCreatedAt(new \DateTimeImmutable('Now'));
            $message->setContent($content);
            $message->setChat($project->getChat());
            $this->entityManager->persist($message);
            $this->entityManager->flush();
            return true;
        } catch (\Exception $exception) {
            $this->logService->createLog('ERROR', ' Internal Servor Error at |' . $exception->getFile() . ' | line |' . $exception->getLine());
            return false;
        }

    }
}
