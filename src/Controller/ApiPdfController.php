<?php

namespace App\Controller;

use App\Form\PdfType;
use App\Repository\PdfRepository;
use App\Repository\ProjectRepository;
use App\Service\DateService;
use App\Service\LogService;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;
use Knp\Snappy\Pdf;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ApiPdfController extends AbstractController
{
    private DateService $dateService;
    private LogService $logService;

    public function __construct(LogService $logService, DateService $dateService)
    {
        $this->logService = $logService;
        $this->dateService = $dateService;
    }

    #[Route('/upload/pdf', name: 'upload_pdf_without_token', methods: ['post'])]
    public function uploadPdf(Request $request): Response
    {
        try {

            $file = $request->files->get("pdf");
            if (!$file) {
                return $this->json([
                    'state' => 'NED',
                    'value' => 'file'
                ]);
            }
            $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $newFilename = $originalFilename . '-' . uniqid() . '.' . $file->guessExtension();

            try {
                $file->move('pdf', $newFilename);
                $this->logService->createLog('ACTION','Uploaded File by '.$this->getUser()->getEmail().' name : '.$newFilename,null);
                return $this->json([
                    'state' => 'OK',

                ]);
            } catch (\Exception $exception) {
                return $this->json([
                    'state' => 'ISE',
                    'value' => ' Internal Servor Error : ' . $exception->getMessage() . ' at |' . $exception->getFile() . ' | line |' . $exception->getLine()

                ]);
            }


        } catch (\Exception $exception) {
            return $this->json([
                'state' => 'ISE',
                'value' => ' Internal Servor Error : ' . $exception->getMessage() . ' at |' . $exception->getFile() . ' | line |' . $exception->getLine()

            ]);
        }
    }

    #[Route('/api/project/{id}/specifications/upload/pdf', name: 'upload_pdf', methods: ['post'])]
    public function uploadPdfSpecification($id, PdfRepository $pdfRepository, ProjectRepository $projectRepository, Request $request, EntityManagerInterface $manager): Response
    {
        try {
            $project = $projectRepository->find($id);
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

            $file = $request->files->get("pdf");
            if (!$file) {
                return $this->json([
                    'state' => 'NED',
                    'value' => 'file'
                ]);
            }
            $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);


            $newFilename = $originalFilename . '-' . uniqid() . '.' . $file->guessExtension();
            $pdf = $pdfRepository->findOneBy(['project' => $project, 'type' => 'SPECIFICATION', 'owner' => $this->getUser()]);
            if ($pdf) {
                return $this->json([
                    'state' => 'NU',
                    'value' => 'pdf'
                ]);
            }
            $pdf = new \App\Entity\Pdf();
            $pdf->setOwner($this->getUser());
            $pdf->setType('SPECIFICATION');
            $pdf->setFileName($newFilename);
            $pdf->setProject($project);
            $manager->persist($pdf);
            $manager->flush();

            $filePath =$this->getParameter('upload_directory') . '/' . $pdf->getFileName();

            try {
                $file->move('pdf', $newFilename);
                return $this->json([
                    'state' => 'OK',
                    'value' => $filePath

                ]);
            } catch (\Exception $exception) {
                return $this->json([
                    'state' => 'ISE',
                    'value' => ' Internal Servor Error : ' . $exception->getMessage() . ' at |' . $exception->getFile() . ' | line |' . $exception->getLine()

                ]);
            }

        } catch (\Exception $exception) {
            return $this->json([
                'state' => 'ISE',
                'value' => ' Internal Servor Error : ' . $exception->getMessage() . ' at |' . $exception->getFile() . ' | line |' . $exception->getLine()

            ]);
        }
    }

    #[Route('/api/get/{id}/specifications', name: 'get_specification_pdf', methods: ['get'])]
    public function getPdfSpecification(ProjectRepository $projectRepository, PdfRepository $pdfRepository, $id, Request $request, EntityManagerInterface $manager): Response
    {
        try {
            $project = $projectRepository->find($id);
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
            $pdf = $pdfRepository->findOneBy(['project' => $project, 'type' => 'SPECIFICATION', 'owner' => $this->getUser()]);
            if (!$pdf) {
                return $this->json([
                    'state' => 'NDF',
                    'value' => 'pdf'
                ]);
            }
           $filePath =$this->getParameter('upload_directory') . '/' . $pdf->getFileName();


            if (!file_exists($filePath)) {
                return $this->json([
                    'state' => 'NDF',
                    'value' => 'pdf'
                ]);
            }
            try {
                return $this->json([
                    'state' => 'OK',
                    'value' => $filePath
                ]);
            } catch (\Exception $exception) {
                return $this->json([
                    'state' => 'ISE',
                    'value' => ' Internal Servor Error : ' . $exception->getMessage() . ' at |' . $exception->getFile() . ' | line |' . $exception->getLine()

                ]);
            }

        } catch (\Exception $exception) {
            return $this->json([
                'state' => 'ISE',
                'value' => ' Internal Servor Error : ' . $exception->getMessage() . ' at |' . $exception->getFile() . ' | line |' . $exception->getLine()

            ]);
        }
    }

    #[Route('/api/remove/{id}/specifications', name: 'remove_specification_pdf', methods: ['delete'])]
    public function removePdfSpecification(ProjectRepository $projectRepository, PdfRepository $pdfRepository, $id, Request $request, EntityManagerInterface $manager): Response
    {
        try {
            $project = $projectRepository->find($id);
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

            $pdf = $pdfRepository->findOneBy(['project' => $project, 'type' => 'SPECIFICATION', 'owner' => $this->getUser()]);
            if (!$pdf) {
                return $this->json([
                    'state' => 'NDF',
                    'value' => 'pdf'
                ]);
            }
            $filePath =$this->getParameter('upload_directory') . '/' . $pdf->getFileName();


            if (!file_exists($filePath)) {
                return $this->json([
                    'state' => 'NDF',
                    'value' => 'pdf'
                ]);
            }

            $filePath = 'pdf/' . $pdf->getFileName();
            if (unlink($filePath)) {
                $this->logService->createLog('DELETE','Delete File by '.$this->getUser()->getEmail().' name : '. $pdf->getFileName(),null);

                $manager->remove($pdf);
                $manager->flush();
                return $this->json([
                    'state' => 'OK'
                ]);
            } else {
                return $this->json([
                    'state' => 'ISE',
                    'value' => 'Failed to remove pdf'
                ]);
            }


        } catch (\Exception $exception) {
            return $this->json([
                'state' => 'ISE',
                'value' => ' Internal Servor Error : ' . $exception->getMessage() . ' at |' . $exception->getFile() . ' | line |' . $exception->getLine()

            ]);
        }
    }

    #[Route('/pdf', name: 'app_api_pdf', methods: ['GET'])]
    public function downloadPdf(Pdf $knpSnappyPdf): PdfResponse
    {
        $html = $this->getPdf();

        return new PdfResponse(
            $knpSnappyPdf->getOutputFromHtml($html),
            'file.pdf'
        );
    }

    #[Route('/pdftest', name: 'app_pdf')]
    public function renderPdf(Pdf $knpSnappyPdf)
    {

        $html = $this->getPdf();

        $pdfFilePath = $this->getParameter('kernel.project_dir') . '/public/uploads/pdf/' . uniqid() . '.pdf';

        // Générer le PDF
        $knpSnappyPdf->generateFromHtml($html, $pdfFilePath);

        return $this->render('/invoice.html.twig', [

            'pdfPath' => '/uploads/pdf/' . basename($pdfFilePath),
        ]);
    }

    public function getPdf()
    {
        return $this->renderView('component/invoice.html.twig', [
            'workerName' => 'Mey Detour',
            'workerEmail' => 'info@meydetour.fr',
            'workerPhone' => '07 82 40',
            'workerAddress' => '14 allée etc',
            'workerSiret' => null,
            'workerNaf' => null,
            'workerTVA' => null,

            'clientName' => 'Mey Detour',
            'clientEmail' => 'info@meydetour.fr',
            'clientPhone' => '07 82 40',
            'clientAddress' => '14 allée etc',
            'clientSiret' => 'XXX',
            'clientNaf' => 'xxxx',
            'clientTVA' => 'xxx',

            'invoiceNumber' => '25625',
            'invoiceDate' => '01/01/2017', /*$this->dateService->formateDate(*/
            'projectName' => 'F&B',

            "details" => [
                [
                    "title" => 'Conception interface',
                    "compositions" => [
                        "Analyse des besoins utilisateurs",
                        "Wireframing et Prototypage",
                        "Design Visuel et conception de l'identité visuelle",
                        "Réalisation de maquettes haute fidélité",
                        "Tests Utilisateurs pour valider l'ergonomie."
                    ],
                    "quantity" => 1,
                    "price" => 50.00
                ], [
                    "title" => 'Développement backend',
                    "compositions" => [
                        "Système d’envoi de mail ( intégration du serveur de mails, creation et personnalisation des templates d'emai, gestation des envois et suivi des erreurs.)",
                        "Gestion d’images et de compte (Implementation du téléchargement et stockage sécurisé des images, Automatisation du redimensionnement et optimisation des images, caricaturisation et gestion des permissions des utilisateurs.)",
                    ],
                    "quantity" => 1,
                    "price" => 500.00
                ], [
                    "title" => 'Développement  front end',
                    "compositions" => [
                        "Utilisation de frameworks modernes (React, Angular, Vue.js)",
                        "Application des bonnes pratiques ( SEO )",
                        "Conception de l'interface adaptée aux différents dispositifs (mobile, tablette, desktop).",
                        "Tests de compatibilité cross-browser et cross-device.",
                    ],
                    "quantity" => 1,
                    "price" => 100.00
                ],
                [
                    "title" => 'Conception interface',
                    "compositions" => [
                        "Analyse des besoins utilisateurs",
                        "Wireframing et Prototypage",
                        "Design Visuel et conception de l'identité visuelle",
                        "Réalisation de maquettes haute fidélité",
                        "Tests Utilisateurs pour valider l'ergonomie."
                    ],
                    "quantity" => 1,
                    "price" => 50.00
                ], [
                    "title" => 'Développement backend',
                    "compositions" => [
                        "Système d’envoi de mail ( intégration du serveur de mails, creation et personnalisation des templates d'emai, gestation des envois et suivi des erreurs.)",
                        "Gestion d’images et de compte (Implementation du téléchargement et stockage sécurisé des images, Automatisation du redimensionnement et optimisation des images, caricaturisation et gestion des permissions des utilisateurs.)",
                    ],
                    "quantity" => 1,
                    "price" => 500.00
                ], [
                    "title" => 'Développement  front end',
                    "compositions" => [
                        "Utilisation de frameworks modernes (React, Angular, Vue.js)",
                        "Application des bonnes pratiques ( SEO )",
                        "Conception de l'interface adaptée aux différents dispositifs (mobile, tablette, desktop).",
                        "Tests de compatibilité cross-browser et cross-device.",
                    ],
                    "quantity" => 1,
                    "price" => 100.00
                ],
                [
                    "title" => 'Conception interface',
                    "compositions" => [
                        "Analyse des besoins utilisateurs",
                        "Wireframing et Prototypage",
                        "Design Visuel et conception de l'identité visuelle",
                        "Réalisation de maquettes haute fidélité",
                        "Tests Utilisateurs pour valider l'ergonomie."
                    ],
                    "quantity" => 1,
                    "price" => 50.00
                ], [
                    "title" => 'Développement backend',
                    "compositions" => [
                        "Système d’envoi de mail ( intégration du serveur de mails, creation et personnalisation des templates d'emai, gestation des envois et suivi des erreurs.)",
                        "Gestion d’images et de compte (Implementation du téléchargement et stockage sécurisé des images, Automatisation du redimensionnement et optimisation des images, caricaturisation et gestion des permissions des utilisateurs.)",
                    ],
                    "quantity" => 1,
                    "price" => 500.00
                ], [
                    "title" => 'Développement  front end',
                    "compositions" => [
                        "Utilisation de frameworks modernes (React, Angular, Vue.js)",
                        "Application des bonnes pratiques ( SEO )",
                        "Conception de l'interface adaptée aux différents dispositifs (mobile, tablette, desktop).",
                        "Tests de compatibilité cross-browser et cross-device.",
                    ],
                    "quantity" => 1,
                    "price" => 100.00
                ],
                [
                    "title" => 'Conception interface',
                    "compositions" => [
                        "Analyse des besoins utilisateurs",
                        "Wireframing et Prototypage",
                        "Design Visuel et conception de l'identité visuelle",
                        "Réalisation de maquettes haute fidélité",
                        "Tests Utilisateurs pour valider l'ergonomie."
                    ],
                    "quantity" => 1,
                    "price" => 50.00
                ], [
                    "title" => 'Développement backend',
                    "compositions" => [
                        "Système d’envoi de mail ( intégration du serveur de mails, creation et personnalisation des templates d'emai, gestation des envois et suivi des erreurs.)",
                        "Gestion d’images et de compte (Implementation du téléchargement et stockage sécurisé des images, Automatisation du redimensionnement et optimisation des images, caricaturisation et gestion des permissions des utilisateurs.)",
                    ],
                    "quantity" => 1,
                    "price" => 500.00
                ], [
                    "title" => 'Développement  front end',
                    "compositions" => [
                        "Utilisation de frameworks modernes (React, Angular, Vue.js)",
                        "Application des bonnes pratiques ( SEO )",
                        "Conception de l'interface adaptée aux différents dispositifs (mobile, tablette, desktop).",
                        "Tests de compatibilité cross-browser et cross-device.",
                    ],
                    "quantity" => 1,
                    "price" => 100.00
                ],
                [
                    "title" => 'Conception interface',
                    "compositions" => [
                        "Analyse des besoins utilisateurs",
                        "Wireframing et Prototypage",
                        "Design Visuel et conception de l'identité visuelle",
                        "Réalisation de maquettes haute fidélité",
                        "Tests Utilisateurs pour valider l'ergonomie."
                    ],
                    "quantity" => 1,
                    "price" => 50.00
                ], [
                    "title" => 'Développement backend',
                    "compositions" => [
                        "Système d’envoi de mail ( intégration du serveur de mails, creation et personnalisation des templates d'emai, gestation des envois et suivi des erreurs.)",
                        "Gestion d’images et de compte (Implementation du téléchargement et stockage sécurisé des images, Automatisation du redimensionnement et optimisation des images, caricaturisation et gestion des permissions des utilisateurs.)",
                    ],
                    "quantity" => 1,
                    "price" => 500.00
                ], [
                    "title" => 'Développement  front end',
                    "compositions" => [
                        "Utilisation de frameworks modernes (React, Angular, Vue.js)",
                        "Application des bonnes pratiques ( SEO )",
                        "Conception de l'interface adaptée aux différents dispositifs (mobile, tablette, desktop).",
                        "Tests de compatibilité cross-browser et cross-device.",
                    ],
                    "quantity" => 1,
                    "price" => 100.00
                ],

            ]]);
    }


}
