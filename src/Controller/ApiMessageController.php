<?php

namespace App\Controller;

use App\Entity\Message;
use App\Repository\ChatRepository;
use App\Repository\ClientRepository;
use App\Repository\ProjectRepository;
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

    public function __construct(LogService $logService, EntityManagerInterface $entityManager)
    {
        $this->logService = $logService;
        $this->entityManager = $entityManager;
    }

    #[Route('/api/chats', name: 'api_get_chats', methods: ['get'])]
    public function getChats(Request $request, EntityManagerInterface $manager, ChatRepository $chatRepository): Response
    {
        try {
            $data = [];
            foreach ($chatRepository->findAll() as $chat) {

                if ($chat->getProject()->getOwner() == $this->getUser() || $chat->getProject()->hasUserInUserAuthorised($this->getUser())) {
                    $data[] = $this->chatDataShortData($chat);
                }
            }
            return $this->json($data);

        } catch
        (\Exception $exception) {
            $this->logService->createLog('ERROR', ' Internal Servor Error at |' . $exception->getFile() . ' | line |' . $exception->getLine(), $exception->getMessage());


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
            return $this->json($this->chatData($chat));

        } catch
        (\Exception $exception) {
            $this->logService->createLog('ERROR', ' Internal Servor Error at |' . $exception->getFile() . ' | line |' . $exception->getLine(), $exception->getMessage());


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
                'datetime' => $message->getCreatedAt()->format('Y-m-d H:i'),
                'author' => $authorData,
                'type' => $type
            ];
        }

        return [
            'state' => 'OK',
            'value' => [
                'id' => $chat->getId(),
                'name' => $chat->getName(),
                'date' => $chat->getCreatedAt()->format('d-m-Y'),
                'project_id'=>$chat->getProject()->getId(),
                'project_uuid'=>$chat->getProject()->getUuid(),
                'client' => [
                    'id' => $client->getId(),
                    'firstName' => $client->getFirstName(),
                    'lastName' => $client->getLastName(),
                    'online' => $client->isOnline(),
                    'date' => $client->getCreatedAt()->format('d-m-Y'),
                    'projectNumber' => count($client->getProjects()),
                ],
                'messages' => $formattedMessages

            ]

        ];
    }

    public function chatDataShortData($chat)
    {


        return [
            'state' => 'OK',
            'value' => [
                'id' => $chat->getId(),
                'name' => $chat->getName(),
                'date' => $chat->getCreatedAt()->format('d-m-Y'),
                'client' => [
                    'id' => $chat->getClient()->getId(),
                    'firstName' => $chat->getClient()->getFirstName(),
                    'lastName' => $chat->getClient()->getLastName(),

                ],

            ]

        ];
    }

    #[Route('/message', name: 'new_message', methods: ['post'])]
    public function index(Request $request, EntityManagerInterface $manager, ClientRepository $clientRepository, ProjectRepository $projectRepository): Response
    {
        try {
            $data = json_decode($request->getContent(), true);
            if ($data) {

                //verifying if needed data are set
                if (!isset($data['id'])|| empty(trim($data['id']))) {
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
            $this->logService->createLog('ERROR', ' Internal Servor Error at |' . $exception->getFile() . ' | line |' . $exception->getLine(), $exception->getMessage());


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
                } if ($project->getOwner() != $this->getUser() && !$project->hasUserInUserAuthorised($this->getUser())) {
                    return $this->json([
                        'state' => 'FO',
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
            $this->logService->createLog('ERROR', ' Internal Servor Error at |' . $exception->getFile() . ' | line |' . $exception->getLine(), $exception->getMessage());
            return $this->json([
                'state' => 'ISE',
                'value' => ' Internal Servor Error : ' . $exception->getMessage() . ' at |' . $exception->getFile() . ' | line |' . $exception->getLine()

            ]);
        }

    }

    public function newMessage($message, $content, $project)
    {
        try {
            $message->setCreatedAt(new \DateTimeImmutable());
            $message->setContent($content);
            $message->setAuthorUser($this->getUser());
            $message->setChat($project->getChat());
            $this->entityManager->persist($message);
            $this->entityManager->flush();
            return true;
        } catch (\Exception $exception) {
            $this->logService->createLog('ERROR', ' Internal Servor Error at |' . $exception->getFile() . ' | line |' . $exception->getLine(), $exception->getMessage());
            return false;
        }

    }
}
