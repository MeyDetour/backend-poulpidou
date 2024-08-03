<?php

namespace App\Controller;

use App\Entity\Note;
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
    public function edit(Request $request, EntityManagerInterface $manager, NoteRepository $repository): Response
    {
        try {
            $note = $repository->findOneBy(['owner' => $this->getUser()]);
            if (!$note) {
                $note = new Note();
                $note->setOwner($this->getUser());
                $note->setNotes("");
                $note->setRemembers("");

                $manager->persist($note);
                $manager->flush();

            }
            $data = json_decode($request->getContent(), true);

            if ($data) {

                if (isset($data['notes']) && !empty($data['notes'])) {

                    $note->setNotes($data['notes']);
                }
                if (isset($data['remembers']) && !empty($data['remembers'])) {

                    $note->setRemembers($data['remembers']);
                }

                $manager->persist($note);
                $manager->flush();
                $this->logService->createLog('ACTION', $this->getUser()->getEmail() . ' edit his note (' . $note->getId() . ')', null);
                return $this->json([
                    'state' => 'OK',
                    'value' =>
                        $this->getDataNote($note)
                ]);

            }
            return $this->json([
                'state' => 'ND',
            ]);
        } catch (\Exception $exception) {
            return $this->json([
                'state' => 'ISE',
                'value' => ' Internal Servor Error : ' . $exception->getMessage() . ' at |' . $exception->getFile() . ' | line |' . $exception->getLine()

            ]);
        }
    }

    #[Route('/api/note', name: 'get_note', methods: 'get')]
    public function index(Request $request, EntityManagerInterface $manager, NoteRepository $repository): Response
    {
        try {
            $note = $repository->findOneBy(['owner' => $this->getUser()]);
            if (!$note) {
                $note = new Note();
                $note->setOwner($this->getUser());
                $note->setNotes("");
                $note->setRemembers("");

                $manager->persist($note);
                $manager->flush();

            }
            return $this->json([
                'state' => 'OK',
                'value' =>
                    $this->getDataNote($note)
            ]);
        } catch (\Exception $exception) {
            return $this->json([
                'state' => 'ISE',
                'value' => ' Internal Servor Error : ' . $exception->getMessage() . ' at |' . $exception->getFile() . ' | line |' . $exception->getLine()

            ]);
        }
    }

    public function getDataNote(Note $note): array
    {
        return [
            'id' => $note->getId(),
            'notes' => $note->getNotes(),
            'remembers' => $note->getRemembers(),
        ];
    }
}
