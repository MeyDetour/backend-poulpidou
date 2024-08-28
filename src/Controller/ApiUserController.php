<?php

namespace App\Controller;

use App\Entity\Client;
use App\Repository\ClientRepository;
use App\Repository\UserRepository;
use App\Service\LogService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\Routing\Attribute\Route;

class ApiUserController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private LogService $logService;

    public function __construct(EntityManagerInterface $entityManager, LogService $logService)
    {
        $this->entityManager = $entityManager;
        $this->logService = $logService;
    }

    #[Route('/api/me', name: 'user_me', methods: 'get')]
    public function index(): Response
    {

        return new JsonResponse( [
                'state' => 'OK','value' => $this->getData($this->getUser())
            ]
         ,Response::HTTP_OK);
    }
    #[Route('/api/users', name: 'users', methods: 'get')]
    public function getUsers(UserRepository $userRepository): Response
    {
        $data = [];
        foreach ($userRepository->findAll() as $user) {
            $data[]=[
                'id' => $user->getId(),
                'mail' => $user->getMail(),
                'phone' => $user->getPhone(),
                'firstName' => $user->getFirstName(),
                'lastName' => $user->getLastName(),
            ];
        }

        return new JsonResponse( [
                'state' => 'OK',   'value' => $data
            ]
         ,Response::HTTP_OK);
    }
    #[Route('/api/edit/me', name: 'edit_me', methods: 'put')]
    public function edit(Request $request, EntityManagerInterface $manager)
    {
        try {
            $data = json_decode($request->getContent(), true);
            $user = $this->getUser();

            if ($data) {

                if (isset($data['firstName']) && !empty(trim($data['firstName']))) {
                    $user->setFirstName($data['firstName']);
                }
                if (isset($data['lastName']) && !empty(trim($data['lastName']))) {
                    $user->setLastName($data['lastName']);
                }
                if (isset($data['phone']) && !empty(trim($data['phone']))) {
                    $user->setPhone($data['phone']);
                }
                if (isset($data['siret']) && !empty(trim($data['siret']))) {
                    $user->setSiret($data['siret']);
                }
                if (isset($data['address']) && !empty(trim($data['address']))) {
                    $user->setAdresse($data['address']);
                }
                $manager->persist($user);
                $manager->flush();
                $this->logService->createLog('ACTION', 'Edit profile (' . $user->getEmail() . ')');

                return new JsonResponse( [
                        'state' => 'OK','value' => $this->getData($user)
                    ]
                 ,Response::HTTP_OK);

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

    #[Route('/api/user/{id}', name: 'user',methods: 'get')]
    public function getOneUser($id, UserRepository $repository): Response
    {
        $user = $repository->find($id);
        if (!$user) {
            try {
                 return new JsonResponse( [
                        'state' => 'NDF',
                        'value' => 'user',
                     ] , Response::HTTP_NOT_FOUND);
            } catch (\Exception $exception) {
                return new JsonResponse( [

                  'state' => 'ISE',
                'value' => ' Internal Servor Error : '.$exception->getMessage().' at |' . $exception->getFile() . ' | line |' . $exception->getLine()

                ]
             ,Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }

        return new JsonResponse( [
                'state' => 'OK',"value" =>$this->getData($user)
            ]
         ,Response::HTTP_OK);

    }

    public function getData($user)
    {
        return [
            'id' => $user->getId(),
            'mail' => $user->getEmail(),
            'phone' => $user->getPhone(),
            'siret' => $user->getSiret(),
            'address' => $user->getAdresse(),
            'firstName' => $user->getFirstName(),
            'lastName' => $user->getLastName(),
        ];

    }

    public function formatNames($user)
    {
        $user->setFirstName(ucfirst($user->getFirstName()));
        $user->setLastName(strtoupper($user->getLastName()));
        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }
}
