<?php

namespace App\Controller;

use App\Repository\InvoiceRepository;
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

class ApiStatistiqueController extends AbstractController
{
    private LogService $logService;
    private DateService $dateService;

    private $typeValues = ['incomes', 'projects', 'tasks'];
    private $timeValues = ['10yrs', '1yr', '1m'];

    public function __construct(LogService $logService, DateService $dateService)
    {
        $this->logService = $logService;
        $this->dateService = $dateService;
    }

    #[Route('/api/statistic', name: 'app_api_statistic', methods: "get")]
    public function index(Request $request, ProjectRepository $projectRepository, TaskRepository $taskRepository, InvoiceRepository $invoiceRepository): Response
    {
        try {

            $type = $request->query->get('type');
            $time = $request->query->get('time');

            if (empty($type)) {
                return new JsonResponse(['state' => 'NED', 'value' => 'type'], Response::HTTP_UNPROCESSABLE_ENTITY);
            }
            if (empty($time)) {
                return new JsonResponse(['state' => 'NED', 'value' => 'time'], Response::HTTP_UNPROCESSABLE_ENTITY);
            }
            if (!in_array($type, $this->typeValues)) {
                return new JsonResponse([
                        'state' => 'IDV',
                        'value' => 'type',
                    ]
                    , Response::HTTP_UNPROCESSABLE_ENTITY);
            }
            if (!in_array($time, $this->timeValues)) {
                return new JsonResponse([
                        'state' => 'IDV',
                        'value' => 'time',
                    ]
                    , Response::HTTP_UNPROCESSABLE_ENTITY);
            }
            $dataToSend = [];

            $today = new \DateTimeImmutable();
            $today = $today->setTime(0, 0);
            $firstDayInYear = $today->setDate($today->format('Y'), 1, 1);
            $firstDayInMonth = $today->setDate($today->format('Y'), $today->format('m'), 1);
            $lastDayInMonth = $today->setDate($today->format('Y'), $today->format('m') + 1, 0);
            $lastDayInYear = $firstDayInYear->setDate($firstDayInYear->format('Y'), 13, 0);

            switch ($type) {
                case "projects":
                    switch ($time) {
                        case "10yrs":
                            for ($i = 0; $i <= 10; $i++) {
                                $year = $today->format('Y') - 10 + $i;
                                $startOfYear = (new \DateTimeImmutable())->setDate($year, 1, 1)->setTime(0, 0);
                                $endOfYear = (new \DateTimeImmutable())->setDate($year, 12, 31)->setTime(23, 59, 59);

                                $count = count($projectRepository->findBetweenDate($startOfYear, $endOfYear, $this->getUser()));

                                $dataToSend[$year] = $count;


                            }
                            break;

                        case "1yr":
                            for ($i = 1; $i <= 12; $i++) {
                                $startOfMonth = (new \DateTimeImmutable())->setDate($today->format('Y'), $i, 1)->setTime(0, 0);
                                $endOfMonth = $startOfMonth->modify('last day of this month')->setTime(23, 59, 59);

                                $count = count($projectRepository->findBetweenDate($startOfMonth, $endOfMonth, $this->getUser()));
                                if ($i <= $today->format('m')) {
                                    $dataToSend[$i] = $count;
                                }

                            }
                            break;

                        case "1m":
                            for ($i = 1; $i <= $lastDayInMonth->format('d'); $i++) {
                                $startOfDay = $today->setDate($today->format('Y'), $today->format('m'), $i)->setTime(0, 0, 0);
                                $endOfDay = $startOfDay->setTime(23, 59, 59);

                                $count = count($projectRepository->findBetweenDate($startOfDay, $endOfDay, $this->getUser()));

                                if ($i <= $today->format('d')) {
                                    $dataToSend[$i] = $count;
                                }


                            }
                            break;
                    }
                    break;

                case "tasks":
                    switch ($time) {
                        case "10yrs":
                            for ($i = 0; $i <= 10; $i++) {
                                $year = $today->format('Y') - 10 + $i;
                                $startOfYear = (new \DateTimeImmutable())->setDate($year, 1, 1)->setTime(0, 0);
                                $endOfYear = (new \DateTimeImmutable())->setDate($year, 12, 31)->setTime(23, 59, 59);

                                $count = count($taskRepository->findBetweenDate($startOfYear, $endOfYear, $this->getUser()));

                                $dataToSend[$year] = $count;

                            }
                            break;

                        case "1yr":
                            for ($i = 1; $i <= 12; $i++) {
                                $startOfMonth = (new \DateTimeImmutable())->setDate($today->format('Y'), $i, 1)->setTime(0, 0);
                                $endOfMonth = $startOfMonth->modify('last day of this month')->setTime(23, 59, 59);

                                $count = count($taskRepository->findBetweenDate($startOfMonth, $endOfMonth, $this->getUser()));
                                if ($i <= $today->format('m')) {
                                    $dataToSend[$i] = $count;
                                }
                            }
                            break;

                        case "1m":
                            for ($i = 1; $i <= $lastDayInMonth->format('d'); $i++) {
                                $startOfDay = $today->setDate($today->format('Y'), $today->format('m'), $i)->setTime(0, 0, 0);
                                $endOfDay = $startOfDay->setTime(23, 59, 59);

                                $count = count($taskRepository->findBetweenDate($startOfDay, $endOfDay, $this->getUser()));


                                if ($i <= $today->format('d')) {
                                    $dataToSend[$i] = $count;
                                }
                            }
                            break;
                    }
                    break;

                case "incomes":
                    switch ($time) {
                        case "10yrs":
                            for ($i = 0; $i <= 10; $i++) {
                                $year = $today->format('Y') - 10 + $i;
                                $startOfYear = (new \DateTimeImmutable())->setDate($year, 1, 1)->setTime(0, 0);
                                $endOfYear = (new \DateTimeImmutable())->setDate($year, 12, 31)->setTime(23, 59, 59);

                                $count = count($invoiceRepository->findBetweenDate($startOfYear, $endOfYear, $this->getUser()));

                                $dataToSend[$year] = $count;

                            }
                            break;

                        case "1yr":
                            for ($i = 1; $i <= 12; $i++) {
                                $startOfMonth = (new \DateTimeImmutable())->setDate($today->format('Y'), $i, 1)->setTime(0, 0);
                                $endOfMonth = $startOfMonth->modify('last day of this month')->setTime(23, 59, 59);

                                $count = count($invoiceRepository->findBetweenDate($startOfMonth, $endOfMonth, $this->getUser()));

                                if ($i <= $today->format('m')) {
                                    $dataToSend[$i] = $count;
                                }
                            }
                            break;

                        case "1m":
                            for ($i = 1; $i <= $lastDayInMonth->format('d'); $i++) {
                                $startOfDay = $today->setDate($today->format('Y'), $today->format('m'), $i)->setTime(0, 0, 0);
                                $endOfDay = $startOfDay->setTime(23, 59, 59);

                                $count = count($invoiceRepository->findBetweenDate($startOfDay, $endOfDay, $this->getUser()));


                                if ($i <= $today->format('d')) {
                                    $dataToSend[$i] = $count;
                                }
                            }
                            break;
                    }
                    break;
            }

            return new JsonResponse([
                    'state' => 'OK',
                    "value" =>
                        $dataToSend
                ]
                , Response::HTTP_OK);

        } catch
        (\Exception $exception) {
            $this->logService->createLog('ERROR', ' Internal Servor Error ~' . $exception->getMessage() . '~ at |' . $exception->getFile() . ' | line |' . $exception->getLine());


            return new JsonResponse([

                    'state' => 'ISE',
                    'value' => ' Internal Servor Error : ' . $exception->getMessage() . ' at |' . $exception->getFile() . ' | line |' . $exception->getLine()

                ]
                , Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
