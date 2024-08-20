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
    public function newTask(ProjectRepository $projectRepository, Request $request, CategoryRepository $categoryRepository): Response
    {
        try {
            $data = json_decode($request->getContent(), true);
            if ($data) {
                $task = new Task();

                if (!isset($data['name']) || empty(trim($data['name']))) {
                    return $this->json([
                        'state' => 'NEF',
                        'value' => 'name',
                    ]);
                }
                if (!isset($data['project_id'])) {
                    return $this->json([
                        'state' => 'NEF',
                        'value' => 'project_id',
                    ]);
                }
                if (!is_numeric($data['project_id'])) {
                    return $this->json([
                        'state' => 'IDT',
                        'value' => 'project_id'
                    ]);
                }
                $project = $projectRepository->find($data['project_id']);
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
                $task->setProject($project);

                if (isset($data['category_id'])) {
                    if (!is_numeric($data['category_id'])) {
                        return $this->json([
                            'state' => 'IDT',
                            'value' => 'category_id'
                        ]);
                    }
                    $cat = $categoryRepository->find($data['category_id']);
                    if (!$cat) {
                        return $this->json([
                            'state' => 'NDF',
                            'value' => 'category'
                        ]);
                    }

                    if ($cat->getProject()->getOwner() != $this->getUser() && !$cat->getProject()->hasUserInUserAuthorised($this->getUser())) {
                        return $this->json([
                            'state' => 'FO',
                            'value' => 'category'
                        ]);
                    }
                    if ($cat->getProject()->getState() == 'deleted') {
                        return $this->json([
                            'state' => 'DD',
                            'value' => 'project'
                        ]);
                    }
                }

                if (isset($data['dueDate']) && !empty(trim($data['dueDate']))) {

                    $searchDate = \DateTime::createFromFormat('d/m/Y', $data['dueDate']);
                    if (!$searchDate) {
                        return $this->json([
                            'state' => 'IDT',
                            'value' => 'dueDate'
                        ]);
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

                return $this->json(['state' => 'OK',

                    'value' => $this->getData($task)]);

            }
            return $this->json(['state' => 'ND']);

        } catch (\Exception $exception) {
            $this->logService->createLog('ERROR', ' Internal Servor Error at |' . $exception->getFile() . ' | line |' . $exception->getLine());
            return $this->json(['state' => 'ISE',
                'value' => ' Internal Servor Error : ' . $exception->getMessage() . ' at |' . $exception->getFile() . ' | line |' . $exception->getLine()]);
        }
    }

    #[Route('/api/task/{id}/edit', name: 'edit_api_task', methods: ['PUT'])]
    public function editTask(EntityManagerInterface $entityManager, $id, Request $request, CategoryRepository $categoryRepository, TaskRepository $repository): Response
    {
        try {
            $task = $repository->find($id);
            if (!$task) {
                return $this->json([
                    'state' => 'NDF',
                    'value' => 'task'
                ]);
            }
            if ($task->getProject()->getOwner() != $this->getUser() && !$task->getProject()->hasUserInUserAuthorised($this->getUser())) {
                return $this->json([
                    'state' => 'FO',
                    'value' => 'project'
                ]);
            }

            if ($task->getProject()->getState() == 'deleted') {
                return $this->json([
                    'state' => 'DD',
                    'value' => 'project'
                ]);
            }
            $data = json_decode($request->getContent(), true);
            if ($data) {

                if (!isset($data['name']) || empty(trim($data['name']))) {
                    return $this->json([
                        'state' => 'NEF',
                        'value' => 'name',
                    ]);
                }

                if (isset($data['category_id'])) {
                    if (!is_numeric($data['category_id'])) {
                        return $this->json([
                            'state' => 'IDT',
                            'value' => 'category_id'
                        ]);
                    }
                    $cat = $categoryRepository->find($data['category_id']);
                    if (!$cat) {
                        return $this->json([
                            'state' => 'NDF',
                            'value' => 'category'
                        ]);
                    }

                    if ($cat->getProject()->getOwner() != $this->getUser() && !$cat->getProject()->hasUserInUserAuthorised($this->getUser())) {
                        return $this->json([
                            'state' => 'FO',
                            'value' => 'category'
                        ]);
                    }
                    if ($cat->getProject()->getState() == 'deleted') {
                        return $this->json([
                            'state' => 'DD',
                            'value' => 'project'
                        ]);
                    }
                    $task->setCategory($cat);
                }

                if (isset($data['dueDate']) && !empty(trim($data['dueDate']))) {

                    $searchDate = \DateTime::createFromFormat('d/m/Y', $data['dueDate']);
                    if (!$searchDate) {
                        return $this->json([
                            'state' => 'IDT',
                            'value' => 'dueDate'
                        ]);
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

                return $this->json(['state' => 'OK',

                    'value' => $this->getData($task)]);

            }
            return $this->json(['state' => 'ND']);

        } catch (\Exception $exception) {
            $this->logService->createLog('ERROR', ' Internal Servor Error at |' . $exception->getFile() . ' | line |' . $exception->getLine());
            return $this->json(['state' => 'ISE',
                'value' => ' Internal Servor Error : ' . $exception->getMessage() . ' at |' . $exception->getFile() . ' | line |' . $exception->getLine()]);
        }
    }

    #[Route('/api/task/{id}/edit/status', name: 'edit_status_api_task', methods: ['PUT'])]
    public function editTaskStatut(EntityManagerInterface $entityManager, $id, Request $request, CategoryRepository $categoryRepository, TaskRepository $repository): Response
    {
        try {
            $task = $repository->find($id);
            if (!$task) {
                return $this->json([
                    'state' => 'NDF',
                    'value' => 'task'
                ]);
            }
            if ($task->getProject()->getOwner() != $this->getUser() && !$task->getProject()->hasUserInUserAuthorised($this->getUser())) {
                return $this->json([
                    'state' => 'FO',
                    'value' => 'project'
                ]);
            }
            if ($task->getProject()->getState() == 'deleted') {
                return $this->json([
                    'state' => 'DD',
                    'value' => 'project'
                ]);
            }

            $data = json_decode($request->getContent(), true);
            if ($data) {

                if (!isset($data['status']) || empty(trim($data['status']))) {
                    return $this->json([
                        'state' => 'NEF',
                        'value' => 'status',
                    ]);
                }
                if (!in_array($data['status'], $this->statusArray)) {
                    return $this->json([
                        'state' => 'IDV',
                        'value' => 'status',
                    ]);
                }


                $task->setCol($data['status']);

                $entityManager->persist($task);
                $entityManager->flush();

                $this->logService->createLog('ACTION', ' Edit Status of Task (' . $task->getId() . ':' . $task->getName() . ') for project : ' . $task->getProject()->getName() . ' ), action by ' . $this->getUser()->getEmail());

                return $this->json(['state' => 'OK',

                    'value' => $this->getData($task)]);

            }
            return $this->json(['state' => 'ND']);

        } catch (\Exception $exception) {
            $this->logService->createLog('ERROR', ' Internal Servor Error at |' . $exception->getFile() . ' | line |' . $exception->getLine());
            return $this->json(['state' => 'ISE',
                'value' => ' Internal Servor Error : ' . $exception->getMessage() . ' at |' . $exception->getFile() . ' | line |' . $exception->getLine()]);
        }
    }

    #[Route('/api/task/{id}/edit/order', name: 'edit_order_api_task', methods: ['PUT'])]
    public function editTaskOrder(EntityManagerInterface $entityManager, $id, Request $request, CategoryRepository $categoryRepository, TaskRepository $repository): Response
    {
        try {
            $task = $repository->find($id);
            if (!$task) {
                return $this->json([
                    'state' => 'NDF',
                    'value' => 'task'
                ]);
            }
            if ($task->getProject()->getOwner() != $this->getUser() && !$task->getProject()->hasUserInUserAuthorised($this->getUser())) {
                return $this->json([
                    'state' => 'FO',
                    'value' => 'project'
                ]);
            }
            if ($task->getProject()->getState() == 'deleted') {
                return $this->json([
                    'state' => 'DD',
                    'value' => 'project'
                ]);
            }

            $data = json_decode($request->getContent(), true);
            if ($data) {

                if (!isset($data['order'])) {
                    return $this->json([
                        'state' => 'NEF',
                        'value' => 'order',
                    ]);
                }
                if (filter_var($data['order'], FILTER_VALIDATE_INT) === false || $data['order'] <= 0) {
                    return $this->json([
                        'state' => 'IDT',
                        'value' => 'order',
                    ]);
                }

                $tasks = $this->taskRepository->findBy(['project' => $task->getProject(), 'col' => $task->getCol()]);

                if ($data['order'] > count($tasks)) {
                    return $this->json([
                        'state' => 'IDV',
                        'value' => 'order',
                    ]);
                }

                $this->reorderTask($task->getProject(), $task->getCol(), $task, $data['order']);


                return $this->json(['state' => 'OK',

                    'value' => $this->getData($task)]);

            }
            return $this->json(['state' => 'ND']);

        } catch (\Exception $exception) {
            $this->logService->createLog('ERROR', ' Internal Servor Error at |' . $exception->getFile() . ' | line |' . $exception->getLine());
            return $this->json(['state' => 'ISE',
                'value' => ' Internal Servor Error : ' . $exception->getMessage() . ' at |' . $exception->getFile() . ' | line |' . $exception->getLine()]);
        }
    }

    public function reorderTask($project, $col, $taskElement, $newOrder)
    {
        try {
            $tasks = $this->taskRepository->findBy(['project' => $project, 'col' => $col]);
            $currentOrder = $taskElement->getTaskOrder();

            dump($tasks);
            if ($newOrder == 0) {
                foreach ($tasks as $task) {
                    if ($task != $taskElement ) {
                        $task->setTaskOrder($task->getTaskOrder() + 1);
                        $this->entityManager->persist($task);
                    }
                }

            }
            if ($newOrder > $currentOrder) {
                foreach ($tasks as $task) {
                    if ($task != $taskElement && $task->getTaskOrder() > $currentOrder && $task->getTaskOrder() <= $newOrder) {
                        $task->setTaskOrder($task->getTaskOrder() - 1);
                        $this->entityManager->persist($task);
                    }
                }
            } // Si le nouvel ordre est plus petit que l'ordre actuel
            elseif ($newOrder < $currentOrder) {
                foreach ($tasks as $task) {
                    if ($task != $taskElement && $task->getTaskOrder() >= $newOrder && $task->getTaskOrder() < $currentOrder) {
                        $task->setTaskOrder($task->getTaskOrder() + 1);
                        $this->entityManager->persist($task);
                    }
                }
            }


            $taskElement->setTaskOrder($newOrder);
            $this->entityManager->persist($taskElement);
            dd($taskElement);
            $this->entityManager->flush();
            return Null;
        } catch (\Exception $exception) {
            $this->logService->createLog('ERROR', ' Internal Servor Error at |' . $exception->getFile() . ' | line |' . $exception->getLine());
            return $this->json(['state' => 'ISE',
                'value' => ' Internal Servor Error : ' . $exception->getMessage() . ' at |' . $exception->getFile() . ' | line |' . $exception->getLine()]);
        }
    }

    #[Route('/api/task/{id}/delete', name: 'delete_task', methods: 'delete')]
    public function delete($id, TaskRepository $repository, EntityManagerInterface $manager): Response
    {
        try {
            $task = $repository->find($id);
            if (!$task) {
                return $this->json([
                    'state' => 'NDF',
                    'value' => 'task'
                ]);
            }
            if ($task->getProject()->getOwner() != $this->getUser() && !$task->getProject()->hasUserInUserAuthorised($this->getUser())) {
                return $this->json([
                    'state' => 'FO',
                    'value' => 'project'
                ]);
            }
            if ($task->getProject()->getState() == 'deleted') {
                return $this->json([
                    'state' => 'DD',
                    'value' => 'project'
                ]);
            }
            $message = ' Delete task (' . $task->getId() . ':' . $task->getName() . ') of project : (' . $task->getProject()->getName() . ' ), action by ' . $this->getUser()->getEmail();
            $manager->remove($task);
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

    #[Route('/api/task/{id}/get', name: 'get_task', methods: 'get')]
    public function getTask($id, TaskRepository $repository, EntityManagerInterface $manager): Response
    {
        try {
            $task = $repository->find($id);
            if (!$task) {
                return $this->json([
                    'state' => 'NDF',
                    'value' => 'task'
                ]);
            }
            if ($task->getProject()->getOwner() != $this->getUser() && !$task->getProject()->hasUserInUserAuthorised($this->getUser())) {
                return $this->json([
                    'state' => 'FO',
                    'value' => 'project'
                ]);
            }
            if ($task->getProject()->getState() == 'deleted') {
                return $this->json([
                    'state' => 'DD',
                    'value' => 'project'
                ]);
            }
            return $this->json([
                'state' => 'OK',
                'value' => $this->getData($task)
            ]);
        } catch (\Exception $exception) {
            $this->logService->createLog('ERROR', ' Internal Servor Error at |' . $exception->getFile() . ' | line |' . $exception->getLine());


            return $this->json([
                'state' => 'ISE',
                'value' => ' Internal Servor Error : ' . $exception->getMessage() . ' at |' . $exception->getFile() . ' | line |' . $exception->getLine()

            ]);
        }
    }

    #[Route('/api/{id}/tasks', name: 'get_tasks', methods: 'get')]
    public function getTasks(ProjectRepository $projectRepository, $id, Request $request, EntityManagerInterface $manager): Response
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
            $tasks = $project->getTasks();
            $data = [
                'waiting' => [],
                'progress' => [],
                'done' => []
            ];
            foreach ($tasks as $task) {
                $data[$task->getCol()][] = $this->getData($task);
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

    public function getData($task)
    {
        $categorName = null;
        if ($task->getCategory() != null) {
            $categorName = $task->getCategory()->getName();
        }
        return [
            'id' => $task->getId(),
            'name' => $task->getName(),
            'content' => $task->getDescription(),
            'category' => $categorName,
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
