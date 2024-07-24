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

                    'need token ? ' => false
                ],
                [
                    'register' => '/register',
                    'methode' => 'post',
                    'parametres a mettre dans le body' => "username,password",
                    'need token ? ' => false
                ]
            ],
            'clients' => [
                [
                    'new client' => '/api/client/new',
                    'utilisation' => 'Créer une fiche client avec les parametres rentréer dans le body',
                    'methode' => 'post',
                    'parametres a mettre dans le body' => "first_name,last_name,job,age,location,mail,phone",
                    'need token ? ' => true],
                [
                    'new client' => '/api/client/{id du client}',
                    'methode' => 'get',
                    'parametres a mettre dans le body' => null,
                    'utilisation' => "passer en parametre l'id du client pour obtenir les informations",
                    'need token ? ' => true],
                [
                    'edit client' => '/api/client/edit/{id du client}',
                    'methode' => 'post',
                    'parametres a mettre dans le body' => "first_name,last_name,job,age,location,mail,phone",
                    'utilisation' => "passer en parametre l'id du client et mettre dans le body les parametre a changer",
                    'need token ? ' => true],
            ]

        ]);
    }
}
