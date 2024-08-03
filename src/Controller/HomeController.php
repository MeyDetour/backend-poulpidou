<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{


    #[Route('/doc', name: 'all_routes', methods: 'get')]
    public function getAllRoutes(): Response
    {

        return $this->json([
            'account' => [
                [
                    'login' => '/api/login_check',
                    'methode' => 'post',
                    'parametres a mettre dans le body' => "username,password",
                    'renvoie' => 'un token',
                    'need token ? ' => false
                ],
                [
                    'register' => '/register',
                    'methode' => 'post',
                    'parametres a mettre dans le body' => "username,password",
                    'renvoie' => 'ok si utilisateur bien crée',
                    'need token ? ' => false
                ]
            ],
            'clients' => [
                [
                    'new client' => '/api/client/new',
                    'utilisation' => 'Créer une fiche client avec les parametres rentréer dans le body, les données sont automatiquement formaté pour etre en maj',
                    'methode' => 'post',
                    "renvoie" => "la fiche client",
                    'parametres a mettre dans le body' => "first_name,last_name,job,age,location,mail,phone,siret",
                    'need token ? ' => true],
                [
                    'get client' => '/api/client/{id du client}',
                    'methode' => 'get',
                    "renvoie" => "la fiche client",
                    'parametres a mettre dans le body' => null,
                    'utilisation' => "passer en parametre l'id du client pour obtenir les informations",
                    'need token ? ' => true],
                [
                    'get clients' => '/api/clients',
                    'methode' => 'get',
                    "renvoie" => "tous les clients associés au compte de l'utilisateur selon le parametre entré",
                    'parametres a mettre dans le body' => "display_deleted (boolean), order_by (string)",
                    'utilisation' => "passer en parametre l'id du client pour obtenir les informations et renvoie tous les clients actifs par défaut ou tous les clients (actifs et supprimés) si display_deleted = true | si order_by='name' les clients seront trié par le lastName puis firstName",
                    'need token ? ' => true],
                [
                    'edit client' => '/api/client/edit/{id du client}',
                    'methode' => 'put',
                    "renvoie" => "la fiche client",
                    'parametres a mettre dans le body' => "first_name,last_name,job,age,location,mail,phone,siret",
                    'utilisation' => "passer en parametre l'id du client et mettre dans le body les parametre a changer",
                    'need token ? ' => true],
                [
                    'delete ' => '/api/client/delete/{id}',
                    'methode' => 'delete',
                    "renvoie" => "ok si l'action a bien été faite",
                    'parametres a mettre dans le body' => null,
                    'utilisation' => "passer en parametre l'id du client pour mettre la fiche client sur state = 'deleted' ",
                    'need token ? ' => true],
                [
                    'delete force' => '/api/client/deleteforce/{id du client}',
                    'methode' => 'delete',
                    "renvoie" => "ok si l'action a bien été faite",
                    'parametres a mettre dans le body' => null,
                    'utilisation' => "passer en parametre l'id du client pour supprimer vraiment la fiche client",
                    'need token ? ' => true],
                [
                    'client projects' => '/api/client/{id}/projects',
                    'methode' => 'get',
                    "renvoie" => "liste des projets du client : id,name,startDate,endDate ( a toi de me dire les infos quil faut renvoyer sur le projet)",
                    'parametres a mettre dans le body' => null,
                    'utilisation' => "passer en parametre l'id du client pour obtenir la liste de ses projets",
                    'need token ? ' => true],
                [
                    'client currents projects' => '/api/client/{id}/currentProjects',
                    'methode' => 'post',
                    "renvoie" => "liste des projets courrents du client : id,name,startDate,endDate ( a toi de me dire les infos quil faut renvoyer sur le projet)",
                    'parametres a mettre dans le body' => "display_deleted (boolean) => true = afficher les projets supprimés",
                    'utilisation' => "passer en parametre l'id du client pour obtenir la liste de ses projets courrants",
                    'need token ? ' => true],
                [
                    'add  currents projects to client' => '/api/client/{id}/currentProjects/add',
                    'methode' => 'post',
                    "renvoie" => "ok si c'est bien passé",
                    'parametres a mettre dans le body' => "project_id",
                    'utilisation' => "passer en parametre l'id du client et mettre project_id dans le body pour ajouter un projet dans les projets courrents ",
                    'need token ? ' => true],
                [
                    'remove client currents projects' => '/api/client/{id}/currentProjects/remove',
                    'methode' => 'put',
                    "renvoie" => "ok si c'est bien passé",
                    'parametres a mettre dans le body' => "project_id",
                    'utilisation' => "passer en parametre l'id du client et mettre project_id dans le body pour enlever le projet des projets courrents ",
                    'need token ? ' => true],
                [
                    'edit note of project' => '/api/project/id/note',
                    'methode' => 'put',
                    "renvoie" => "renvoie les notes",
                    'parametres a mettre dans le body' => "names ( dico avec les clés de 0 à 4 contenant les noms) et content ( dico de clé 0 à 5 cpontenant le contenu)",
                    'utilisation' => "passer en parametre l'id du client et mettre project_id dans le body pour enlever le projet des projets courrents ",
                    'need token ? ' => true],

            ],
            "client Interface" => [
                [
                    'get data of project' => '/interface/{uuid}',
                    'methode' => 'get',
                    "renvoie" => "project(id,startDate,endDAte,price,maintenancePercentage), client(id,firstName,lastname)  avoir le chat et les messages",
                    'parametres a mettre dans le body' => "nothing",
                    'utilisation' => "nothin",
                    'need token ? ' => false],
                [
                    'set client online/offline' => '/interface/project/{id}/online',
                    'methode' => 'post',
                    "renvoie" => "ok si c'est bien passé",
                    'parametres a mettre dans le body' => "online(boolean)",
                    'utilisation' => "a appeller quand l'utilisateur se connecte et se deconnecte ",
                    'need token ? ' => false],

            ],
            'projects' => [
                [
                    'new project' => '/api/project/new',
                    'utilisation' => 'Créer une projet avec les parametres rentrér dans le body',
                    'methode' => 'post',
                    "renvoie" => "le projet (id,name,figmaLink,githubLink,state,startDate,endDate,totalPrice,client_id,owner...+",
                    'parametres a mettre dans le body' => "name(*),figmaLink,githubLink,state,startDate,endDate,totalPrice,client_id,estimatedPrice,isPaying,database,maquette,maintenance,type,framework,options,devices,needTemplate,maintenancePercentage",
                    'need token ? ' => true],
                [
                    'get project' => '/api/project/{id du client}',
                    'methode' => 'get',
                    "renvoie" => "le projet",
                    'parametres a mettre dans le body' => null,
                    'utilisation' => "passer en parametre l'id du projet pour obtenir les informations",
                    'need token ? ' => true],
                [
                    'get projects' => '/api/clients',
                    'methode' => 'get',
                    "renvoie" => "tous les projet associés au compte de l'utilisateur selon le parametre entré",
                    'parametres a mettre dans le body' => "display_deleted (boolean)",
                    'utilisation' => "passer en parametre l'id du projet pour obtenir les informations et renvoie tous les projets actifs par défaut ou tous les projets (actifs et supprimés) si display_deleted = true ",
                    'need token ? ' => true],
                [
                    'edit project' => '/api/project/edit/{id du client}',
                    'methode' => 'put',
                    "renvoie" => "le projet",
                    'parametres a mettre dans le body' => "name(*),figmaLink,githubLink,state,startDate,endDate,totalPrice,client_id,estimatedPrice,isPaying,database,maquette,maintenance,type,framework,options,devices,maintenancePercentage",
                    'utilisation' => "passer en parametre l'id du project et mettre dans le body les parametre a changer",
                    'need token ? ' => true],
                [
                    'delete ' => '/api/project/delete/{id}',
                    'methode' => 'delete',
                    "renvoie" => "ok si l'action a bien été faite",
                    'parametres a mettre dans le body' => null,
                    'utilisation' => "passer en parametre l'id du project pour mettre la fiche project sur state = 'deleted' ",
                    'need token ? ' => true],
                [
                    'delete force' => '/api/project/deleteforce/{id du project}',
                    'methode' => 'delete',
                    "renvoie" => "ok si l'action a bien été faite",
                    'parametres a mettre dans le body' => null,
                    'utilisation' => "passer en parametre l'id du project pour supprimer vraiment la fiche project",
                    'need token ? ' => true],
                [
                    'editer les notes du projet' => '/api/project/{id du projet}/note',
                    'methode' => 'put',
                    "renvoie" => "renvoie les notes modifiés",
                    'parametres a mettre dans le body' => "notes,remembers ",
                    'utilisation' => "Créer une note, la modifier ou la renvoie",
                    'need token ? ' => true],
            ],
            'user' => [
                [
                    'get user connected' => '/api/me',
                    'methode' => 'get',
                    "renvoie" => "id,mail,pho,esiret,address,firstName,lastName",
                    'parametres a mettre dans le body' => null,
                    'utilisation' => "permet d'avoir l'utilisateur a partir du token",
                    'need token ? ' => true],
                [
                    'get user with id' => '/api/user/{id}',
                    'methode' => 'get',
                    "renvoie" => "id,mail,pho,esiret,address,firstName,lastName",
                    'parametres a mettre dans le body' => null,
                    'utilisation' => "permet d'avoir l'utilisateur a partir d'un id",
                    'need token ? ' => true],
            ],
            'invoice' => [
                [
                    'new invoice' => '/api/invoice/new',
                    'methode' => 'post',
                    "renvoie" => "la facture (id,price,description,date,project_id,owner)",
                    'parametres a mettre dans le body' => "price,description,project_id",
                    'utilisation' => "permet de créer une nouvelle facture liée a un projet ",
                    'need token ? ' => true],
                [
                    'edit invoice' => '/api/invoice/edit/{id}',
                    'methode' => 'post',
                    "renvoie" => "la facture (id,price,description,date,project_id,client_id,number,owner)",
                    'parametres a mettre dans le body' => "price,description",
                    'utilisation' => "permet de modifier une facture liée a un projet ",
                    'need token ? ' => true],
                [
                    'get all invoices' => '/api/invoices',
                    'methode' => 'get',
                    "renvoie" => "liste of factures (id,price,description,date,project_id,client_id,number,owner)",
                    'parametres a mettre dans le body' => "",
                    'utilisation' => "permet d'btenir toutes les factures crées ",
                    'need token ? ' => true],
                [
                    'get all of a client' => '/api/invoices/of/client/{id of cient}',
                    'methode' => 'get',
                    "renvoie" => "liste of factures (id,price,description,date,project_id,client_id,number,owner)",
                    'parametres a mettre dans le body' => "nothing",
                    'utilisation' => "obtenir les factures d'un seul client",
                    'need token ? ' => true],
                [
                    'to pay invoice' => '/api/invoice/{id of invoice}/pay',
                    'methode' => 'PUT',
                    "renvoie" => "ok",
                    'parametres a mettre dans le body' => "nothing",
                    'utilisation' => "marqué une facture comme payé",
                    'need token ? ' => true],
            ],
            'logs' => [
                [
                    'get logs' => '/api/logs',
                    'methode' => 'get',
                    "renvoie" => "les logs (id,date,author,message,error,patch)",
                    'parametres a mettre dans le body' => "les logs se generent automatiquement",
                    'utilisation' => "récupere tous les logs",
                    'need token ? ' => true],

            ],
            'pdf' => [
                [
                    'upload one pdf for technbical specification' => '/api/project/{id du project}/specifications/upload/pdf',
                    'methode' => 'post',
                    "renvoie" => "ok et le chemin du pdf",
                    'parametres a mettre dans le body' => "form-data avec la clé pdf et l'id du project ",
                    'utilisation' => "uploader le fichier pdf d'un cahier des charges ",
                    'need token ? ' => true],
                [
                    'get pdf of technical Specification' => '/api/get/{id}/specifications',
                    'methode' => 'get',
                    "renvoie" => " et le chemin du pdfr",
                    'parametres a mettre dans le body' => " l'id du project ",
                    'utilisation' => "obtenir le pdf d'un cahier des charges ",
                    'need token ? ' => true],
                [
                    'delete pdf of technical Specification' => '/api/remove/{id}/specifications',
                    'methode' => 'delete',
                    "renvoie" => " ok",
                    'parametres a mettre dans le body' => " l'id du project ",
                    'utilisation' => "supprimer le pdf d'un cahier des charges ",
                    'need token ? ' => true],

            ],
            'note' => [
                [
                    'edit les note' => '/api/edit/note',
                    'methode' => 'post',
                    "renvoie" => "renvoie la note modifié",
                    'parametres a mettre dans le body' => "notes,remembers ",
                    'utilisation' => "Créer une note, la modifier ou la renvoie",
                    'need token ? ' => true],
                [
                    'get les note' => '/api/note',
                    'methode' => 'get',
                    "renvoie" => "renvoie la note soit vide soit pleine",
                    'parametres a mettre dans le body' => "nothin",
                    'utilisation' => "renvoie la note",
                    'need token ? ' => true],


            ],
            "search" => [
                [
                    'search project' => '/api/search/project',
                    'methode' => 'get',
                    "renvoie" => "renvoie les projets trouvés",
                    'parametres a mettre dans le body' => "searchTerm",
                    'utilisation' => "chercher les projets",
                    'need token ? ' => true],
                [
                    'search client' => '/api/search/client',
                    'methode' => 'get',
                    "renvoie" => "renvoie les clients trouvés",
                    'parametres a mettre dans le body' => "searchTerm",
                    'utilisation' => "chercher les clients",
                    'need token ? ' => true],
            ],
            "message" => [
                [
                    'gets all chats' => '/api/chats',
                    'methode' => 'get',
                    "renvoie" => "renvoie les chats dans lequels vous vous trouvez",
                    'parametres a mettre dans le body' => "nothin",
                    'utilisation' => "avoir les chats",
                    'need token ? ' => true],
                [
                    'get one chat' => '/api/chat/{id du chat}',
                    'methode' => 'get',
                    "renvoie" => "renvoie le chat avec les données du client et les messages",
                    'parametres a mettre dans le body' => "nothing",
                    'utilisation' => "avoir une conversation",
                    'need token ? ' => true],
                [
                    'envoyer un message depuis l interface client' => '/message',
                    'methode' => 'post',
                    "renvoie" => "ok",
                    'parametres a mettre dans le body' => "id (uuid du projet) , content",
                    'utilisation' => "envoyer un message",
                    'need token ? ' => false],
                [
                    'envoyer un message depuis l app' => '/api/message',
                    'methode' => 'post',
                    "renvoie" => "ok",
                    'parametres a mettre dans le body' => "id (id du projet) , content",
                    'utilisation' => "envoyer un message",
                    'need token ? ' => false],
            ],
            "setting"=>[
                [
                    "recuperer les parametre de l'utilisateur " => '/api/message',
                    'methode' => 'post',
                    "renvoie" => "ok",
                    'parametres a mettre dans le body' => "id (id du projet) , content",
                    'utilisation' => "envoyer un message",
                    'need token ? ' => false],
            ]

        ]);
    }
    /* #[Route('/api/template', name: 'template', methods: 'post')]
     public function edit( Request $request, EntityManagerInterface $manager, ClientRepository $clientRepository, ProjectRepository $projectRepository): Response
     {
         try {
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

     public function getDataInvoice($thing)
     {
         return [
             'id' => $thing->getId(),
             'price' => $thing->getPrice(),
             'description' => $thing->getDescription(),
             'date' => $thing->getDate(),
             'project_id' => $thing->getProject()->getId(),
             'owner' => $thing->getOwner()->getEmail(),

         ];
     }*/
}
