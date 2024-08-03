<?php

namespace App\Controller;

use App\Entity\Note;
use App\Entity\Setting;
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
        $create = true;
        if (!$create) {
            return $this->json([
                'state' => 'ISE',
                'value' => 'cannot create account'
            ]);
        }

        $data = json_decode($request->getContent(), true);

        if (!isset($data['username']) || empty(trim($data['username']))) {
            return $this->json([
                'state' => 'NED',
                'value' => 'username'
            ]);
        }
        if (!isset($data['password']) || empty(trim($data['password']))) {

            return $this->json([
                'state' => 'NED',
                'value' => 'password'
            ]);
        }
        if (strlen($data['password'] <= 5)) {
            return $this->json([
                'state' => 'LTS',
                'value' => 'password'
            ]);
        }
        if ($repository->findOneBy(['email' => $data['username']])) {
            return $this->json([
                'state' => 'NU',
                'value' => 'email'
            ]);
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
            return $this->json([
                'state' => 'ISE',
                'value' => $this->json($errors[0])
            ]);
        }


        $this->entityManager->persist($user);
        $this->entityManager->flush();


        $setting = new Setting();
        $setting->setOwner($this->getUser());
        $setting->setDateFormat('UE');
        $setting->setPayment('');

        $note = new Note();
        $note->setOwner($this->getUser());
        $note->setNotes("");
        $note->setRemembers("");
        $this->entityManager->persist($note);
        $this->entityManager->persist($setting);
        $this->entityManager->flush();
        return $this->json([
            'state' => 'OK'
        ]);
    }
}
