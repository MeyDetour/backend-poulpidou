<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(): Response
    {


        $invoicesData = [[
            'id' => '1',
            'price' => 12.5,
            'description' => 'jspppp',
            'date' => '2025/60/03',
            'project_id' => 1,
            'nbTitles' => 3,
            'number' => 'af8441df84',
            'client' => [
                "firstName" => 'Gaelle',
                "lastName" => 'Ghizoli',
            ],
            'payed' => true,

        ], [
            'id' => '1',
            'price' => 565465,
            'nbTitles' => 3,
            'date' => '2024/06/03',
            'project_id' => 1,

            'number' => 'gf8d552gf',
            'client' => [
                "firstName" => 'Gaelle',
                "lastName" => 'Ghizoli',
            ],
            'payed' => true,

        ], [
            'id' => '1',
            'price' => 1250,
            'description' => 'jspppp',

            'nbTitles' => 3,
            'date' => '2024/06/03',
            'number' => '8z4f4d15f',
            'project_id' => 1,
            'client' => [
                "firstName" => 'Gaelle',
                "lastName" => 'Ghizoli',
            ],
            'payed' => false,

        ]];
        return $this->render('/component/invoiceList.html.twig',
            ['invoices' => $invoicesData]);
    }

    #[Route('/doc', name: 'all_routes', methods: 'get')]
    public function getAllRoutes(): Response
    {

        return $this->json([
            'account' => [
                [
                    'login' => '/api/login_check',
                    'methode' => 'post',
                    'parametres a mettre dans le body' =>
                        [
                            "username" => "username",
                            "password" => "password",
                        ],
                    'renvoie' => [
                        "token" => null
                    ],
                    'need token ? ' => false
                ],
                [
                    'register' => '/register',
                    'methode' => 'post',
                    'parametres a mettre dans le body' => [
                        "username" => "username",
                        "password" => "password",
                    ],
                    'renvoie' => 'ok si utilisateur bien crée',
                    'need token ? ' => false
                ], [
                    '(non testé) export data' => '/api/export/data',
                    'methode' => 'get',
                    'parametres a mettre dans le body' => [
                    ],
                    'renvoie' => 'telecharge un fichier contenant les donnée du compte',
                    'need token ? ' => true
                ], [
                    '(non testé) import data' => '/api/import/data',
                    'methode' => 'get',
                    'parametres a mettre dans le body' => [
                        'save' => "fichier d'importation .poulpidou "
                    ],
                    'renvoie' => "ok si c'est bon sinon ASFO",
                    'need token ? ' => true
                ]
            ],
            'clients' => [
                [
                    'new client' => '/api/client/new',
                    'utilisation' => 'Créer une fiche client avec les parametres rentréer dans le body, les données sont automatiquement formaté pour etre en maj',
                    'methode' => 'post',
                    "renvoie :" => [
                        "id" => null,
                        "firstName" => null,
                        "lastName" => null,
                        "job" => null,
                        "age" => null,
                        "location" => null,
                        "mail" => null,
                        "siret" => null,
                        "phone" => null,
                        "createdAt" => null,
                        "state" => null,
                        "online" => null,
                        "note"=>null
                    ],
                    'parametres a mettre dans le body' => [
                        "firstName" => null,
                        "lastName" => null,
                        "job" => null,
                        "age" => null,
                        "location" => null,
                        "mail" => null,
                        "siret" => null,
                        "phone" => null,
                    ],
                    'need token ? ' => true],
                [
                    'get client' => '/api/client/{id du client}',
                    'methode' => 'get',
                    "renvoie :" => [
                        "id" => null,
                        "firstName" => null,
                        "lastName" => null,
                        "job" => null,
                        "age" => null,
                        "location" => null,
                        "mail" => null,
                        "siret" => null,
                        "phone" => null,
                        "createdAt" => null,
                        "state" => null,
                        "online" => null,
                        "note"=>null

                    ],
                    'parametres a mettre dans le body' => null,
                    'utilisation' => "passer en parametre l'id du client pour obtenir les informations",
                    'need token ? ' => true],
                [
                    'get clients' => '/api/clients',
                    'methode' => 'get',
                    "renvoie :" => [
                        "read" => [],
                        "unread" => [
                            [
                                "id" => 2,
                                "name" => "ITOW Chat",
                                "date" => "20/08/2024",
                                "client" => [
                                    "id" => 1,
                                    "firstName" => "Coralie",
                                    "lastName" => "DUPONT"
                                ],
                                "users" => [
                                    [
                                        "id" => 1,
                                        "firstName" => null,
                                        "lastName" => null,
                                        "email" => "meydetour@gmail.com"
                                    ]
                                ]
                            ]
                        ]],
                    'parametres a mettre dans le body' => [
                        "displayDeleted" => null,
                        "order_by" => null,
                    ],
                    'utilisation' => "passer en parametre l'id du client pour obtenir les informations et renvoie tous les clients actifs par défaut ou tous les clients (actifs et supprimés) si display_deleted = true | si order_by='name' les clients seront trié par le lastName puis firstName",
                    'need token ? ' => true],
                [
                    'edit client' => '/api/client/edit/{id du client}',
                    'methode' => 'put',
                    "renvoie :" => [
                        "id" => null,
                        "firstName" => null,
                        "lastName" => null,
                        "job" => null,
                        "age" => null,
                        "location" => null,
                        "mail" => null,
                        "siret" => null,
                        "phone" => null,
                        "createdAt" => null,
                        "state" => null,
                        "online" => null,
                        "note"=>null,

                    ],
                    'parametres a mettre dans le body' => [
                        "firstName" => null,
                        "lastName" => null,
                        "job" => null,
                        "age" => null,
                        "location" => null,
                        "mail" => null,
                        "siret" => null,
                        "phone" => null,
                    ],
                    'utilisation' => "passer en parametre l'id du client et mettre dans le body les parametre a changer",
                    'need token ? ' => true],
                [
                    'delete ' => '/api/client/delete/{id}',
                    'methode' => 'delete',
                    "renvoie :" => "ok si l'action a bien été faite",
                    'parametres a mettre dans le body' => "nothing",
                    'utilisation' => "passer en parametre l'id du client pour mettre la fiche client sur state = 'deleted' ",
                    'need token ? ' => true],
                [
                    'delete force' => '/api/client/deleteforce/{id du client}',
                    'methode' => 'delete',
                    "renvoie :" => "ok si l'action a bien été faite",
                    'parametres a mettre dans le body' => null,
                    'utilisation' => "passer en parametre l'id du client pour supprimer vraiment la fiche client",
                    'need token ? ' => true],
                [
                    'get projects of client' => '/api/client/{id du client}/projects',
                    'methode' => 'get',
                    "renvoie :" => [[
                        "id" => null,
                        "name" => null,
                        "uuid" => null,
                        "cratedAt" => null,
                        'totalTasks' => null,
                        'doneTasks' => null,
                    ], [
                        "id" => null,
                        "name" => null,
                        "uuid" => null,
                        "cratedAt" => null,
                        'totalTasks' => null,
                        'doneTasks' => null,
                    ]],
                    'parametres a mettre dans le body' => null,
                    'utilisation' => "passer en parametre l'id du client pour obtenir la liste de ses projets",
                    'need token ? ' => true],

                [
                    'get all chats of client' => '/api/client/{id du client}/chats',
                    'methode' => 'get',
                    "renvoie :" => [
                        [
                            'id' => 'id du chat',
                            'name' => 'nom du chat',
                            'lastMessage' => [
                                "content" => 'contenu du dernier message',
                                "date" => 'date du dernier message',
                            ]
                        ]
                    ],
                    'parametres a mettre dans le body' => null,
                    'utilisation' => "afficher dans la fiche client la liste de chats associé",
                    'need token ? ' => true],


            ],
            "client Interface" => [
                [
                    'get data of project' => '/interface/{uuid}',
                    'methode' => 'get',
                    "renvoie :" => [
                        "lang" => null,
                        "modalités" => [
                            'payments' => []
                            ,
                            'delayDays' => null,
                            'installmentPayments' => null,
                            'freeMaintenance' => null,
                            'interfaceLangage' => null,

                        ],
                        'project' => [
                            'startDate' => null,
                            'endDate' => null,
                            'price' => null,
                            'maintenancePercentage' => null,

                        ],
                        'client' => [
                            'id' => null,
                            "firstName" => null,
                            "lastName" => null,

                        ],
                        'parametres a mettre dans le body' => "nothing",
                        'utilisation' => "nothin",
                        'need token ? ' => false],
                ],
                ['set client online/offline' => '/interface/project/{uuid}/online',
                    'methode' => 'post',
                    "renvoie :" => "ok si c'est bien passé",
                    'parametres a mettre dans le body' => [
                        'online' => "boolean"
                    ],
                    'utilisation' => "a appeller quand l'utilisateur se connecte et se deconnecte ",
                    'need token ? ' => false],

            ],
            'projects' => [
                [
                    'new project' => '/api/project/new',
                    'utilisation' => 'Créer une projet avec les parametres rentrér dans le body',
                    'methode' => 'post',
                    "renvoie :" => [
                        "totalPrice" => null,
                        "estimatedPrice" => null,
                        "maintenancePercentage" => 10,
                        "members" => [
                            [
                                'email' => null,
                                'firstName' => null,
                                'lastName' => null,
                            ], [
                                'email' => null,
                                'firstName' => null,
                                'lastName' => null,
                            ]
                        ],
                        "rules" => [
                            'canEditInvoices' => "boolean",
                            'canSeeClientProfile' => "boolean",
                        ],
                        "identity" => [
                            "id" => 35,
                            "name" => "nouveau 2",
                            "note" => null,
                            "figmaLink" => null,
                            "githubLink" => null,
                            "websiteLink" => null,
                            "startDateBaseFormat" => "Y-m-d",
                            "startDate" => "15/08/2024",
                            "endDate" => null,
                            "endDateBaseFormat" => null,
                            "client" => [
                                "id" => 25,
                                "firstName" => "Mey ",
                                "lastName" => "DETOUR",
                                "online" => false,
                                'email' => null,
                            ],
                            "owner" => [
                                'email' => null,
                                'firstName' => null,
                                'lastName' => null,
                            ],
                            "chatName" => "nouveau 2 Chat",
                            "state" => "active",
                            "isCurrent" => true,
                            "cratedAt" => "09/08/2024"
                        ],
                        "note" => [
                            [
                                "Note 1",
                                " "
                            ],
                            [
                                "Note 2",
                                " "
                            ],
                            [
                                "Note 3",
                                " "
                            ],
                            [
                                "Note 4",
                                " "
                            ],
                            [
                                "Note 5",
                                " "
                            ]
                        ],
                        "composition" => [
                            "isPaying" => null,
                            "database" => null,
                            "maquette" => null,
                            "maintenance" => null,
                            "type" => [],
                            "framework" => [],
                            "options" => [],
                            "devices" => []
                        ]],
                    'parametres a mettre dans le body' => [
                        "totalPrice" => null,
                        "estimatedPrice" => null,
                        "maintenancePercentage" => 10,
                        "identity" => [
                            "name" => "nouveau 2",
                            "note" => null,
                            "figmaLink" => null,
                            "githubLink" => null,
                            "websiteLink" => null,
                            "startDateBaseFormat" => "Y-m-d",
                            "startDate" => "15/08/2024",
                            "endDate" => null,
                            "endDateBaseFormat" => null,
                            "client_id" => null,
                        ],

                        "composition" => [
                            "isPaying" => null,
                            "database" => null,
                            "maquette" => null,
                            "maintenance" => null,
                            "type" => [],
                            "framework" => [],
                            "options" => [],
                            "devices" => []
                        ]],
                    'need token ? ' => true],
                [
                    'get project' => '/api/project/{id du projet}',
                    'methode' => 'get',
                    "renvoie :" => [
                        "totalPrice" => null,
                        "estimatedPrice" => null,
                        "maintenancePercentage" => 10,
                        "members" => [
                            [
                                'email' => null,
                                'firstName' => null,
                                'lastName' => null,
                            ], [
                                'email' => null,
                                'firstName' => null,
                                'lastName' => null,
                            ]
                        ],
                        "rules" => [
                            'canEditInvoices' => "boolean",
                            'canSeeClientProfile' => "boolean",
                        ],
                        "identity" => [
                            "id" => 35,
                            "name" => "nouveau 2",
                            "note" => null,
                            "figmaLink" => null,
                            "githubLink" => null,
                            "websiteLink" => null,
                            "startDateBaseFormat" => "Y-m-d",
                            "startDate" => "15/08/2024",
                            "endDate" => null,
                            "endDateBaseFormat" => null,
                            "client" => [
                                "id" => 25,
                                "firstName" => "Mey ",
                                "lastName" => "DETOUR",
                                "online" => false,
                                'email' => null,
                            ],
                            "owner" => [
                                'email' => null,
                                'firstName' => null,
                                'lastName' => null,
                            ],
                            "isCurrent" => true,
                            "chatName" => "nouveau 2 Chat",
                            "state" => "active",
                            "cratedAt" => "09/08/2024"
                        ],
                        "note" => [
                            [
                                "Note 1",
                                " "
                            ],
                            [
                                "Note 2",
                                " "
                            ],
                            [
                                "Note 3",
                                " "
                            ],
                            [
                                "Note 4",
                                " "
                            ],
                            [
                                "Note 5",
                                " "
                            ]
                        ],
                        "composition" => [
                            "isPaying" => null,
                            "database" => null,
                            "maquette" => null,
                            "maintenance" => null,
                            "type" => [],
                            "framework" => [],
                            "options" => [],
                            "devices" => []
                        ]],
                    'parametres a mettre dans le body' => null,
                    'utilisation' => "passer en parametre l'id du projet pour obtenir les informations",
                    'need token ? ' => true],
                [
                    'get projects (can show deleted)' => '/api/projects',
                    'methode' => 'get',
                    "renvoie :" => [[
                        "totalPrice" => null,
                        "estimatedPrice" => null,
                        "maintenancePercentage" => 10,
                        "members" => [
                            [
                                'email' => null,
                                'firstName' => null,
                                'lastName' => null,
                            ], [
                                'email' => null,
                                'firstName' => null,
                                'lastName' => null,
                            ]
                        ],
                        "rules" => [
                            'canEditInvoices' => "boolean",
                            'canSeeClientProfile' => "boolean",
                        ],
                        "identity" => [
                            "id" => 35,
                            "name" => "nouveau 2",
                            "note" => null,
                            "figmaLink" => null,
                            "githubLink" => null,
                            "websiteLink" => null,
                            "startDateBaseFormat" => "Y-m-d",
                            "startDate" => "15/08/2024",
                            "endDate" => null,
                            "endDateBaseFormat" => null,
                            "client" => [
                                "id" => 25,
                                "firstName" => "Mey ",
                                "lastName" => "DETOUR",
                                "online" => false,
                                'email' => null,
                            ],
                            "owner" => [
                                'email' => null,
                                'firstName' => null,
                                'lastName' => null,
                            ],
                            "isCurrent" => true,
                            "chatName" => "nouveau 2 Chat",
                            "state" => "active",
                            "cratedAt" => "09/08/2024"
                        ],
                        "note" => [
                            [
                                "Note 1",
                                " "
                            ],
                            [
                                "Note 2",
                                " "
                            ],
                            [
                                "Note 3",
                                " "
                            ],
                            [
                                "Note 4",
                                " "
                            ],
                            [
                                "Note 5",
                                " "
                            ]
                        ],
                        "composition" => [
                            "isPaying" => null,
                            "database" => null,
                            "maquette" => null,
                            "maintenance" => null,
                            "type" => [],
                            "framework" => [],
                            "options" => [],
                            "devices" => []
                        ]], [
                        "totalPrice" => null,
                        "estimatedPrice" => null,
                        "maintenancePercentage" => 10,
                        "members" => [
                            [
                                'email' => null,
                                'firstName' => null,
                                'lastName' => null,
                            ], [
                                'email' => null,
                                'firstName' => null,
                                'lastName' => null,
                            ]
                        ],
                        "rules" => [
                            'canEditInvoices' => "boolean",
                            'canSeeClientProfile' => "boolean",
                        ],
                        "identity" => [
                            "id" => 35,
                            "name" => "nouveau 2",
                            "note" => null,
                            "figmaLink" => null,
                            "githubLink" => null,
                            "websiteLink" => null,
                            "startDateBaseFormat" => "Y-m-d",
                            "startDate" => "15/08/2024",
                            "endDate" => null,
                            "endDateBaseFormat" => null,
                            "client" => [
                                "id" => 25,
                                "firstName" => "Mey ",
                                "lastName" => "DETOUR",
                                "online" => false,
                                'email' => null,
                            ],
                            "owner" => [
                                'email' => null,
                                'firstName' => null,
                                'lastName' => null,
                            ],
                            "isCurrent" => true,
                            "chatName" => "nouveau 2 Chat",
                            "state" => "active",
                            "cratedAt" => "09/08/2024"
                        ],
                        "note" => [
                            [
                                "Note 1",
                                " "
                            ],
                            [
                                "Note 2",
                                " "
                            ],
                            [
                                "Note 3",
                                " "
                            ],
                            [
                                "Note 4",
                                " "
                            ],
                            [
                                "Note 5",
                                " "
                            ]
                        ],
                        "composition" => [
                            "isPaying" => null,
                            "database" => null,
                            "maquette" => null,
                            "maintenance" => null,
                            "type" => [],
                            "framework" => [],
                            "options" => [],
                            "devices" => []
                        ]]],
                    'parametres a mettre dans le body' => ["display_deleted" => " (boolean)"],
                    'utilisation' => "obtenir les informations et renvoie tous les projets de tout le monde actifs par défaut ou tous les projets (actifs et supprimés) si display_deleted = true ",
                    'need token ? ' => true],
                [
                    'get projects to render' => '/api/your/projects',
                    'methode' => 'get',
                    "renvoie :" => [
                        'currents' => [
                            [
                                "id" => null,
                                "name" => null,
                                "uuid" => null,
                                "cratedAt" => null,
                                'totalTasks' => null,
                                'doneTasks' => null,
                            ],
                            [
                                "id" => null,
                                "name" => null,
                                "uuid" => null,
                                "cratedAt" => null,
                                'totalTasks' => null,
                                'doneTasks' => null,
                            ]
                        ],
                        'others' => [
                            [
                                "id" => null,
                                "name" => null,
                                "uuid" => null,
                                "cratedAt" => null,
                                'totalTasks' => null,
                                'doneTasks' => null,
                            ], [
                                "id" => null,
                                "name" => null,
                                "uuid" => null,
                                "cratedAt" => null,
                                'totalTasks' => null,
                                'doneTasks' => null,
                            ], [
                                "id" => null,
                                "name" => null,
                                "uuid" => null,
                                "cratedAt" => null,
                                'totalTasks' => null,
                                'doneTasks' => null,
                            ]
                        ],
                    ],
                    'parametres a mettre dans le body' => [],
                    'utilisation' => "renvoie les projets courrents et autre appartenant a l'utilisateur ",
                    'need token ? ' => true],
                [
                    'edit project' => '/api/project/edit/{id du projet}',
                    'methode' => 'put',
                    "renvoie :" => [
                        "totalPrice" => null,
                        "estimatedPrice" => null,
                        "maintenancePercentage" => 10,
                        "members" => [
                            [
                                'email' => null,
                                'firstName' => null,
                                'lastName' => null,
                            ], [
                                'email' => null,
                                'firstName' => null,
                                'lastName' => null,
                            ]
                        ],
                        "rules" => [
                            'canEditInvoices' => "boolean",
                            'canSeeClientProfile' => "boolean",
                        ],
                        "identity" => [
                            "id" => 35,
                            "name" => "nouveau 2",
                            "note" => null,
                            "figmaLink" => null,
                            "githubLink" => null,
                            "websiteLink" => null,
                            "startDateBaseFormat" => "Y-m-d",
                            "startDate" => "15/08/2024",
                            "endDate" => null,
                            "endDateBaseFormat" => null,
                            "client" => [
                                "id" => 25,
                                "firstName" => "Mey ",
                                "lastName" => "DETOUR",
                                "online" => false,
                                'email' => null,
                            ],
                            "chatName" => "nouveau 2 Chat",
                            "state" => "active",
                            "owner" => [
                                'email' => null,
                                'firstName' => null,
                                'lastName' => null,
                            ],
                            "isCurrent" => true,
                            "cratedAt" => "09/08/2024"
                        ],
                        "note" => [
                            [
                                "Note 1",
                                " "
                            ],
                            [
                                "Note 2",
                                " "
                            ],
                            [
                                "Note 3",
                                " "
                            ],
                            [
                                "Note 4",
                                " "
                            ],
                            [
                                "Note 5",
                                " "
                            ]
                        ],
                        "composition" => [
                            "isPaying" => null,
                            "database" => null,
                            "maquette" => null,
                            "maintenance" => null,
                            "type" => [],
                            "framework" => [],
                            "options" => [],
                            "devices" => []
                        ]],
                    'parametres a mettre dans le body' => [
                        "totalPrice" => null,
                        "estimatedPrice" => null,
                        "maintenancePercentage" => 10,
                        "identity" => [
                            "name" => "nouveau 2",
                            "note" => null,
                            "figmaLink" => null,
                            "githubLink" => null,
                            "websiteLink" => null,
                            "startDateBaseFormat" => "Y-m-d",
                            "startDate" => "15/08/2024",
                            "endDate" => null,
                            "endDateBaseFormat" => null,
                        ],

                        "composition" => [
                            "isPaying" => null,
                            "database" => null,
                            "maquette" => null,
                            "maintenance" => null,
                            "type" => [],
                            "framework" => [],
                            "options" => [],
                            "devices" => []
                        ]],
                    'utilisation' => "passer en parametre l'id du project et mettre dans le body les parametre a changer",
                    'need token ? ' => true],
                [
                    'get client of the project ' => '/api/project/{id du projet}/get/client',
                    'methode' => 'get',
                    "renvoie :" => [
                        'id' => null,
                        'firstName' => null,
                        'lastName' => null,
                        'date' => null,
                    ],
                    'parametres a mettre dans le body' => null,
                    'utilisation' => "passer en parametre l'id du project",
                    'need token ? ' => true],
                [
                    'add an user to the project ' => '/api/project/{id du project}/add/user/{user id}',
                    'methode' => 'put',
                    "renvoie :" => "ok",
                    'parametres a mettre dans le body' => null,
                    'utilisation' => "passer en parametre l'id du project et l'utilisateur visé, seul le propriétaire du projet peut le faire ",
                    'need token ? ' => true],
                [
                    'remove an user to the project ' => '/api/project/{id du project}/remove/user/{user id}',
                    'methode' => 'delete',
                    "renvoie :" => "ok",
                    'parametres a mettre dans le body' => null,
                    'utilisation' => "passer en parametre l'id du project et l'utilisateur visé, seul le propriétaire du projet peut le faire ",
                    'need token ? ' => true],
                [
                    'delete ' => '/api/project/delete/{id}',
                    'methode' => 'delete',
                    "renvoie :" => "ok si l'action a bien été faite",
                    'parametres a mettre dans le body' => null,
                    'utilisation' => "passer en parametre l'id du project pour mettre la fiche project sur state = 'deleted' ",
                    'need token ? ' => true],
                [
                    'quitter un projet' => '/api/project/{id}/left',
                    'methode' => 'delete',
                    "renvoie :" => "ok si l'action a bien été faite",
                    'parametres a mettre dans le body' => null,
                    'utilisation' => "passer en parametre l'id du project pour mettre la fiche project sur state = 'deleted' ",
                    'need token ? ' => true],
                [
                    'delete force' => '/api/project/deleteforce/{id du project}',
                    'methode' => 'delete',
                    "renvoie :" => "ok si l'action a bien été faite",
                    'parametres a mettre dans le body' => null,
                    'utilisation' => "quitter un projet",
                    'need token ? ' => true],
                [
                    'set project current/no current' => '/api/project/{id du projet}/switch/current',
                    'methode' => 'put',
                    "renvoie :" => "ok si l'action a bien été faite",
                    'parametres a mettre dans le body' => [
                        'isCurrent' => "boolean",
                    ],
                    'utilisation' => "passer en parametre  true si le projet est actif false si il est en attend ou temriné",
                    'need token ? ' => true],

                [
                    'edit note of project' => '/api/project/id/note',
                    'methode' => 'put',
                    "renvoie :" => [
                        "totalPrice" => null,
                        "estimatedPrice" => null,
                        "maintenancePercentage" => 10,
                        "members" => [
                            [
                                'email' => null,
                                'firstName' => null,
                                'lastName' => null,
                            ], [
                                'email' => null,
                                'firstName' => null,
                                'lastName' => null,
                            ]
                        ],
                        "rules" => [
                            'canEditInvoices' => "boolean",
                            'canSeeClientProfile' => "boolean",
                        ],
                        "identity" => [
                            "id" => 35,
                            "name" => "nouveau 2",
                            "note" => null,
                            "figmaLink" => null,
                            "githubLink" => null,
                            "websiteLink" => null,
                            "startDateBaseFormat" => "Y-m-d",
                            "startDate" => "15/08/2024",
                            "endDate" => null,
                            "endDateBaseFormat" => null,
                            "client" => [
                                "id" => 25,
                                "firstName" => "Mey ",
                                "lastName" => "DETOUR",
                                "online" => false,
                                'email' => null,
                            ],
                            "chatName" => "nouveau 2 Chat",
                            "state" => "active",
                            "owner" => [
                                'email' => null,
                                'firstName' => null,
                                'lastName' => null,
                            ],
                            "isCurrent" => true,
                            "cratedAt" => "09/08/2024"
                        ],
                        "note" => [
                            [
                                "Note 1",
                                " "
                            ],
                            [
                                "Note 2",
                                " "
                            ],
                            [
                                "Note 3",
                                " "
                            ],
                            [
                                "Note 4",
                                " "
                            ],
                            [
                                "Note 5",
                                " "
                            ]
                        ],
                        "composition" => [
                            "isPaying" => null,
                            "database" => null,
                            "maquette" => null,
                            "maintenance" => null,
                            "type" => [],
                            "framework" => [],
                            "options" => [],
                            "devices" => []
                        ]],
                    'parametres a mettre dans le body' => [
                        'names' => [
                            "0" => "name1",
                            "1" => "name1",
                            "2" => "name1",
                            "3" => "name1",
                            "4" => "name1",
                        ],
                        'contents' => [
                            "0" => "content1",
                            "1" => "content1",
                            "2" => "content1",
                            "3" => "content1",
                            "4" => "content1",
                        ]
                    ],
                    'utilisation' => "passer en parametre l'id du client et mettre project_id dans le body pour enlever le projet des projets courrents ",
                    'need token ? ' => true],],
            'user' => [
                [
                    'get user connected' => '/api/me',
                    'methode' => 'get',
                    "renvoie :" => [
                        'id' => null,
                        'mail' => null,
                        'phone' => null,
                        'siret' => null,
                        'address' => null,
                        'firstName' => null,
                        'lastName' => null,
                    ],
                    'parametres a mettre dans le body' => null,
                    'utilisation' => "permet d'avoir l'utilisateur a partir du token",
                    'need token ? ' => true
                ], [
                    'get users' => '/api/users',
                    'methode' => 'get',
                    "renvoie :" => [
                        'id' => null,
                        'mail' => null,
                        'phone' => null,
                        'firstName' => null,
                        'lastName' => null,
                    ],
                    'parametres a mettre dans le body' => null,
                    'utilisation' => "avoir tous les utilisateursn",
                    'need token ? ' => true
                ],
                [
                    'edit current user' => '/api/edit/me',
                    'methode' => 'put',
                    "renvoie :" => [
                        'id' => null,
                        'mail' => null,
                        'phone' => null,
                        'siret' => null,
                        'address' => null,
                        'firstName' => null,
                        'lastName' => null,
                    ],
                    'parametres a mettre dans le body' => [
                        'phone' => null,
                        'siret' => null,
                        'address' => null,
                        'firstName' => null,
                        'lastName' => null,
                    ],
                    'utilisation' => "modifier l'utilisateur",
                    'need token ? ' => true
                ],
                [
                    'get one user by id' => '/api/user/{id}e',
                    'methode' => 'get',
                    "renvoie :" => [
                        'id' => null,
                        'mail' => null,
                        'phone' => null,
                        'siret' => null,
                        'address' => null,
                        'firstName' => null,
                        'lastName' => null,
                    ],
                    'parametres a mettre dans le body' => 'nothing',
                    'utilisation' => "avoir un utilisateur en passant en parametre son id",
                    'need token ? ' => true],
            ],
            'invoice' => [
                [
                    'new invoice' => '/api/invoice/new',
                    'methode' => 'post',
                    "renvoie :" => [
                        'id' => null,
                        'price' => null,
                        'description' => null,
                        'date' => null,
                        'project_id' => null,
                        'number' => null,
                        'client' => [
                            "firstName" => null,
                            "lastName" => null,
                        ],
                        'payed' => null,

                    ],
                    'parametres a mettre dans le body' => [
                        'price' => null,
                        'description' => null,
                        'project_id' => null,
                        'date' => 'd/m/Y',

                    ],
                    'utilisation' => "permet de créer une nouvelle facture liée a un projet ",
                    'need token ? ' => true],
                [
                    'edit invoice' => '/api/invoice/edit/{unique number of invoice}',
                    'methode' => 'put',
                    "renvoie :" => [
                        'id' => null,
                        'price' => null,
                        'description' => null,
                        'date' => null,
                        'project_id' => null,
                        'number' => null,
                        'client' => [
                            "firstName" => null,
                            "lastName" => null,
                        ],
                        'payed' => null,

                    ],
                    'parametres a mettre dans le body' => [
                        'price' => null,
                        'description' => null,
                        'date' => 'd/m/Y',

                    ],
                    'utilisation' => "permet de modifier une facture liée a un projet ",
                    'need token ? ' => true],
                [
                    'get all invoices of project' => '/api/{id du project}/invoices',
                    'methode' => 'get',
                    "renvoie :" => [[
                        'id' => null,
                        'price' => null,
                        'description' => null,
                        'date' => null,
                        'project_id' => null,
                        'number' => null,
                        'client' => [
                            "firstName" => null,
                            "lastName" => null,
                        ],
                        'payed' => null,

                    ], [
                        'id' => null,
                        'price' => null,
                        'description' => null,
                        'date' => null,
                        'project_id' => null,
                        'number' => null,
                        'client' => [
                            "firstName" => null,
                            "lastName" => null,
                        ],
                        'payed' => null,

                    ]],
                    'parametres a mettre dans le body' => 'nothing',
                    'utilisation' => "permet d'btenir toutes les factures d'un projet ",
                    'need token ? ' => true],
                [
                    'get all of a client' => '/api/invoices/of/client/{unique number of client}',
                    'methode' => 'get',
                    "renvoie :" => [[
                        'id' => null,
                        'price' => null,
                        'description' => null,
                        'date' => null,
                        'project_id' => null,
                        'number' => null,
                        'client' => [
                            "firstName" => null,
                            "lastName" => null,
                        ],
                        'payed' => null,

                    ], [
                        'id' => null,
                        'price' => null,
                        'description' => null,
                        'date' => null,
                        'project_id' => null,
                        'number' => null,
                        'client' => [
                            "firstName" => null,
                            "lastName" => null,
                        ],
                        'payed' => null,

                    ]],
                    'parametres a mettre dans le body' => "nothing",
                    'utilisation' => "obtenir les factures d'un seul client",
                    'need token ? ' => true],
                [
                    'to pay invoice' => '/api/invoice/{unique number of invoice}/pay',
                    'methode' => 'PUT',
                    "renvoie :" => "ok",
                    'parametres a mettre dans le body' => "nothing",
                    'utilisation' => "marqué une facture comme payé",
                    'need token ? ' => true],
                [
                    'delete invoice' => '/api/invoice/delete/{unique number of invoice}',
                    'methode' => 'delete',
                    "renvoie :" => "ok",
                    'parametres a mettre dans le body' => "nothing",
                    'utilisation' => "supprimer une facture",
                    'need token ? ' => true],

            ],
            'logs' => [
                [
                    'get logs' => '/api/logs',
                    'methode' => 'get',
                    "renvoie :" => [
                        'id' => null,
                        'date' => null,
                        'author' => null,
                        'message' => null,
                        'type' => null,
                    ],
                    'parametres a mettre dans le body' => "les logs se generent automatiquement",
                    'utilisation' => "récupere tous les logs",
                    'need token ? ' => true],

            ],
            'pdf' => [
                [
                    'upload one pdf for technbical specification' => '/api/project/{id du project}/specifications/upload/pdf',
                    'methode' => 'post',
                    "renvoie :" => [
                        'filePath' => null,
                    ],
                    'parametres a mettre dans le body' => "form-data avec la clé pdf et l'id du project ",
                    'utilisation' => "uploader le fichier pdf d'un cahier des charges ",
                    'need token ? ' => true],
                [
                    'get pdf of technical Specification' => '/api/get/{id du projet}/specifications',
                    'methode' => 'get',
                    "renvoie :" => [
                        'filePath' => null,
                    ],
                    'parametres a mettre dans le body' => " nothin ",
                    'utilisation' => "obtenir le pdf d'un cahier des charges ",
                    'need token ? ' => true],
                [
                    'delete pdf of technical Specification' => '/api/remove/{id du projet}/specifications',
                    'methode' => 'delete',
                    "renvoie :" => " ok",
                    'parametres a mettre dans le body' => "nothing",
                    'utilisation' => "supprimer le pdf d'un cahier des charges ",
                    'need token ? ' => true],

            ],
            'note' => [
                [
                    'edit les note' => '/api/edit/note',
                    'methode' => 'post',
                    "renvoie :" => [
                        'notes' => null,
                        'remembers' => null,
                    ],
                    'parametres a mettre dans le body' => [
                        'notes' => null,
                        'remembers' => null,
                    ],
                    'utilisation' => "Créer une note, la modifier ou la renvoie",
                    'need token ? ' => true],
                [
                    'get les note' => '/api/note',
                    'methode' => 'get',
                    "renvoie :" => [
                        'notes' => null,
                        'remembers' => null,
                    ],
                    'parametres a mettre dans le body' => "nothing",
                    'utilisation' => "renvoie la note",
                    'need token ? ' => true],


            ],
            "search" => [
                [
                    'search project' => '/api/search/project',
                    'methode' => 'get',
                    "renvoie :" => [
                        [
                            "id" => 35,
                            "name" => "nouveau 2",
                            "note" => null,
                            "client" => [
                                "id" => 25,
                                "firstName" => "Mey ",
                                "lastName" => "DETOUR",
                                "date" => "09/08/2024"
                            ]
                        ], [
                            "id" => 35,
                            "name" => "nouveau 2",
                            "note" => null,
                            "client" => [
                                "id" => 25,
                                "firstName" => "Mey ",
                                "lastName" => "DETOUR",
                                "date" => "09/08/2024"
                            ]
                        ]
                    ],
                    'parametres a mettre dans le body' => [
                        "searchTerm" => null,
                    ],
                    'utilisation' => "chercher les projets",
                    'need token ? ' => true],
                [
                    'search client' => '/api/search/client',
                    'methode' => 'get',
                    "renvoie :" => [[
                        "id" => null,
                        "firstName" => null,
                        "lastName" => null,
                        "online" => null,
                        "projectsNumber" => null,
                        'date' => null,
                    ], [
                        "id" => null,
                        "firstName" => null,
                        "lastName" => null,
                        "online" => null,
                        "projectsNumber" => null,
                        'date' => null,
                    ]],
                    'parametres a mettre dans le body' => [
                        "searchTerm" => null,
                    ],
                    'utilisation' => "chercher les clients",
                    'need token ? ' => true],
                [
                    'search chat' => '/api/search/chat',
                    'methode' => 'get', [
                    [
                        "id" => null,
                        "name" => null,
                    ], [
                        "id" => null,
                        "name" => null,
                    ], [
                        "id" => null,
                        "name" => null,
                    ]
                ],
                    'parametres a mettre dans le body' => [
                        "searchTerm" => null,
                    ],
                    'utilisation' => "chercher les chats",
                    'need token ? ' => true],
            ],
            "message" => [
                [
                    'gets all chats' => '/api/chats',
                    'methode' => 'get',
                    "renvoie :" => [
                        "unread"=>[],
                        "read"=>[]
                    ],
                    'parametres a mettre dans le body' => "nothing",
                    'utilisation' => "renvoie les chats dans lequels vous vous trouver + les chats des projets que l'on vous a partager",
                    'need token ? ' => true],
                [
                    'get one chat' => '/api/chat/{id du chat}',
                    'methode' => 'get',
                    "renvoie :" => [
                        'id' => null,
                        'name' => null,
                        'date' => null,
                        'project_id' => null,
                        'project_uuid' => null,
                        'client' => [
                            'id' => null,
                            'firstName' => null,
                            'lastName' => null,
                            'online' => null,
                            'date' => null,
                            'projectNumber' => null,
                        ],
                        'messages' => [
                            [
                                'id' => null,
                                'content' => null,
                                'datetime' => null,
                                'author' => [
                                    'id' => null,
                                    'firstname' => null,
                                    'lastname' => null,
                                    'email' => null,
                                ],
                                'type' => 'client or user',
                            ],
                            [
                                'id' => null,
                                'content' => null,
                                'datetime' => null,
                                'author' => [
                                    'id' => null,
                                    'firstname' => null,
                                    'lastname' => null,
                                    'email' => null,
                                ],
                                'type' => 'client or user',
                            ]
                        ]

                    ],
                    'parametres a mettre dans le body' => "nothing",
                    'utilisation' => "avoir une conversation",
                    'need token ? ' => true],

                [
                    'envoyer un message depuis l interface client' => '/message',
                    'methode' => 'post',
                    "renvoie :" => "ok",
                    'parametres a mettre dans le body' =>
                        [
                            "id" => "uuid of project",
                            "content" => null
                        ],
                    'utilisation' => "envoyer un message",
                    'need token ? ' => false],
                [
                    'envoyer un message depuis l app' => '/api/message',
                    'methode' => 'post',
                    "renvoie :" => "ok",
                    'parametres a mettre dans le body' =>
                        [
                            "id" => "uuid of project",
                            "content" => null
                        ],
                    'utilisation' => "envoyer un message",
                    'need token ? ' => true],
                [
                    'delete message' => '/api/delete/{id}/message',
                    'methode' => 'delete',
                    "renvoie :" => "ok",
                    'parametres a mettre dans le body' =>
                        [
                        ],
                    'utilisation' => "supprimer un message",
                    'need token ? ' => true],
            ],
            "setting" => [
                [
                    "recuperer les parametre de l'utilisateur " => '/api/settings',
                    'methode' => 'get',
                    "renvoie :" => [
                        'formatDate' => "valeurs acceptées : UE(default),SUI,PB,US,AS,ISO (pour l'instant)",
                        'payments' => "valeurs acceptées : CHEQUE,CASH,BANKTRANSFER",
                        'delayDays' => "valeurs acceptées : 30,50,60",
                        'installmentPayments' => 'boolean',
                        'freeMaintenance' => 'boolean',
                        'interfaceLangage' => "values : AG,FR"
                    ],
                    'parametres a mettre dans le body' => null,
                    'utilisation' => "appeller la route c'est deja un bon debut..",
                    'need token ? ' => true],
                [
                    "modifier les parametre de l'utilisateur " => '/api/edit/settings',
                    'methode' => 'put',
                    "renvoie :" => [
                        'formatDate' => "valeurs acceptées : UE(default),SUI,PB,US,AS,ISO (pour l'instant)",
                        'payments' => "valeurs acceptées : CHEQUE,CASH,BANKTRANSFER",
                        'delayDays' => "valeurs acceptées : 30,50,60",
                        'installmentPayments' => 'boolean',
                        'freeMaintenance' => 'boolean',
                        'interfaceLangage' => "values : AG,FR"
                    ],
                    'parametres a mettre dans le body' => [
                        'formatDate' => "valeurs acceptées : UE(default),SUI,PB,US,AS,ISO (pour l'instant)",
                        'payments' => "valeurs acceptées : CHEQUE,CASH,BANKTRANSFER",
                        'delayDays' => "valeurs acceptées : 30,50,60",
                        'installmentPayments' => 'boolean',
                        'freeMaintenance' => 'boolean',
                        'interfaceLangage' => "values : AG,FR"
                    ],
                    'utilisation' => "envoyer les données a remplacer",
                    'need token ? ' => true],
            ],
            'task' => [
                [
                    "Nouvelle task " => '/api/task/new',
                    'methode' => 'post',
                    "renvoie :" => [
                        'name' => 'New task',
                        'content' => 'Labore fugiat amet voluptate sit quis reprehenderit dolor eiusmod ad fugiat mollit officia est minim ut sint officia voluptate ut laboris aute consectetur labore minim eiusmod sint aute in sed incididunt.',
                        'category' => 'dev',
                        'status' => 'waiting',
                        'dueDate' => '07/08/2024',
                        'dueDateBaseFormat' => '2024-m-d',
                        'author' => [
                            'firstName' => 'Maxence',
                            'lastName' => 'ABRILE'
                        ]
                    ],
                    'parametres a mettre dans le body' => [
                        'name' => null,
                        'content' => null,
                        'category' => "string",
                        'project_id' => null,
                        'dueDate' => 'to formate d/m/y',
                    ],
                    'utilisation' => "Crer une nouvelle tache  a partir du project",
                    'need token ? ' => true],
                [
                    "edit task " => '/api/task/{id de la task}/edit',
                    'methode' => 'put',
                    "renvoie :" => [
                        'name' => 'New task',
                        'content' => 'Labore fugiat amet voluptate sit quis reprehenderit dolor eiusmod ad fugiat mollit officia est minim ut sint officia voluptate ut laboris aute consectetur labore minim eiusmod sint aute in sed incididunt.',
                        'category' => 'dev',
                        'status' => 'waiting',
                        'dueDate' => '07/08/2024',
                         'dueDateBaseFormat' => '2024-m-d',
                        'author' => [
                            'firstName' => 'Maxence',
                            'lastName' => 'ABRILE'
                        ]],
                    'parametres a mettre dans le body' => [
                        'name' => null,
                        'content' => null,
                        'category' => "string",
                        'dueDate' => 'to formate d/m/y',
                    ],
                    'utilisation' => "modifier  une tache ",
                    'need token ? ' => true,
                ],
                ["edit status of task " => '/api/task/{id de la task}/edit/status',
                    'methode' => 'put',
                    "renvoie :" => [
                        'name' => 'New task',
                        'content' => 'Labore fugiat amet voluptate sit quis reprehenderit dolor eiusmod ad fugiat mollit officia est minim ut sint officia voluptate ut laboris aute consectetur labore minim eiusmod sint aute in sed incididunt.',
                        'category' => 'dev',
                        'status' => 'waiting',
                        'order' => 1,
                        'dueDate' => '07/08/2024',
                         'dueDateBaseFormat' => '2024-m-d',
                        'author' => [
                            'firstName' => 'Maxence',
                            'lastName' => 'ABRILE'
                        ]
                    ],
                    'parametres a mettre dans le body' => [
                        'status' => "waiting','progress','done"
                    ],
                    'utilisation' => "modifier le status d' une tache  (waiting , done , progress)",
                    'need token ? ' => true]
                ,
                ["modifier l'ordre de la tache " => '/api/task/{id de la task}/edit/order',
                    'methode' => 'put',
                    "renvoie :" =>
                        ' ok'
                    ,
                    'parametres a mettre dans le body' => [
                        'order' => "int"
                    ],
                    'utilisation' => "modifier le status d' une tache  (waiting , done , progress)",
                    'need token ? ' => true],

                [
                    "delete task " => '/api/task/{id de la task}/delete',
                    'methode' => 'delete',
                    "renvoie :" => "ok",
                    'parametres a mettre dans le body' => "nothing",
                    'utilisation' => "supprimer une tache",
                    'need token ? ' => true],
                [
                    "avoir le tableau des tasks " => '/api/project/{id du projet}/tasks',
                    'methode' => 'get',
                    "renvoie :" => [
                        'waiting' => [
                            [
                                'name' => 'New task',
                                'content' => 'Labore fugiat amet voluptate sit quis reprehenderit dolor eiusmod ad fugiat mollit officia est minim ut sint officia voluptate ut laboris aute consectetur labore minim eiusmod sint aute in sed incididunt.',
                                'category' => 'dev',
                                'order' => 1,
                                'status' => 'waiting',
                                'dueDate' => '07/08/2024',
                                 'dueDateBaseFormat' => '2024-m-d',
                                'author' => [
                                    'firstName' => 'Maxence',
                                    'lastName' => 'ABRILE'
                                ]
                            ]
                        ],
                        'progress' => [],
                        'done' => []
                    ],
                    'parametres a mettre dans le body' => "nothing",
                    'utilisation' => "obtenir les taches d'un projet",
                    'need token ? ' => true],
                [
                    "avoir une tache " => '/api/task/{id de la task}/get',
                    'methode' => 'get',
                    "renvoie :" => [
                        'name' => 'New task',
                        'content' => 'Labore fugiat amet voluptate sit quis reprehenderit dolor eiusmod ad fugiat mollit officia est minim ut sint officia voluptate ut laboris aute consectetur labore minim eiusmod sint aute in sed incididunt.',
                        'category' => 'dev',
                        'order' => 1,
                        'status' => 'waiting',
                        'dueDate' => '07/08/2024',
                        'dueDateBaseFormat' => '2024-m-d',
                        'author' => [
                            'firstName' => 'Maxence',
                            'lastName' => 'ABRILE'
                        ]

                    ],
                    'parametres a mettre dans le body' => "nothing",
                    'utilisation' => "obtenir une tache  ",
                    'need token ? ' => true],
            ]

        ]);
    }
    /* #[Route('/api/template', name: 'template', methods: 'post')]
     public function edit( Request $request, EntityManagerInterface $manager, ClientRepository $clientRepository, ProjectRepository $projectRepository): Response
     {
         try {
             $invoice = $invoiceRepository->find($id);
             if (!$invoice) {
                  return new JsonResponse( [
                        'state' => 'NDF',
                        'value' => 'invoice',
                     ] , Response::HTTP_NOT_FOUND);
             }

             $data = json_decode($request->getContent(), true);

             if ($data) {

                 if (!isset($data['description']) || empty(trim($data['description']))) {
                    return new JsonResponse( [
                        'state' => 'NED',
                        'value' => 'description',
                    ] ,Response::HTTP_UNPROCESSABLE_ENTITY);
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
              return new JsonResponse( ['state' => 'ND'] ,Response::HTTP_BAD_REQUEST);

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
