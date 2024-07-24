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


            if (isset($data['client_id']) && !empty($data['client_id'])) {
                $client = $clientRepository->find($data['client_id']);
                if (!$client) {
                    return $this->json(['message' => 'no client found']);
                }
                if ($client->getOwner() != $this->getUser()) {
                    return $this->json(['message' => 'not yours']);
                }

                $project->setClient($client);
            }
            if (isset($data['name']) && !empty($data['name'])) {
                $project->setName($data['name']);
            } else {
                return $this->json(['message' => 'no name send']);
            }
            if (isset($data['figma_link']) && !empty($data['figma_link'])) {
                $project->setFigmaLink($data['figma_link']);
            }
            if (isset($data['github_link']) && !empty($data['github_link'])) {
                $project->setGithubLink($data['github_link']);
            }

            if (isset($data['start_date']) && !empty($data['start_date'])) {
                $project->setStartDate($data['start_date']);
            }
            if (isset($data['end_date']) && !empty($data['end_date'])) {
                $project->setEndDate($data['end_date']);
            }
            if (isset($data['total_price']) && !empty($data['total_price'])) {
                $project->setTotalPrice($data['total_price']);
            }
            $project->setCreatedAt(new \DateTimeImmutable());
            $project->setOwner($this->getUser());
            $project->setState('active');
            $manager->persist($project);
            $manager->flush();


            return $this->json($this->getDataProject($project));
        }
        return $this->json(['message' => 'no data']);

    }

    #[Route('/api/project/edit/{id}', name: 'edit_api_project')]
    public function edit($id, ProjectRepository $repository, ClientRepository $clientRepository, Request $request, EntityManagerInterface $manager): Response
    {
        $project = $repository->find($id);
        if (!$project) {

            return $this->json(['message' => 'no project']);
        }
        $data = json_decode($request->getContent(), true);

        if ($data) {
            if (
                $project->getOwner() == $this->getUser()
            ) {
                if (isset($data['name']) && !empty($data['name'])) {
                    $project->setName($data['name']);
                } else {
                    return $this->json(['message' => 'no name send']);
                }
                if (isset($data['client_id']) && !empty($data['client_id'])) {
                    $client = $clientRepository->find($data['client_id']);
                    if (!$client) {
                        return $this->json(['message' => 'no client found']);
                    }
                    if ($client->getOwner() != $this->getUser()) {
                        return $this->json(['message' => 'not yours']);
                    }
                    $project->setClient($client);
                }
                if (isset($data['figma_link']) && !empty($data['figma_link'])) {
                    $project->setFigmaLink($data['figma_link']);
                }
                if (isset($data['github_link']) && !empty($data['github_link'])) {
                    $project->setGithubLink($data['github_link']);
                }

                if (isset($data['start_date']) && !empty($data['start_date'])) {
                    $project->setStartDate($data['start_date']);
                }
                if (isset($data['end_date']) && !empty($data['end_date'])) {
                    $project->setEndDate($data['end_date']);
                }
                if (isset($data['total_price']) && !empty($data['total_price'])) {
                    $project->setTotalPrice($data['total_price']);
                }
                $project->setCreatedAt(new \DateTimeImmutable());
                $project->setOwner($this->getUser());
                $project->setState('active');
                $manager->persist($project);
                $manager->flush();


                return $this->json($this->getDataProject($project));
            }else{

                return $this->json(['message' => 'not yours']);
            }
        }
        return $this->json(['message' => 'no data']);

    }

    public function getDataProject($project)
    {
        $client = null;
        if($project->getClient()){
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
            "client_id"=>$client,
            'owner'=>$project->getOwner()->getEmail()
        ];
    }

    #[Route('/api/project/delete/{id}', name: 'delete_project', methods: 'delete')]
    public function delete($id, ProjectRepository $repository, EntityManagerInterface $manager): Response
    {

        $project = $repository->find($id);
        if (!$project) {
            return $this->json(['message' => 'no project found']);
        }
        if (!$project->getOwner() == $this->getUser()) {
            return $this->json(['message' => 'access denied to this project, not yours']);
        }
        $project->setState('deleted');
        $manager->persist($project);
        $manager->flush();
        return $this->json(['message' => 'ok']);

    }

    #[Route('/api/project/deleteforce/{id}', name: 'delete_force_project', methods: 'delete')]
    public function deleteforce($id, ProjectRepository $repository, EntityManagerInterface $manager): Response
    {

        $project = $repository->find($id);
        if (!$project) {
            return $this->json(['message' => 'no project found']);
        }
        if (!$project->getOwner() == $this->getUser()) {
            return $this->json(['message' => 'access denied to this project, not yours']);
        }
        $manager->remove($project);
        $manager->flush();
        return $this->json(['message' => 'ok']);

    }

    #[Route('/api/project/{id}', name: 'get_project', methods: 'get')]
    public function getProject($id, ProjectRepository $repository): Response
    {
        $project = $repository->find($id);
        if (!$project) {
            return $this->json(['message' => 'no project found']);
        }
        return $this->json($this->getDataProject($project));
    }

    #[Route('/api/projects', name: 'get_projects', methods: 'get')]
    public function getProjects(ProjectRepository $repository, Request $request): Response
    {
        $datum = json_decode($request->getContent(), true);
        if ($datum) {
            if (isset($datum['display_deleted']) && !empty($datum['display_deleted'])) {
                if ($datum['display_deleted']) {
                    $data = [];
                    foreach ($repository->findBy([], ['createdAt' => 'ASC']) as $client) {
                        if ($client->getOwner() == $this->getUser()) {
                            $data[] = $this->getDataProject($client);
                        }
                    }
                    return $this->json($data);
                }
            }
        }

        $data = [];
        foreach ($repository->findBy([], ['createdAt' => 'ASC']) as $client) {
            if ($client->getOwner() == $this->getUser() && $client->getState() != 'deleted') {
                $data[] = $this->getDataProject($client);
            }

        }
        return $this->json($data);
    }

}
