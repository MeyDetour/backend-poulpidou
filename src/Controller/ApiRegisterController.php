<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;


class ApiRegisterController extends AbstractController
{
    private $entityManager;

    private $validator;

    public function __construct(EntityManagerInterface $entityManager, ValidatorInterface $validator)
    {
        $this->entityManager = $entityManager;
        $this->validator = $validator;
    }

    #[Route('/register', name: 'api_app_register', methods: 'post')]
    public function register(Request $request, UserRepository $repository, UserPasswordHasherInterface $passwordHasher): Response
    {
        $data = json_decode($request->getContent(), true);

        if (!$data['username'] || $data['username'] == '' || $data['username'] == ' ') {
            return $this->json(['message' => 'Username inexistant ou vide']);
        }
        if (!$data['password'] || $data['password'] == '' || $data['password'] == ' ') {
            return $this->json(['message' => 'Mot de passe inexistant ou vide']);
        }
        if ($repository->findOneBy(['email' => $data['username']])) {
            return $this->json(['message' => "Nom d'utilisateur déjà prit."]);

        }
        $user = new User();
        $user->setEmail($data['username']);
        $user->setPassword(
            $passwordHasher->hashPassword(
                $user,
                $data['password']
            )
        );

        $errors = $this->validator->validate($user);
        if (count($errors) > 0) {
            return $this->json($errors[0]);
        }
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $this->json(['message' => 'ok']);
    }
}
