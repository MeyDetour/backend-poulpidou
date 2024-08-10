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
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ApiCategoryController extends AbstractController
{ private LogService $logService;

    public function __construct(LogService $logService ,DateService $dateService)
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
                if($project->getState() == 'deleted'){
                    return $this->json([
                        'state' => 'DD',
                        'value' => 'project'
                    ]);
                }
                $cat->setName($data['name']);
                $cat->setProject($project);

                $entityManager->persist($cat);
                $entityManager->flush();

                $this->logService->createLog('ACTION', ' Create Category (' . $cat->getId() . ':' . $cat->getName() . ') for project : ' . $cat->getProject()->getName() . ' ) by '.($this->getUser()->getEmail()));

                return $this->json(['state' => 'OK',

                    'value' => $this->getData($cat)]);

            }
            return $this->json(['state' => 'ND']);

        } catch (\Exception $exception) {
            $this->logService->createLog('ERROR', ' Internal Servor Error at |' . $exception->getFile() . ' | line |' . $exception->getLine());
            return $this->json(['state' => 'ISE',
                'value' => ' Internal Servor Error : ' . $exception->getMessage() . ' at |' . $exception->getFile() . ' | line |' . $exception->getLine()]);
        }
    }
    #[Route('/api/category/{id}/edit', name: 'edit_category', methods: ['put'])]
    public function editCategory(EntityManagerInterface $entityManager,$id , CategoryRepository $repository, Request $request): Response
    {
        try {
            $category = $repository->find($id);
            if (!$category) {
                return $this->json([
                    'state' => 'NDF',
                    'value' => 'category'
                ]);
            }
            if ($category->getProject()->getOwner() != $this->getUser() && !$category->getProject()->hasUserInUserAuthorised($this->getUser())) {
                return $this->json([
                    'state' => 'FO',
                    'value' => 'project'
                ]);
            }
            if($category->getProject()->getState() == 'deleted'){
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

                $category->setName($data['name']);

                $entityManager->persist($category);
                $entityManager->flush();

                $this->logService->createLog('ACTION', ' Edit Category (' . $category->getId() . ':' . $category->getName() . ') for project : ' . $category->getProject()->getName() . ' ) by '.($this->getUser()->getEmail()));

                return $this->json(['state' => 'OK',

                    'value' => $this->getData($category)]);

            }
            return $this->json(['state' => 'ND']);

        } catch (\Exception $exception) {
            $this->logService->createLog('ERROR', ' Internal Servor Error at |' . $exception->getFile() . ' | line |' . $exception->getLine() );
            return $this->json(['state' => 'ISE',
                'value' => ' Internal Servor Error : ' . $exception->getMessage() . ' at |' . $exception->getFile() . ' | line |' . $exception->getLine()]);
        }
    }


    #[Route('/api/category/{id}/delete', name: 'delete_category', methods: 'delete')]
    public function delete($id, CategoryRepository $repository, EntityManagerInterface $manager, CategoryRepository $categoryRepository ): Response
    {
        try {
            $category = $repository->find($id);
            if (!$category) {
                return $this->json([
                    'state' => 'NDF',
                    'value' => 'category'
                ]);
            }
            if ($category->getProject()->getOwner() != $this->getUser() && !$category->getProject()->hasUserInUserAuthorised($this->getUser())) {
                return $this->json([
                    'state' => 'FO',
                    'value' => 'project'
                ]);
            }
            if($category->getProject()->getState() == 'deleted'){
                return $this->json([
                    'state' => 'DD',
                    'value' => 'project'
                ]);
            }
            if(count($category->getTasks())!=0){
                return $this->json([
                    'state' => 'ASFO',
                    'value' => 'category'
                ]);
            }
            $message = ' Delete Category (' . $category->getId() . ':' . $category->getName() . ') for project : ' . $category->getProject()->getName() . ' ) by '.($this->getUser()->getEmail());
            $manager->remove($category);
            $manager->flush();
            $this->logService->createLog('DELETE', $message);

            return $this->json([
                'state' => 'OK'
            ]);
        } catch (\Exception $exception) {
            $this->logService->createLog('ERROR', ' Internal Servor Error at |' . $exception->getFile() . ' | line |' . $exception->getLine() );


            return $this->json([
                'state' => 'ISE',
                'value' => ' Internal Servor Error : ' . $exception->getMessage() . ' at |' . $exception->getFile() . ' | line |' . $exception->getLine()

            ]);
        }
    }
    #[Route('/api/{id}/categories', name: 'get_categories', methods: 'get')]
    public function getCategories(ProjectRepository $projectRepository, $id, Request $request, EntityManagerInterface $manager): Response
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
            if($project->getState() == 'deleted'){
                return $this->json([
                    'state' => 'DD',
                    'value' => 'project'
                ]);
            }
            $cats = $project->getCategories();
            $data = [];
            foreach ($cats as $cat) {
                $data[] = $this->getData($cat);
            }
            return $this->json([
                'state' => 'OK',
                'value' => $data
            ]);
        } catch (\Exception $exception) {
            $this->logService->createLog('ERROR', ' Internal Servor Error at |' . $exception->getFile() . ' | line |' . $exception->getLine() );


            return $this->json([
                'state' => 'ISE',
                'value' => ' Internal Servor Error : ' . $exception->getMessage() . ' at |' . $exception->getFile() . ' | line |' . $exception->getLine()

            ]);
        }
    }

    public function getData($cat)
    {

        return [
            'id' => $cat->getId(),
            'name' => $cat->getName(),
            'tasksNumber'=>count( $cat->getTasks()),

        ];
    }
}
