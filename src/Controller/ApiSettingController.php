<?php

namespace App\Controller;

use App\Entity\Setting;
use App\Service\LogService;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ApiSettingController extends AbstractController
{
    private $associationKey = [
        "UE", "SUI", "PB", "US", "AS", "ISO"
    ];
    private $associationLangageKey = [
        "FR","AG"
    ];  private $associationPayementKey = [
        "CHEQUE","CASH","BANKTRANSFER"
    ];
    private $delayDaysKey = [30,60,50];
    private LogService $logService;
    private EntityManagerInterface $entityManager;

    public function __construct(LogService $logService, EntityManagerInterface $entityManager)
    {
        $this->logService = $logService;
        $this->entityManager = $entityManager;
    }

    #[Route('/api/settings', name: 'get_api_setting', methods: ['GET'])]
    public function getSettings(): Response
    {
        try {
            $settings = $this->getUser()->getSetting();

            if (!$settings) {
                $settings = $this->createDefaultSettings();

            }
            return new JsonResponse( [
                    'state' => 'OK', 'value' => $this->getData($settings)
                ]
             ,Response::HTTP_OK);

        } catch (\Exception $exception) {
            $this->logService->createLog('ERROR', ' Internal Servor Error ~'.$exception->getMessage().'~ at |' . $exception->getFile() . ' | line |' . $exception->getLine() );
            return new JsonResponse( [

                    'state' => 'ISE',
                    'value' => ' Internal Servor Error : '.$exception->getMessage().' at |' . $exception->getFile() . ' | line |' . $exception->getLine()

                ]
             ,Response::HTTP_INTERNAL_SERVER_ERROR);
              }
    }

    #[Route('/api/edit/settings', name: 'edit_api_setting', methods: ['put'])]
    public function editSettings(EntityManagerInterface $entityManager, Request $request): Response
    {
        try {
            $data = json_decode($request->getContent(), true);
            if ($data) {

                $settings = $this->getUser()->getSetting();
                if (!$settings) {
                    $settings = $this->createDefaultSettings();

                }
                if (isset($data['formatDate']) && !empty(trim($data['formatDate']))) {
                    if (!in_array($data['formatDate'], $this->associationKey)) {
                        return new JsonResponse( [
                        'state' => 'IDT',
                        'value' => 'formatDate',
                    ] ,Response::HTTP_UNPROCESSABLE_ENTITY);
                    }
                    $settings->setDateFormat($data['formatDate']);
                }

                if (isset($data['payments']) && gettype($data['payments']) == 'array') {
                    foreach ($data['payments'] as $pay) {
                            if(!in_array($pay, $this->associationPayementKey)) {
                                return new JsonResponse( [
                        'state' => 'IDT',
                        'value' => 'payments',
                    ] ,Response::HTTP_UNPROCESSABLE_ENTITY);
                            }
                    }
                    $settings->setPayment(implode(',', $data['payments']));
                }
                if (isset($data['delayDays'])) {
                    if (!is_numeric($data['delayDays']) || !in_array($data['delayDays'], $this->delayDaysKey)) {
                        return new JsonResponse( [
                        'state' => 'IDT',
                        'value' => 'formatDate',
                    ] ,Response::HTTP_UNPROCESSABLE_ENTITY);
                    }


                    $settings->setDelayDays($data['delayDays']);
                }
                if (isset($data['installmentPayments'])) {

                    if (!is_bool($data['installmentPayments'])) {
                        return new JsonResponse( [
                            'state' => 'IDT',
                            'value' => 'installmentPayments',
                        ] ,Response::HTTP_UNPROCESSABLE_ENTITY);

                    }

                    $settings->setInstallmentPayments($data['installmentPayments']);
                }
                if (isset($data['freeMaintenance'])) {

                    if (!is_bool($data['freeMaintenance'])) {
                        return new JsonResponse( [
                            'state' => 'IDT',
                            'value' => 'freeMaintenance',
                        ] ,Response::HTTP_UNPROCESSABLE_ENTITY);

                    }

                    $settings->setFreeMaintenance($data['freeMaintenance']);
                }

                $entityManager->persist($settings);
                $entityManager->flush();
                return new JsonResponse( [
                        'state' => 'OK','value' => $this->getData($settings)
                    ]
                 ,Response::HTTP_OK);

            }
             return new JsonResponse( ['state' => 'ND'] ,Response::HTTP_BAD_REQUEST);

        } catch (\Exception $exception) {
            $this->logService->createLog('ERROR', ' Internal Servor Error ~'.$exception->getMessage().'~ at |' . $exception->getFile() . ' | line |' . $exception->getLine() );
            return new JsonResponse( [

                    'state' => 'ISE',
                    'value' => ' Internal Servor Error : '.$exception->getMessage().' at |' . $exception->getFile() . ' | line |' . $exception->getLine()

                ]
             ,Response::HTTP_INTERNAL_SERVER_ERROR);  }
    }

    public function createDefaultSettings()
    {
        try {
            $setting = new Setting();
            $setting->setOwner($this->getUser());
            $setting->setDateFormat('UE');
            $setting->setPayment('');
            $setting->setDelayDays(30);
            $setting->setFreeMaintenance(true);
            $setting->setInstallmentPayments(true);
            $setting->setInterfaceLangage('FR');
            $this->entityManager->persist($setting);
            $this->entityManager->flush();
            return $setting;
        } catch (\Exception $exception) {
            $this->logService->createLog('ERROR', ' Internal Servor Error ~'.$exception->getMessage().'~ at |' . $exception->getFile() . ' | line |' . $exception->getLine() );
            return new JsonResponse( [

                    'state' => 'ISE',
                    'value' => ' Internal Servor Error : '.$exception->getMessage().' at |' . $exception->getFile() . ' | line |' . $exception->getLine()

                ]
             ,Response::HTTP_INTERNAL_SERVER_ERROR); }
    }

    public function getData($setting)
    {
        $payments = explode(',', $setting->getPayment());
        return [
            'formatDate' => $setting->getDateFormat(),
            'payments' => $payments,
            'delayDays' => $setting->getDelayDays(),
            'installmentPayments' => $setting->isInstallmentPayments(),
            'freeMaintenance' => $setting->isFreeMaintenance(),

        ];
    }
}
