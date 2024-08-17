<?php

namespace App\Controller;

use App\Entity\Client;
use App\Entity\Invoice;
use App\Repository\ClientRepository;
use App\Repository\InvoiceRepository;
use App\Repository\ProjectRepository;
use App\Service\DateService;
use App\Service\LogService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ApiInvoiceController extends AbstractController
{
    private LogService $logService;
    private DateService $dateService;

    public function __construct(LogService $logService, DateService $dateService)
    {
        $this->logService = $logService;
        $this->dateService = $dateService;
    }

    #[Route('/api/invoice/new', name: 'new_invoice', methods: 'post')]
    public function index(Request $request, EntityManagerInterface $manager, ProjectRepository $projectRepository, InvoiceRepository $invoiceRepository): Response
    {
        try {
            $data = json_decode($request->getContent(), true);

            if ($data) {
                $invoice = new Invoice();
                if (!isset($data['description']) || empty(trim($data['description']))) {
                    return $this->json([
                        'state' => 'NED',
                        'value' => 'description'
                    ]);
                }
                $invoice->setDescription($data['description']);

                if (!isset($data['project_id'])) {
                    return $this->json([
                        'state' => 'NED',
                        'value' => 'project_id'
                    ]);
                }
                if (!is_numeric($data['project_id'])) {
                    return $this->json([
                        'state' => 'IDT',
                        'value' => 'project_id'
                    ]);
                }
                $project = $projectRepository->find($data['project_id']);
                if (!$project) {
                    return $this->json([
                        'state' => 'NDF',
                        'value' => 'project'
                    ]);
                }
                if ($project->getOwner() != $this->getUser() && !$project->hasUserInUserAuthorised($this->getUser())) {
                    return $this->json([
                        'state' => 'FO',
                        'value' => 'project'
                    ]);
                }
                if (!$project->isOtherUserCanEditInvoices() && $project->getOwner() != $this->getUser()) {
                    return $this->json([
                        'state' => 'ASFO',
                        'value' => 'project'
                    ]);
                }
                if ($project->getState() == 'deleted') {
                    return $this->json([
                        'state' => 'DD',
                        'value' => 'project'
                    ]);
                }

                $invoice->setProject($project);


                if (isset($data['price']) && !empty(trim($data['price']))) {
                    $isValid = $data['price'] > 0 && is_numeric($data['price']);
                    if (!$isValid) {
                        return $this->json([
                            'state' => 'IDT',
                            'value' => 'price'
                        ]);
                    }
                    $invoice->setPrice($data['price']);
                }

                if (isset($data['date']) && !empty(trim($data['date']))) {
                    $searchDate = \DateTime::createFromFormat('d/m/Y', $data['date']);
                    if (!$searchDate) {
                        return $this->json([
                            'state' => 'IDT',
                            'value' => 'date'
                        ]);
                    }
                    $invoice->setDate($searchDate);
                }

                $nb = mt_rand(100000, 999999);
                while (count($invoiceRepository->findBy(['number' => $nb])) != 0) {
                    $nb = mt_rand(100000, 999999);
                }
                $invoice->setCreatedAt(new \DateTimeImmutable());
                $invoice->setPayed(false);
                $invoice->setNumber($nb);
                $manager->persist($invoice);
                $manager->flush();
                $this->logService->createLog('ACTION', ' Create Invoice (' . $invoice->getId() . ') for project  Project (' . $project->getId() . ':' . $project->getName() . ') for client (' . $project->getClient()->getId() . ' | ' . $project->getClient()->getFirstName() . ' ' . $project->getClient()->getLastName() . '), action by ' . $this->getUser()->getEmail());


                return $this->json([
                    'state' => 'OK',
                    'value' => $this->getDataInvoice($invoice)
                ]);
            }
            return $this->json(['state' => 'ND']);
        } catch (\Exception $exception) {
            $this->logService->createLog('ERROR', ' Internal Servor Error at |' . $exception->getFile() . ' | line |' . $exception->getLine());


            return $this->json(['state' => 'ISE',
                'value' => ' Internal Servor Error : ' . $exception->getMessage() . ' at |' . $exception->getFile() . ' | line |' . $exception->getLine()]);
        }
    }

    #[Route('/api/invoice/edit/{id}', name: 'edit_invoice', methods: 'put')]
    public function edit($id, InvoiceRepository $invoiceRepository, Request $request, EntityManagerInterface $manager, ClientRepository $clientRepository, ProjectRepository $projectRepository): Response
    {
        try {
            $invoice = $invoiceRepository->findOneBy(['number'=>$id]);
            if (!$invoice) {
                return $this->json([
                    'state' => 'NDF',
                    'value' => 'invoice'
                ]);
            }
            if ($invoice->getProject()->getOwner() != $this->getUser() && !$invoice->getProject()->hasUserInUserAuthorised($this->getUser())) {
                return $this->json([
                    'state' => 'FO',
                    'value' => 'project'
                ]);
            }
            if (!$invoice->getProject()->isOtherUserCanEditInvoices() && $invoice->getProject()->getOwner() != $this->getUser()) {
                return $this->json([
                    'state' => 'ASFO',
                    'value' => 'project'
                ]);
            }
            if ($invoice->getProject()->getState() == 'deleted') {
                return $this->json([
                    'state' => 'DD',
                    'value' => 'project'
                ]);
            }

            $data = json_decode($request->getContent(), true);

            if ($data) {

                if (isset($data['description']) && !empty(trim($data['description']))) {
                    $invoice->setDescription($data['description']);
                }
                if (isset($data['price']) && !empty(trim($data['price']))) {
                    $isValid = $data['price'] > 0 && is_numeric($data['price']);
                    if (!$isValid) {
                        return $this->json([
                            'state' => 'IDT',
                            'value' => 'price'
                        ]);
                    }
                    $invoice->setPrice($data['price']);
                }
                if (isset($data['date']) && !empty(trim($data['date']))) {
                    $searchDate = \DateTime::createFromFormat('d/m/Y', $data['date']);
                    if (!$searchDate) {
                        return $this->json([
                            'state' => 'IDT',
                            'value' => 'date'
                        ]);
                    }
                    $invoice->setDate($searchDate);
                }
                $manager->persist($invoice);
                $manager->flush();

                $this->logService->createLog('ACTION', ' Edit Invoice (' . $invoice->getId() . ') for project  Project (' . $invoice->getProject()->getId() . ':' . $invoice->getProject()->getName() . ') for client (' . $invoice->getProject()->getClient()->getId() . ' | ' . $invoice->getProject()->getClient()->getFirstName() . ' ' . $invoice->getProject()->getClient()->getLastName() . '), action by ' . $this->getUser()->getEmail());

                return $this->json([
                    'state' => 'OK',
                    'value' => $this->getDataInvoice($invoice)
                ]);
            }
            return $this->json([
                'state' => 'ND'
            ]);
        } catch (\Exception $exception) {
            $this->logService->createLog('ERROR', ' Internal Servor Error at |' . $exception->getFile() . ' | line |' . $exception->getLine());


            return $this->json([
                'state' => 'ISE',
                'value' => ' Internal Servor Error : ' . $exception->getMessage() . ' at |' . $exception->getFile() . ' | line |' . $exception->getLine()

            ]);
        }
    }

    #[Route('/api/invoice/{id}/pay', name: 'pay_invoice', methods: 'put')]
    public function payInvoice($id, InvoiceRepository $invoiceRepository, Request $request, EntityManagerInterface $manager): Response
    {
        try {
            $invoice = $invoiceRepository->findOneBy(['number'=>$id]);
            if (!$invoice) {
                return $this->json([
                    'state' => 'NDF',
                    'value' => 'invoice'
                ]);
            }
            if ($invoice->getProject()->getOwner() != $this->getUser() && !$invoice->getProject()->hasUserInUserAuthorised($this->getUser())) {
                return $this->json([
                    'state' => 'FO',
                    'value' => 'project'
                ]);
            }
            if ($invoice->getProject()->getState() == 'deleted') {
                return $this->json([
                    'state' => 'DD',
                    'value' => 'project'
                ]);
            }
            $invoice->setPayed(true);
            $manager->persist($invoice);
            $manager->flush();
            return $this->json([
                'state' => 'OK'
            ]);
        } catch (\Exception $exception) {
            $this->logService->createLog('ERROR', ' Internal Servor Error at |' . $exception->getFile() . ' | line |' . $exception->getLine());


            return $this->json([
                'state' => 'ISE',
                'value' => ' Internal Servor Error : ' . $exception->getMessage() . ' at |' . $exception->getFile() . ' | line |' . $exception->getLine()

            ]);
        }
    }

    #[Route('/api/{id}/invoices', name: 'get_invoices', methods: 'get')]
    public function getInvoices(ProjectRepository $repository, $id): Response
    {
        try {
            $project = $repository->find($id);
            if (!$project) {
                return $this->json([
                    'state' => 'NDF',
                    'value' => 'project'
                ]);
            }
            if ($project->getOwner() != $this->getUser() && !$project->hasUserInUserAuthorised($this->getUser())) {
                return $this->json([
                    'state' => 'FO',
                    'value' => 'project'
                ]);
            }
            if ($project->getState() == 'deleted') {
                return $this->json([
                    'state' => 'DD',
                    'value' => 'project'
                ]);
            }
            $data = [];
            foreach ($project->getInvoices() as $invoice) {
                $data[] = $this->getDataInvoice($invoice);
            }
            return $this->json([
                'state' => 'OK',
                'value' => $data]);
        } catch (\Exception $exception) {
            $this->logService->createLog('ERROR', ' Internal Servor Error at |' . $exception->getFile() . ' | line |' . $exception->getLine());


            return $this->json([
                'state' => 'ISE',
                'value' => ' Internal Servor Error : ' . $exception->getMessage() . ' at |' . $exception->getFile() . ' | line |' . $exception->getLine()

            ]);
        }

    }

    #[Route('/api/invoices/of/client/{id}', name: 'get_invoices_of_client', methods: 'get')]
    public function getInvoicesOfClient(ClientRepository $repository, InvoiceRepository $invoiceRepository, $id): Response
    {
        try {
            $client = $repository->find($id);
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
            if ($client->getState() == 'deleted') {
                return $this->json([
                    'state' => 'DD',
                    'value' => 'client'
                ]);
            }
            $data = [];
            foreach ($invoiceRepository->findInvoicesOfClient() as $invoice) {
                $data[] = $this->getDataInvoiceForClient($invoice);

            }
            return $this->json([
                'state' => 'OK',
                'value' => $data]);
        } catch (\Exception $exception) {
            $this->logService->createLog('ERROR', ' Internal Servor Error at |' . $exception->getFile() . ' | line |' . $exception->getLine());


            return $this->json([
                'state' => 'ISE',
                'value' => ' Internal Servor Error : ' . $exception->getMessage() . ' at |' . $exception->getFile() . ' | line |' . $exception->getLine()

            ]);
        }

    }

    #[Route('/api/invoice/delete/{id}', name: 'delete_invoice', methods: 'delete')]
    public function delete($id, InvoiceRepository $repository, EntityManagerInterface $manager): Response
    {
        try {
            $invoice = $repository->findOneBy(['number'=>$id]);
            if (!$invoice) {
                return $this->json([
                    'state' => 'NDF',
                    'value' => 'invoice'
                ]);
            }
            if ($invoice->getProject()->getOwner() != $this->getUser() && !$invoice->getProject()->hasUserInUserAuthorised($this->getUser())) {
                return $this->json([
                    'state' => 'FO',
                    'value' => 'invoice'
                ]);
            }
            if ($invoice->getProject()->getState() == 'deleted') {
                return $this->json([
                    'state' => 'DD',
                    'value' => 'project'
                ]);
            }
            $message = ' Delete Invoice (' . $invoice->getId() . ') for project  Project (' . $invoice->getProject()->getId() . ':' . $invoice->getProject()->getName() . ') for client (' . $invoice->getProject()->getClient()->getId() . ' | ' . $invoice->getProject()->getClient()->getFirstName() . ' ' . $invoice->getProject()->getClient()->getLastName() . '), action by ' . $this->getUser()->getEmail();
            $manager->remove($invoice);
            $manager->flush();
            $this->logService->createLog('DELETE', $message);
            return $this->json(['state' => 'OK']);
        } catch (\Exception $exception) {
            $this->logService->createLog('ERROR', ' Internal Servor Error at |' . $exception->getFile() . ' | line |' . $exception->getLine());


            return $this->json([
                'state' => 'ISE',
                'value' => ' Internal Servor Error : ' . $exception->getMessage() . ' at |' . $exception->getFile() . ' | line |' . $exception->getLine()

            ]);
        }

    }

    public function getDataInvoice($invoice)
    {
        $client = $invoice->getProject()->getCLient();
        return [
            'id' => $invoice->getId(),
            'price' => $invoice->getPrice(),
            'description' => $invoice->getDescription(),
            'date' => $this->dateService->formateDate($invoice->getDate()),
            'project_id' => $invoice->getProject()->getId(),
            'number' => $invoice->getNumber(),
            'client' => [
                "firstName" => $client->getFirstName(),
                "lastName" => $client->getLastName(),
            ],
            'payed' => $invoice->isPayed(),

        ];
    }

    public function getDataInvoiceForClient($invoice)
    {
        return [
            'id' => $invoice->getNumber(),
            'reason' => $invoice->getDescription(),
            'date' => $this->dateService->formateDate($invoice->getDate()),
            'projectName' => $invoice->getProject()->getName(),
            'number' => $invoice->getNumber(),

            'payed' => $invoice->isPayed(),

        ];
    }
}
