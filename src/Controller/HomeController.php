<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/home', name: 'app_home')]
    public function index(): Response
    {

        return $this->json(['message' => 'ok']);
    }

    #[Route('/routes', name: 'all_routes')]
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
                    'utilisation' => 'Créer une fiche client avec les parametres rentréer dans le body',
                    'methode' => 'post',
                    "renvoie" => "la fiche client",
                    'parametres a mettre dans le body' => "first_name,last_name,job,age,location,mail,phone",
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
                    'parametres a mettre dans le body' => "display_deleted (boolean)",
                    'utilisation' => "passer en parametre l'id du client pour obtenir les informations et renvoie tous les clients actifs par défaut ou tous les clients (actifs et supprimés) si display_deleted = true ",
                    'need token ? ' => true],
                [
                    'edit client' => '/api/client/edit/{id du client}',
                    'methode' => 'put',

                    "renvoie" => "la fiche client",
                    'parametres a mettre dans le body' => "first_name,last_name,job,age,location,mail,phone",
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
                    'methode' => 'get',
                    "renvoie" => "liste des projets courrants du client : id,name,startDate,endDate ( a toi de me dire les infos quil faut renvoyer sur le projet)",
                    'parametres a mettre dans le body' => null,
                    'utilisation' => "passer en parametre l'id du client pour obtenir la liste de ses projets courrants",
                    'need token ? ' => true],
            ],
            'projects' => [
                [
                    'new client' => '/api/project/new',
                    'utilisation' => 'Créer une projet avec les parametres rentrér dans le body',
                    'methode' => 'post',
                    "renvoie" => "le projet (id,name,figmaLink,githubLink,state,startDate,endDate,totalPrice,client_id,owner,",
                    'parametres a mettre dans le body' => "name(*),figmaLink,githubLink,state,startDate,endDate,totalPrice,client_id,",
                    'need token ? ' => true],
                [
                    'get client' => '/api/project/{id du client}',
                    'methode' => 'get',
                    "renvoie" => "le projet",
                    'parametres a mettre dans le body' => null,
                    'utilisation' => "passer en parametre l'id du projet pour obtenir les informations",
                    'need token ? ' => true],
                [
                    'get clients' => '/api/clients',
                    'methode' => 'get',
                    "renvoie" => "tous les projet associés au compte de l'utilisateur selon le parametre entré",
                    'parametres a mettre dans le body' => "display_deleted (boolean)",
                    'utilisation' => "passer en parametre l'id du projet pour obtenir les informations et renvoie tous les projets actifs par défaut ou tous les projets (actifs et supprimés) si display_deleted = true ",
                    'need token ? ' => true],
                [
                    'edit client' => '/api/project/edit/{id du client}',
                    'methode' => 'put',
                    "renvoie" => "le projet",
                    'parametres a mettre dans le body' => "name(*),figmaLink,githubLink,state,startDate,endDate,totalPrice,client_id,",
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
            ],
            'user' => [
                [
                    'get user connected' => '/api/me',
                    'methode' => 'get',
                    "renvoie" => "renvoie l'id et le mail",
                    'parametres a mettre dans le body' => null,
                    'utilisation' => "permet d'avoir l'utilisateur a partir du token",
                    'need token ? ' => true],
                [
                    'get user with id' => '/api/user/{id}',
                    'methode' => 'get',
                    "renvoie" => "renvoie l'id et le mail de l'utilisateur visé",
                    'parametres a mettre dans le body' => null,
                    'utilisation' => "permet d'avoir l'utilisateur a partir d'un id",
                    'need token ? ' => true],
            ]

        ]);
    }
}
