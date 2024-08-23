<?php

namespace App\Controller;

use App\Entity\Task;
use App\Repository\CategoryRepository;
use App\Repository\ProjectRepository;
use App\Repository\TaskRepository;
use App\Service\DateService;
use App\Service\LogService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ApiTaskController extends AbstractController
{
    private LogService $logService;
    private DateService $dateService;
    private TaskRepository $taskRepository;
    private EntityManagerInterface $entityManager;
    private $statusArray = ['waiting', 'progress', 'done'];

    public function __construct(LogService $logService, TaskRepository $repository, DateService $dateService, EntityManagerInterface $entityManager)
    {
        $this->taskRepository = $repository;
        $this->logService = $logService;
        $this->dateService = $dateService;
        $this->entityManager = $entityManager;
    }

    #[Route('/api/task/new', name: 'new_api_task', methods: ['post'])]
    public function newTask(ProjectRepository $projectRepository, Request $request): Response
    {
        try {
            $data = json_decode($request->getContent(), true);
            if ($data) {
                $task = new Task();

                if (!isset($data['name']) || empty(trim($data['name']))) {
                    return new JsonResponse([
                        'state' => 'NED',
                        'value' => 'name',
                    ], Response::HTTP_UNPROCESSABLE_ENTITY);
                }
                if (!isset($data['project_id'])) {
                    return new JsonResponse([
                        'state' => 'NED',
                        'value' => 'project_id',
                    ], Response::HTTP_UNPROCESSABLE_ENTITY);
                }
                if (!is_numeric($data['project_id'])) {
                    return new JsonResponse([
                        'state' => 'IDT',
                        'value' => 'project_id',
                    ], Response::HTTP_UNPROCESSABLE_ENTITY);
                }
                $project = $projectRepository->find($data['project_id']);
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
                $task->setProject($project);

                if (isset($data['category']) && !empty(trim($data['category']))) {
                    $task->setCategory($data['category']);
                }

                if (isset($data['dueDate']) && !empty(trim($data['dueDate']))) {

                    $searchDate = \DateTime::createFromFormat('d/m/Y', $data['dueDate']);
                    if (!$searchDate) {
                        return new JsonResponse([
                            'state' => 'IDT',
                            'value' => 'dueDate',
                        ], Response::HTTP_UNPROCESSABLE_ENTITY);
                    }
                    $task->setDueDate($searchDate);
                }


                if (isset($data['content']) && !empty(trim($data['content']))) {

                    $task->setDescription($data['content']);
                }


                $task->setName($data['name']);
                $task->setOwner($this->getUser());
                $task->setCol('waiting');
                $task->setTaskOrder(0);


                $this->entityManager->persist($task);
                $this->entityManager->flush();

                $this->reorderTask($project, 'waiting', $task, 0);

                $this->logService->createLog('ACTION', ' Create Task (' . $task->getId() . ':' . $task->getName() . ') for project : ' . $task->getProject()->getName() . ' ), action by ' . $this->getUser()->getEmail());


                return new JsonResponse([
                        'state' => 'OK', 'value' => $this->getData($task)
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

    #[Route('/api/task/{id}/edit', name: 'edit_api_task', methods: ['PUT'])]
    public function editTask(EntityManagerInterface $entityManager, $id, Request $request, TaskRepository $repository): Response
    {
        try {
            $task = $repository->find($id);
            if (!$task) {
                return new JsonResponse([
                    'state' => 'NDF',
                    'value' => 'task',
                ], Response::HTTP_NOT_FOUND);
            }
            if ($task->getProject()->getOwner() != $this->getUser() && !$task->getProject()->hasUserInUserAuthorised($this->getUser())) {
                return new JsonResponse([
                    'state' => 'FO',
                    'value' => 'project',
                ], Response::HTTP_FORBIDDEN);
            }

            if ($task->getProject()->getState() == 'deleted') {
                return new JsonResponse([
                    'state' => 'DD',
                    'value' => 'project',
                ], Response::HTTP_NOT_FOUND);
            }
            $data = json_decode($request->getContent(), true);
            if ($data) {

                if (!isset($data['name']) || empty(trim($data['name']))) {
                    return new JsonResponse([
                        'state' => 'NED',
                        'value' => 'name',
                    ], Response::HTTP_UNPROCESSABLE_ENTITY);
                }

                if (isset($data['category']) || !empty(trim($data['category']))) {
                    $task->setCategory($data['category']);
                }
                if (isset($data['dueDate']) && !empty(trim($data['dueDate']))) {

                    $searchDate = \DateTime::createFromFormat('d/m/Y', $data['dueDate']);
                    if (!$searchDate) {
                        return new JsonResponse([
                            'state' => 'IDT',
                            'value' => 'dueDate',
                        ], Response::HTTP_UNPROCESSABLE_ENTITY);
                    }
                    $task->setDueDate($searchDate);
                }


                if (isset($data['content']) && !empty(trim($data['content']))) {

                    $task->setDescription($data['content']);
                }


                $task->setName($data['name']);
                $task->setOwner($this->getUser());

                $entityManager->persist($task);
                $entityManager->flush();


                $this->logService->createLog('ACTION', ' Edit Task (' . $task->getId() . ':' . $task->getName() . ') for project : ' . $task->getProject()->getName() . ' ), action by ' . $this->getUser()->getEmail());

                return new JsonResponse([
                        'state' => 'OK', 'value' => $this->getData($task)
                    ]
                    , Response::HTTP_OK);

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

    #[Route('/api/task/{id}/edit/status', name: 'edit_status_api_task', methods: ['PUT'])]
    public function editTaskStatut(EntityManagerInterface $entityManager, $id, Request $request, TaskRepository $repository): Response
    {
        try {
            $task = $repository->find($id);
            if (!$task) {
                return new JsonResponse([
                    'state' => 'NDF',
                    'value' => 'task',
                ], Response::HTTP_NOT_FOUND);
            }
            if ($task->getProject()->getOwner() != $this->getUser() && !$task->getProject()->hasUserInUserAuthorised($this->getUser())) {
                return new JsonResponse([
                    'state' => 'FO',
                    'value' => 'project',
                ], Response::HTTP_FORBIDDEN);
            }
            if ($task->getProject()->getState() == 'deleted') {
                return new JsonResponse([
                    'state' => 'DD',
                    'value' => 'project',
                ], Response::HTTP_NOT_FOUND);
            }
            $lastColumnStatus = $task->getStatus();
            $data = json_decode($request->getContent(), true);
            if ($data) {

                if (!isset($data['status']) || empty(trim($data['status']))) {
                    return new JsonResponse([
                        'state' => 'NED',
                        'value' => 'name',
                    ], Response::HTTP_UNPROCESSABLE_ENTITY);
                }
                if (!in_array($data['status'], $this->statusArray)) {

                    return new JsonResponse([
                            'state' => 'IDV',
                            'value' => 'status',
                        ]
                        , Response::HTTP_UNPROCESSABLE_ENTITY);
                }


                $task->setCol($data['status']);
                $task->setTaskOrder(0);


                $entityManager->persist($task);
                $entityManager->flush();
                $this->reorderTask($task->getProject(), $data['status'], $task, 0);
                $this->reorderTaskInColumn($task->getProject(), $lastColumnStatus);
                $this->logService->createLog('ACTION', ' Edit Status of Task (' . $task->getId() . ':' . $task->getName() . ') for project : ' . $task->getProject()->getName() . ' ), action by ' . $this->getUser()->getEmail());

                return new JsonResponse([
                        'state' => 'OK', 'value' => $this->getData($task)
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

    #[Route('/api/task/{id}/edit/order', name: 'edit_order_api_task', methods: ['PUT'])]
    public function editTaskOrder(EntityManagerInterface $entityManager, $id, Request $request, TaskRepository $repository): Response
    {
        try {
            $task = $repository->find($id);
            if (!$task) {
                return new JsonResponse([
                    'state' => 'NDF',
                    'value' => 'task',
                ], Response::HTTP_NOT_FOUND);
            }
            if ($task->getProject()->getOwner() != $this->getUser() && !$task->getProject()->hasUserInUserAuthorised($this->getUser())) {
                return new JsonResponse([
                    'state' => 'FO',
                    'value' => 'project',
                ], Response::HTTP_FORBIDDEN);
            }
            if ($task->getProject()->getState() == 'deleted') {
                return new JsonResponse([
                    'state' => 'DD',
                    'value' => 'project',
                ], Response::HTTP_NOT_FOUND);
            }

            $data = json_decode($request->getContent(), true);
            if ($data) {

                if (!isset($data['order'])) {
                    return new JsonResponse([
                        'state' => 'NED',
                        'value' => 'name',
                    ], Response::HTTP_UNPROCESSABLE_ENTITY);
                }
                if (filter_var($data['order'], FILTER_VALIDATE_INT) === false || $data['order'] < 0) {
                    return new JsonResponse([
                        'state' => 'IDT',
                        'value' => 'order',
                    ], Response::HTTP_UNPROCESSABLE_ENTITY);
                }

                $tasks = $this->taskRepository->findBy(['project' => $task->getProject(), 'col' => $task->getCol()]);

                if ($data['order'] > count($tasks) - 1) {

                    return new JsonResponse([
                            'state' => 'IDV',
                            'value' => 'order',
                        ]
                        , Response::HTTP_UNPROCESSABLE_ENTITY);
                }

                $this->reorderTask($task->getProject(), $task->getCol(), $task, $data['order']);


                return new JsonResponse([
                        'state' => 'OK', 'value' => $this->getData($task)
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

    public function reorderTask($project, $col, $taskElement, $newOrder)
    {
        try {
            $tasks = $this->taskRepository->findBy(['project' => $project, 'col' => $col], ['taskOrder' => 'ASC']);

            $finalArray = [];
            foreach ($tasks as $task) {
                if ($task != $taskElement) {
                    $finalArray[] = $task;
                }
            }
            array_splice($finalArray, $newOrder, 0, $taskElement);

            foreach ($tasks as $key => $task) {
                $task->setTaskOrder($key);
                $this->entityManager->persist($task);
            }
            $this->entityManager->flush();
            return Null;
        } catch (\Exception $exception) {
            $this->logService->createLog('ERROR', ' Internal Servor Error at |' . $exception->getFile() . ' | line |' . $exception->getLine());
            return new JsonResponse([

                    'state' => 'ISE',
                    'value' => ' Internal Servor Error : ' . $exception->getMessage() . ' at |' . $exception->getFile() . ' | line |' . $exception->getLine()

                ]
                , Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function reorderTaskInColumn($project, $col)
    {
        try {
            $tasks = $this->taskRepository->findBy(['project' => $project, 'col' => $col], ['taskOrder' => 'ASC']);
            $tasks =  (array) $tasks;
            foreach ($tasks as $key => $task) {
                dump($key);
                $task->setTaskOrder($key);
                $this->entityManager->persist($task);
            }
            $this->entityManager->flush();
            dd("ok");
            return Null;
        } catch (\Exception $exception) {
            $this->logService->createLog('ERROR', ' Internal Servor Error at |' . $exception->getFile() . ' | line |' . $exception->getLine());
            return new JsonResponse([

                    'state' => 'ISE',
                    'value' => ' Internal Servor Error : ' . $exception->getMessage() . ' at |' . $exception->getFile() . ' | line |' . $exception->getLine()

                ]
                , Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/api/task/{id}/delete', name: 'delete_task', methods: 'delete')]
    public function delete($id, TaskRepository $repository, EntityManagerInterface $manager): Response
    {
        try {
            $task = $repository->find($id);
            if (!$task) {
                return new JsonResponse([
                    'state' => 'NDF',
                    'value' => 'task',
                ], Response::HTTP_NOT_FOUND);
            }
            if ($task->getProject()->getOwner() != $this->getUser() && !$task->getProject()->hasUserInUserAuthorised($this->getUser())) {
                return new JsonResponse([
                    'state' => 'FO',
                    'value' => 'project',
                ], Response::HTTP_FORBIDDEN);
            }
            if ($task->getProject()->getState() == 'deleted') {
                return new JsonResponse([
                    'state' => 'DD',
                    'value' => 'project',
                ], Response::HTTP_NOT_FOUND);
            }
            $message = ' Delete task (' . $task->getId() . ':' . $task->getName() . ') of project : (' . $task->getProject()->getName() . ' ), action by ' . $this->getUser()->getEmail();
            $manager->remove($task);
            $manager->flush();
            $this->logService->createLog('DELETE', $message);

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

    #[Route('/api/task/{id}/get', name: 'get_task', methods: 'get')]
    public function getTask($id, TaskRepository $repository, EntityManagerInterface $manager): Response
    {
        try {
            $task = $repository->find($id);
            if (!$task) {
                return new JsonResponse([
                    'state' => 'NDF',
                    'value' => 'task',
                ], Response::HTTP_NOT_FOUND);
            }
            if ($task->getProject()->getOwner() != $this->getUser() && !$task->getProject()->hasUserInUserAuthorised($this->getUser())) {
                return new JsonResponse([
                    'state' => 'FO',
                    'value' => 'project',
                ], Response::HTTP_FORBIDDEN);
            }
            if ($task->getProject()->getState() == 'deleted') {
                return new JsonResponse([
                    'state' => 'DD',
                    'value' => 'project',
                ], Response::HTTP_NOT_FOUND);
            }

            return new JsonResponse([
                    'state' => 'OK', 'value' => $this->getData($task)
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

    #[Route('/api/project/{id}/tasks', name: 'get_tasks', methods: 'get')]
    public function getTasks(ProjectRepository $projectRepository, $id, Request $request, EntityManagerInterface $manager, TaskRepository $taskRepository): Response
    {
        try {
            $project = $projectRepository->find($id);
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
            $tasksWaiting = $taskRepository->findBy(['project' => $project, "col" => "waiting"], ['taskOrder' => 'ASC']);
            $tasksProgress = $taskRepository->findBy(['project' => $project, "col" => "progress"], ['taskOrder' => 'ASC']);
            $tasksDone = $taskRepository->findBy(['project' => $project, "col" => "done"], ['taskOrder' => 'ASC']);
            $this->reorderTaskInColumn($project, "waiting");
            $this->reorderTaskInColumn($project, "progress");
            $this->reorderTaskInColumn($project, "done");

            $data = [
                'waiting' => $tasksWaiting,
                'progress' => $tasksProgress,
                'done' => $tasksDone
            ];


            return new JsonResponse(
                [
                    'state' => 'OK',
                    'value' => $data
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

    public function getData($task)
    {

        return [
            'id' => $task->getId(),
            'name' => $task->getName(),
            'content' => $task->getDescription(),
            'category' => $task->getCategory(),
            'status' => $task->getCol(),
            'order' => $task->getTaskOrder(),
            'dueDate' => $this->dateService->formateDate($task->getDueDate()),
            'author' => [
                'firstName' => $task->getOwner()->getFirstName(),
                'lastName' => $task->getOwner()->getLastName(),
                'email' => $task->getOwner()->getEmail()
            ]
        ];
    }


}
