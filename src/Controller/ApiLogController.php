<?php

namespace App\Controller;

use App\Entity\Logs;
use App\Repository\LogsRepository;
use App\Service\DateService;
use App\Service\LogService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ApiLogController extends AbstractController
{
    private LogService $logService;
    private DateService $dateService;

    public function __construct(LogService $logService, DateService $dateService)
    {
        $this->logService = $logService;
        $this->dateService = $dateService;
    }

    #[Route('/api/logs', name: 'getL_lgs',methods: 'get')]
    public function index(LogsRepository $repository): Response
    {
        try {

            $data = [];

            foreach($repository->findBy(['author' =>$this->getUser()], ['date' => 'DESC']) as $log){
                $data[]=[
                    'id' => $log->getId(),
                    'date' => $this->dateService->formateDateWithHour( $log->getDate()),
                    'author' => $log->getAuthor()->getEmail(),
                    'message' => ucfirst($log->getMessage()) ,
                    'type'=>$log->getType(),
                ];
            }
            return new JsonResponse( [
                    'state' => 'OK',
                    'value' =>$data]
             ,Response::HTTP_OK);
        } catch (\Exception $exception) {
            $this->logService->createLog('ERROR',' Internal Servor Error at |' . $exception->getFile() . ' | line |' . $exception->getLine());
            return new JsonResponse( [

                  'state' => 'ISE',
                'value' => ' Internal Servor Error : '.$exception->getMessage().' at |' . $exception->getFile() . ' | line |' . $exception->getLine()

                ]
             ,Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


}
