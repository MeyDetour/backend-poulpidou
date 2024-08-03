<?php

namespace App\Controller;

use App\Entity\Setting;
use App\Service\LogService;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ApiSettingController extends AbstractController
{ private $associationKey = [
    "UE", "SUI", "PB", "US", "AS", "ISO"
];
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
                $this->createDefaultSettings();
            }
            return $this->json(['state' => 'OK',
                'value' => $this->getData($settings)]);

        } catch (\Exception $exception) {
            $this->logService->createLog('ERROR', ' Internal Servor Error at |' . $exception->getFile() . ' | line |' . $exception->getLine(), $exception->getMessage());
            return $this->json(['state' => 'ISE',
                'value' => ' Internal Servor Error : ' . $exception->getMessage() . ' at |' . $exception->getFile() . ' | line |' . $exception->getLine()]);
        }
    }

    #[Route('/api/edit/settings', name: 'edit_api_setting', methods: ['GET'])]
    public function editSettings(EntityManagerInterface $entityManager, Request $request): Response
    {
        try {
            $data = json_decode($request->getContent(), true);
            if ($data) {

                $settings = $this->getUser()->getSetting();
                if (!$settings) {
                    $this->createDefaultSettings();
                }
                if (isset($data['formatDate']) && !empty(trim($data['formatDate']))) {
                    if (!in_array($data['formatDate'], $this->associationKey)) {
                     return $this->json([
                         'state' => 'IDT',
                         'value'=>'formatDate',
                     ]);
                    }
                    $settings->setDateFormat($data['formatDate']);
                }
                if (isset($data['payment']) && gettype($data['payment']) == 'array') {

                    $settings->setPayment(implode(',', $data['payment']));
                }

    $entityManager->persist($settings);
                $entityManager->flush();
                return $this->json(['state' => 'OK',

                    'value' => $this->getData($settings)]);

            }
            return $this->json(['state' => 'ND']);

        } catch (\Exception $exception) {
            $this->logService->createLog('ERROR', ' Internal Servor Error at |' . $exception->getFile() . ' | line |' . $exception->getLine(), $exception->getMessage());
            return $this->json(['state' => 'ISE',
                'value' => ' Internal Servor Error : ' . $exception->getMessage() . ' at |' . $exception->getFile() . ' | line |' . $exception->getLine()]);
        }
    }

    public function createDefaultSettings()
    {
        $setting = new Setting();
        $setting->setOwner($this->getUser());
        $setting->setDateFormat('UE');
        $setting->setPayment('');
        $this->entityManager->persist($setting);
        $this->entityManager->flush();
    }

    public function getData($setting)
    {

        return [
            'formatDate' => $setting->getDateFormat(),
            'payment' => $setting->getPayment(),
        ];
    }
}
