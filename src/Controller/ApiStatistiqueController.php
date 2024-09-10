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
    private $timeValues = ['10yrs', '1yrs', '1m'];

    public function __construct(LogService $logService, DateService $dateService)
    {
        $this->logService = $logService;
        $this->dateService = $dateService;
    }

    #[Route('/api/statistic', name: 'app_api_statistic', methods: "get")]
    public function index(Request $request, ProjectRepository $projectRepository, TaskRepository $taskRepository, InvoiceRepository $invoiceRepository): Response
    {
        try {
            $data = json_decode($request->getContent(), true);
            if (!$data) {
                return new JsonResponse(['state' => 'ND'], Response::HTTP_BAD_REQUEST);
            }
            if (!isset($data["type"]) || empty($data["type"])) {
                return new JsonResponse(['state' => 'NEF', 'value' => 'type'], Response::HTTP_UNPROCESSABLE_ENTITY);
            }
            if (!isset($data["time"]) || empty($data["time"])) {
                return new JsonResponse(['state' => 'NEF', 'value' => 'type'], Response::HTTP_UNPROCESSABLE_ENTITY);
            }
            if (!in_array($data["type"], $this->typeValues)) {
                return new JsonResponse([
                        'state' => 'IDV',
                        'value' => 'status',
                    ]
                    , Response::HTTP_UNPROCESSABLE_ENTITY);
            }
            if (!in_array($data["time"], $this->timeValues)) {
                return new JsonResponse([
                        'state' => 'IDV',
                        'value' => 'status',
                    ]
                    , Response::HTTP_UNPROCESSABLE_ENTITY);
            }
            $dataToSend = [];
            $type = $data["type"];
            $time = $data["time"];

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
                                $year = $today->format('Y') - $i;
                                $startOfYear = (new \DateTimeImmutable())->setDate($year, 1, 1)->setTime(0, 0);
                                $endOfYear = (new \DateTimeImmutable())->setDate($year, 12, 31)->setTime(23, 59, 59);

                                $count = $projectRepository->findBetweenDate($startOfYear, $endOfYear, $this->getUser());
                                if ($count != 0) {
                                    $dataToSend[$year] = $count;
                                }
                            }
                            break;

                        case "1yr":
                            for ($i = 1; $i <= 12; $i++) {
                                $startOfMonth = (new \DateTimeImmutable())->setDate($today->format('Y'), $i, 1)->setTime(0, 0);
                                $endOfMonth = $startOfMonth->modify('last day of this month')->setTime(23, 59, 59);

                                $count = $projectRepository->findBetweenDate($startOfMonth, $endOfMonth, $this->getUser());
                                if ($count != 0) {
                                    $dataToSend[$i] = $count;
                                }
                            }
                            break;

                        case "1m":
                            for ($i = 1; $i <= $lastDayInMonth->format('d'); $i++) {
                                $startOfDay = $today->setDate($today->format('Y'), $today->format('m'), $i)->setTime(0, 0, 0);
                                $endOfDay = $startOfDay->setTime(23, 59, 59);

                                $count = $projectRepository->findBetweenDate($startOfDay, $endOfDay, $this->getUser());

                                if ($count != 0) {
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
                                $year = $today->format('Y') - $i;
                                $startOfYear = (new \DateTimeImmutable())->setDate($year, 1, 1)->setTime(0, 0);
                                $endOfYear = (new \DateTimeImmutable())->setDate($year, 12, 31)->setTime(23, 59, 59);

                                $count = $taskRepository->findBetweenDate($startOfYear, $endOfYear, $this->getUser());
                                if ($count != 0) {
                                    $dataToSend[$year] = $count;
                                }
                            }
                            break;

                        case "1yr":
                            for ($i = 1; $i <= 12; $i++) {
                                $startOfMonth = (new \DateTimeImmutable())->setDate($today->format('Y'), $i, 1)->setTime(0, 0);
                                $endOfMonth = $startOfMonth->modify('last day of this month')->setTime(23, 59, 59);

                                $count = $taskRepository->findBetweenDate($startOfMonth, $endOfMonth, $this->getUser());
                                if ($count != 0) {
                                    $dataToSend[$i] = $count;
                                }
                            }
                            break;

                        case "1m":
                            for ($i = 1; $i <= $lastDayInMonth->format('d'); $i++) {
                                $startOfDay = $today->setDate($today->format('Y'), $today->format('m'), $i)->setTime(0, 0, 0);
                                $endOfDay = $startOfDay->setTime(23, 59, 59);

                                $count = $taskRepository->findBetweenDate($startOfDay, $endOfDay, $this->getUser());
                                if ($count != 0) {
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
                                $year = $today->format('Y') - $i;
                                $startOfYear = (new \DateTimeImmutable())->setDate($year, 1, 1)->setTime(0, 0);
                                $endOfYear = (new \DateTimeImmutable())->setDate($year, 12, 31)->setTime(23, 59, 59);

                                $count = $invoiceRepository->findBetweenDate($startOfYear, $endOfYear, $this->getUser());
                                if ($count != 0) {
                                    $dataToSend[$year] = $count;
                                }
                            }
                            break;

                        case "1yr":
                            for ($i = 1; $i <= 12; $i++) {
                                $startOfMonth = (new \DateTimeImmutable())->setDate($today->format('Y'), $i, 1)->setTime(0, 0);
                                $endOfMonth = $startOfMonth->modify('last day of this month')->setTime(23, 59, 59);

                                $count = $invoiceRepository->findBetweenDate($startOfMonth, $endOfMonth, $this->getUser());
                                if ($count != 0) {
                                    $dataToSend[$i] = $count;
                                }
                            }
                            break;

                        case "1m":
                            for ($i = 1; $i <= $lastDayInMonth->format('d'); $i++) {
                                $startOfDay = $today->setDate($today->format('Y'), $today->format('m'), $i)->setTime(0, 0, 0);
                                $endOfDay = $startOfDay->setTime(23, 59, 59);

                                $count = $invoiceRepository->findBetweenDate($startOfDay, $endOfDay, $this->getUser());
                                if ($count != 0) {
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
