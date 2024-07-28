<?php

namespace App\Controller;

use App\Entity\Client;
use App\Entity\Invoice;
use App\Repository\ClientRepository;
use App\Repository\InvoiceRepository;
use App\Repository\ProjectRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ApiInvoiceController extends AbstractController
{
    #[Route('/api/invoice/new', name: 'new_invoice', methods: 'post')]
    public function index(Request $request, EntityManagerInterface $manager, ClientRepository $clientRepository, ProjectRepository $projectRepository): Response
    {
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

            if (!isset($data['project_id']) || empty(trim($data['project_id']))) {
                return $this->json([
                    'state' => 'NED',
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
            if (!$project->getOwner() == $this->getUser()) {
                return $this->json([
                    'state' => 'FO',
                    'value' => 'project'
                ]);
            }
            $invoice->setProject($project);


            if (isset($data['price']) && !empty(trim($data['price']))) {
                $isValid = $data['price'] > 0 && is_numeric($data['price']);
                if(!$isValid){
                    return $this->json([
                        'state' => 'IDT',
                        'value' => 'price'
                    ]);
                }
                $invoice->setPrice($data['price']);
            }
            $invoice->setDate(new \DateTime());
            $invoice->setOwner($this->getUser());
            $manager->persist($invoice);
            $manager->flush();


            return $this->json([
                'state' => 'OK',
                'value' => $this->getDataInvoice($invoice)
            ]);
        }
        return $this->json(['state' => 'ND']);
    }

    #[
        Route('/api/invoice/edit/{id}', name: 'edit_invoice', methods: 'post')]
    public function edit($id, InvoiceRepository $invoiceRepository, Request $request, EntityManagerInterface $manager, ClientRepository $clientRepository, ProjectRepository $projectRepository): Response
    {
        $invoice = $invoiceRepository->find($id);
        if (!$invoice) {
            return $this->json([
                'state' => 'NDF',
                'value' => 'invoice'
            ]);
        }

        $data = json_decode($request->getContent(), true);

        if ($data) {

            if (!isset($data['description']) || empty(trim($data['description']))) {
                return $this->json([
                    'state' => 'NED',
                    'value' => 'description'
                ]);
            }
            if (isset($data['price']) && !empty(trim($data['price']))) {
                $isValid = $data['price'] > 0 && is_numeric($data['price']);
                if(!$isValid){
                    return $this->json([
                        'state' => 'IDT',
                        'value' => 'price'
                    ]);
                }
                $invoice->setPrice($data['price']);
            }

            $invoice->setDescription($data['description']);
            $invoice->setDate(new \DateTime());
            $invoice->setOwner($this->getUser());
            $manager->persist($invoice);
            $manager->flush();


            return $this->json([
                'state' => 'OK',
                'value' => $this->getDataInvoice($invoice)
            ]);
        }
        return $this->json([
            'state' => 'ND'
        ]);
    }

    public function getDataInvoice($invoice)
    {
        return [
            'id' => $invoice->getId(),
            'price' => $invoice->getPrice(),
            'description' => $invoice->getDescription(),
            'date' => $invoice->getDate(),
            'project_id' => $invoice->getProject()->getId(),
            'owner' => $invoice->getOwner()->getEmail(),

        ];
    }
}
