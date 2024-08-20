<?php

namespace App\Controller;

use App\Entity\Category;
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

class ApiCategoryController extends AbstractController
{
    private LogService $logService;

    public function __construct(LogService $logService, DateService $dateService)
    {
        $this->logService = $logService;
    }


    #[Route('/api/category/new', name: 'new_category', methods: ['post'])]
    public function newCategory(EntityManagerInterface $entityManager, ProjectRepository $projectRepository, Request $request): Response
    {
        try {
            $data = json_decode($request->getContent(), true);
            if ($data) {
                $cat = new Category();

                if (!isset($data['name']) || empty(trim($data['name']))) {
                    return new JsonResponse(json_encode([
                        'state' => 'NED',
                        'value' => 'name',
                    ]), Response::HTTP_UNPROCESSABLE_ENTITY);
                }
                if (!isset($data['project_id'])) {
                    return new JsonResponse(json_encode([
                        'state' => 'NEF',
                        'value' => 'project_id',
                    ]), Response::HTTP_UNPROCESSABLE_ENTITY);
                }
                if (!is_numeric($data['project_id'])) {
                    return new JsonResponse(json_encode([
                        'state' => 'IDT',
                        'value' => 'project_id',
                    ]), Response::HTTP_UNPROCESSABLE_ENTITY);
                }
                $project = $projectRepository->find($data['project_id']);
                if (!$project) {
                    return new JsonResponse(json_encode([
                        'state' => 'NDF',
                        'value' => 'project',
                    ]), Response::HTTP_NOT_FOUND);
                }
                if ($project->getOwner() != $this->getUser() && !$project->hasUserInUserAuthorised($this->getUser())) {
                    return new JsonResponse(json_encode([
                        'state' => 'FO',
                        'value' => 'project',
                    ]), Response::HTTP_FORBIDDEN);
                }
                if ($project->getState() == 'deleted') {
                    return new JsonResponse(json_encode([
                        'state' => 'DD',
                        'value' => 'project',
                    ]), Response::HTTP_NOT_FOUND);
                }
                $cat->setName($data['name']);
                $cat->setProject($project);

                $entityManager->persist($cat);
                $entityManager->flush();

                $this->logService->createLog('ACTION', ' Create Category (' . $cat->getId() . ':' . $cat->getName() . ') for project : ' . $cat->getProject()->getName() . ' ) by ' . ($this->getUser()->getEmail()));


                return new JsonResponse(json_encode([
                    'state' => 'ok',
                    'value' => $this->getData($cat)
                ]), Response::HTTP_INTERNAL_SERVER_ERROR);

            }
            return new JsonResponse(json_encode(['state' => 'ND']), Response::HTTP_BAD_REQUEST);

        } catch (\Exception $exception) {
            $this->logService->createLog('ERROR', ' Internal Servor Error at |' . $exception->getFile() . ' | line |' . $exception->getLine());
            return new JsonResponse(json_encode([

                    'state' => 'ISE',
                    'value' => ' Internal Servor Error : ' . $exception->getMessage() . ' at |' . $exception->getFile() . ' | line |' . $exception->getLine()

                ]
            ), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/api/category/{id}/edit', name: 'edit_category', methods: ['put'])]
    public function editCategory(EntityManagerInterface $entityManager, $id, CategoryRepository $repository, Request $request): Response
    {
        try {
            $category = $repository->find($id);
            if (!$category) {
                return new JsonResponse(json_encode([
                    'state' => 'NDF',
                    'value' => 'category',
                ]), Response::HTTP_NOT_FOUND);
            }
            if ($category->getProject()->getOwner() != $this->getUser() && !$category->getProject()->hasUserInUserAuthorised($this->getUser())) {
                return new JsonResponse(json_encode([
                    'state' => 'FO',
                    'value' => 'project',
                ]), Response::HTTP_FORBIDDEN);

            }
            if ($category->getProject()->getState() == 'deleted') {
                return new JsonResponse(json_encode([
                    'state' => 'DD',
                    'value' => 'project',
                ]), Response::HTTP_NOT_FOUND);

            }
            $data = json_decode($request->getContent(), true);
            if ($data) {

                if (!isset($data['name']) || empty(trim($data['name']))) {
                    return new JsonResponse(json_encode([
                        'state' => 'NED',
                        'value' => 'name',
                    ]), Response::HTTP_UNPROCESSABLE_ENTITY);

                }

                $category->setName($data['name']);

                $entityManager->persist($category);
                $entityManager->flush();

                $this->logService->createLog('ACTION', ' Edit Category (' . $category->getId() . ':' . $category->getName() . ') for project : ' . $category->getProject()->getName() . ' ) by ' . ($this->getUser()->getEmail()));

                return new JsonResponse(json_encode([
                    'state' => 'OK',  'value' => $this->getData($category)
            ]), Response::HTTP_OK);

            }
            return new JsonResponse(json_encode(['state' => 'ND']), Response::HTTP_BAD_REQUEST);

        } catch (\Exception $exception) {
            $this->logService->createLog('ERROR', ' Internal Servor Error at |' . $exception->getFile() . ' | line |' . $exception->getLine());
            return new JsonResponse(json_encode([

                    'state' => 'ISE',
                    'value' => ' Internal Servor Error : ' . $exception->getMessage() . ' at |' . $exception->getFile() . ' | line |' . $exception->getLine()

                ]
            ), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    #[Route('/api/category/{id}/delete', name: 'delete_category', methods: 'delete')]
    public function delete($id, CategoryRepository $repository, EntityManagerInterface $manager, CategoryRepository $categoryRepository): Response
    {
        try {
            $category = $repository->find($id);
            if (!$category) {
                return new JsonResponse(json_encode([
                    'state' => 'NDF',
                    'value' => 'category',
                ]), Response::HTTP_NOT_FOUND);
            }
            if ($category->getProject()->getOwner() != $this->getUser() && !$category->getProject()->hasUserInUserAuthorised($this->getUser())) {
                return new JsonResponse(json_encode([
                    'state' => 'FO',
                    'value' => 'project',
                ]), Response::HTTP_FORBIDDEN);
            }
            if ($category->getProject()->getState() == 'deleted') {
                return new JsonResponse(json_encode([
                    'state' => 'DD',
                    'value' => 'project',
                ]), Response::HTTP_NOT_FOUND);
            }
            if (count($category->getTasks()) != 0) {

                return new JsonResponse(json_encode([
                        'state' => 'ASFO',
                        'value' => 'category'
                    ]
                ),Response::HTTP_UNPROCESSABLE_ENTITY);
            }
            $message = ' Delete Category (' . $category->getId() . ':' . $category->getName() . ') for project : ' . $category->getProject()->getName() . ' ) by ' . ($this->getUser()->getEmail());
            $manager->remove($category);
            $manager->flush();
            $this->logService->createLog('DELETE', $message);

            return new JsonResponse(json_encode([
                    'state' => 'OK',
                 ]
            ),Response::HTTP_OK);
        } catch (\Exception $exception) {
            $this->logService->createLog('ERROR', ' Internal Servor Error at |' . $exception->getFile() . ' | line |' . $exception->getLine());
            return new JsonResponse(json_encode([

                    'state' => 'ISE',
                    'value' => ' Internal Servor Error : ' . $exception->getMessage() . ' at |' . $exception->getFile() . ' | line |' . $exception->getLine()

                ]
            ), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/api/{id}/categories', name: 'get_categories', methods: 'get')]
    public function getCategories(ProjectRepository $projectRepository, $id, Request $request, EntityManagerInterface $manager): Response
    {
        try {
            $project = $projectRepository->find($id);
            if (!$project) {
                return new JsonResponse(json_encode([
                    'state' => 'NDF',
                    'value' => 'project',
                ]), Response::HTTP_NOT_FOUND);
            }
            if ($project->getOwner() != $this->getUser() && !$project->hasUserInUserAuthorised($this->getUser())) {
                return new JsonResponse(json_encode([
                    'state' => 'FO',
                    'value' => 'project',
                ]), Response::HTTP_FORBIDDEN);
            }
            if ($project->getState() == 'deleted') {
                return new JsonResponse(json_encode([
                    'state' => 'DD',
                    'value' => 'project',
                ]), Response::HTTP_NOT_FOUND);
            }
            $cats = $project->getCategories();
            $data = [];
            foreach ($cats as $cat) {
                $data[] = $this->getData($cat);
            }
            return new JsonResponse(json_encode([
                    'state' => 'OK',
                    'value'=>$data
                ]
            ),Response::HTTP_OK);
        } catch (\Exception $exception) {
            $this->logService->createLog('ERROR', ' Internal Servor Error at |' . $exception->getFile() . ' | line |' . $exception->getLine());
            return new JsonResponse(json_encode([

                    'state' => 'ISE',
                    'value' => ' Internal Servor Error : ' . $exception->getMessage() . ' at |' . $exception->getFile() . ' | line |' . $exception->getLine()

                ]
            ), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getData($cat)
    {

        return [
            'id' => $cat->getId(),
            'name' => $cat->getName(),
            'tasksNumber' => count($cat->getTasks()),

        ];
    }
}
