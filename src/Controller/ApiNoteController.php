<?php

namespace App\Controller;


use App\Entity\Project;
use App\Repository\ClientRepository;
use App\Repository\NoteRepository;
use App\Service\LogService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ApiNoteController extends AbstractController
{
    private LogService $logService;


    public function __construct(LogService $logService)
    {
        $this->logService = $logService;
    }

    #[Route('/api/edit/note', name: 'new_note', methods: 'post')]
    public function edit(Request $request, EntityManagerInterface $manager): Response
    {
        try {

           $user = $this->getUser();
            $data = json_decode($request->getContent(), true);

            if ($data) {

                if (isset($data['notes']) && !empty($data['notes'])) {

                    $user->setNote($data['notes']);
                }
                if (isset($data['remembers']) && !empty($data['remembers'])) {

                    $user->setRemember($data['remembers']);
                }

                $manager->persist($user);
                $manager->flush();
                $this->logService->createLog('ACTION', $this->getUser()->getEmail() .')');
                return new JsonResponse( [
                    'state' => 'OK',
                    'value' =>
                        $this->getDataNote()]
                 ,Response::HTTP_OK, [], true);

            }
            return new JsonResponse( ['state' => 'ND'] ,Response::HTTP_BAD_REQUEST);
        } catch (\Exception $exception) {
            return new JsonResponse( [

                    'state' => 'ISE',
                    'value' => ' Internal Servor Error : '.$exception->getMessage().' at |' . $exception->getFile() . ' | line |' . $exception->getLine()

                ]
             ,Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/api/note', name: 'get_note', methods: 'get')]
    public function index( EntityManagerInterface $manager): Response
    {
        try {

            return new JsonResponse( [
                    'state' => 'OK', 'value' =>
                        $this->getDataNote()
                ]
             ,Response::HTTP_OK, [], true);
        } catch (\Exception $exception) {
            return new JsonResponse( [

                    'state' => 'ISE',
                    'value' => ' Internal Servor Error : '.$exception->getMessage().' at |' . $exception->getFile() . ' | line |' . $exception->getLine()

                ]
             ,Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getDataNote(): array
    {
        return [

            'notes' => $this->getUser()->getNote(),
            'remembers' => $this->getUser()->getRemember(),
        ];
    }
}
