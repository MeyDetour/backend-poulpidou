<?php

namespace App\Controller;

use App\Entity\Project;
use App\Repository\ClientRepository;
use App\Repository\ProjectRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ApiProjectController extends AbstractController
{
    #[Route('/api/project/new', name: 'app_api_project', methods: 'post')]
    public function index(Request $request, EntityManagerInterface $manager, ClientRepository $clientRepository): Response
    {

        $data = json_decode($request->getContent(), true);

        if ($data) {
            $project = new Project();


            if (!isset($data['client_id']) || empty(trim($data['client_id']))) {
                return $this->json([
                    'state' => 'NED',
                    'value' => 'client_id'
                ]);
            }

            if (isset($data['name']) && !empty(trim($data['name']))) {

                return $this->json([
                    'state' => 'NED',
                    'value' => 'name'
                ]);
            }

            $client = $clientRepository->find($data['client_id']);
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

            $project->setClient($client);
            $project->setName($data['name']);

            if (isset($data['figma_link']) && !empty(trim($data['figma_link']))) {
                $project->setFigmaLink($data['figma_link']);
            }
            if (isset($data['github_link']) && !empty(trim($data['github_link']))) {
                $project->setGithubLink($data['github_link']);
            }

            if (isset($data['start_date']) && !empty(trim($data['start_date']))) {
                $project->setStartDate($data['start_date']);
            }
            if (isset($data['end_date']) && !empty(trim($data['end_date']))) {
                $project->setEndDate($data['end_date']);
            }
            if (isset($data['total_price']) && !empty(trim($data['total_price']))) {
                $isValid = $data['total_price'] > 0 && is_numeric($data['total_price']);
                if(!$isValid){
                    return $this->json([
                        'state' => 'IDT',
                        'value' => 'total_price'
                    ]);
                }
                $project->setTotalPrice($data['total_price']);
            }
            $project->setCreatedAt(new \DateTimeImmutable());
            $project->setOwner($this->getUser());
            $project->setState('active');
            $manager->persist($project);
            $manager->flush();

            return $this->json([
                'state' => 'OK',
                'value' => $this->json($this->getDataProject($project))
            ]);
        }
        return $this->json([
            'state' => 'ND'
        ]);

    }

    #[Route('/api/project/edit/{id}', name: 'edit_api_project')]
    public function edit($id, ProjectRepository $repository, ClientRepository $clientRepository, Request $request, EntityManagerInterface $manager): Response
    {
        $project = $repository->find($id);
        if (!$project) {
            return $this->json([
                'state' => 'NDF',
                'value' => 'project'
            ]);
        }
        if (
            $project->getOwner() != $this->getUser()
        ) {
            return $this->json([
                'state' => 'FO',
                'value' => 'project'
            ]);
        }

        $data = json_decode($request->getContent(), true);

        if ($data) {

            {
                if (!isset($data['name']) || empty(trim($data['name']))) {
                    $project->setName($data['name']);
                }

                if (isset($data['client_id']) && !empty(trim($data['client_id']))) {
                    $client = $clientRepository->find($data['client_id']);
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
                    $project->setClient($client);
                }
                if (isset($data['figma_link']) && !empty(trim($data['figma_link']))) {
                    $project->setFigmaLink($data['figma_link']);
                }
                if (isset($data['github_link']) && !empty(trim($data['github_link']))) {
                    $project->setGithubLink($data['github_link']);
                }

                if (isset($data['start_date']) && !empty(trim($data['start_date']))) {
                    $project->setStartDate($data['start_date']);
                }
                if (isset($data['end_date']) && !empty(trim($data['end_date']))) {
                    $project->setEndDate($data['end_date']);
                }
                if (isset($data['total_price']) && !empty(trim($data['total_price']))) {

                    $isValid = $data['total_price'] > 0 && is_numeric($data['total_price']);
                    if(!$isValid){
                        return $this->json([
                            'state' => 'IDT',
                            'value' => 'total_price'
                        ]);
                    }
                    $project->setTotalPrice($data['total_price']);
                }
                $project->setCreatedAt(new \DateTimeImmutable());
                $project->setOwner($this->getUser());
                $project->setState('active');
                $manager->persist($project);
                $manager->flush();
                return $this->json([
                    'state' => 'OK',
                    'value' => $this->json($this->getDataProject($project))
                ]);

            }

        }
        return $this->json([
            'state' => 'ND'
        ]);

    }

    public function getDataProject($project)
    {
        $client = null;
        if ($project->getClient()) {
            $client = $project->getClient()->getId();
        }
        return [
            "id" => $project->getId(),
            "name" => $project->getName(),
            "figmaLink" => $project->getFigmaLink(),
            "githubLink" => $project->getGithubLink(),
            "state" => $project->getState(),
            "startDate" => $project->getStartDate(),
            "endDate" => $project->getEndDate(),
            "totalPrice" => $project->getTotalPrice(),
            "client_id" => $client,
            'owner' => $project->getOwner()->getEmail()
        ];
    }

    #[Route('/api/project/delete/{id}', name: 'delete_project', methods: 'delete')]
    public function delete($id, ProjectRepository $repository, EntityManagerInterface $manager): Response
    {

        $project = $repository->find($id);
        if (!$project) {
            return $this->json([
                'state' => 'NDF',
                'value' => 'project'
            ]);
        }
        if (!$project->getOwner() == $this->getUser()) {
            return $this->json([
                'state' => 'FO',
                'value' => 'project'
            ]);
        }
        $project->setState('deleted');
        $manager->persist($project);
        $manager->flush();
        return $this->json([
            'state' => 'OK'
        ]);

    }

    #[Route('/api/project/deleteforce/{id}', name: 'delete_force_project', methods: 'delete')]
    public function deleteforce($id, ProjectRepository $repository, EntityManagerInterface $manager): Response
    {

        $project = $repository->find($id);
        if (!$project) {
            return $this->json([
                'state' => 'NDF',
                'value' => 'project'
            ]);
        }
        if (!$project->getOwner() == $this->getUser()) {
            return $this->json([
                'state' => 'FO',
                'value' => 'project'
            ]);
        }
        $manager->remove($project);
        $manager->flush();
        return $this->json([
            'state' => 'OK'
        ]);

    }

    #[Route('/api/project/{id}', name: 'get_project', methods: 'get')]
    public function getProject($id, ProjectRepository $repository): Response
    {
        $project = $repository->find($id);
        if (!$project) {
            return $this->json([
                'state' => 'NDF',
                'value' => 'project'
            ]);
        }
        if (!$project->getOwner() == $this->getUser()) {
            return $this->json([
                'state' => 'FO',
                'value' => 'project'
            ]);
        }
        return $this->json([
            'state' => 'OK',
            'value' => $this->json($this->getDataProject($project))
        ]);
    }

    #[Route('/api/projects', name: 'get_projects', methods: 'get')]
    public function getProjects(ProjectRepository $repository, Request $request): Response
    {
        $datum = json_decode($request->getContent(), true);
        if ($datum) {
            if (isset($datum['display_deleted']) && !empty(trim($datum['display_deleted']))) {
                if ($datum['display_deleted']) {
                    $data = [];
                    foreach ($repository->findBy([], ['createdAt' => 'ASC']) as $client) {
                        if ($client->getOwner() == $this->getUser()) {
                            $data[] = $this->getDataProject($client);
                        }
                    }
                    return $this->json([
                        'state' => 'OK',
                        'value' => $data
                    ]);
                }
            }
        }

        $data = [];
        foreach ($repository->findBy([], ['createdAt' => 'ASC']) as $client) {
            if ($client->getOwner() == $this->getUser() && $client->getState() != 'deleted') {
                $data[] = $this->getDataProject($client);
            }

        }
        return $this->json([
            'state' => 'OK',
            'value' => $data
        ]);
    }

}
