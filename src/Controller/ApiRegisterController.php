<?php

namespace App\Controller;

use App\Entity\Setting;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
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
    public function register(Request $request, UserRepository $repository, UserPasswordHasherInterface $passwordHasher)
    {
        $create = true;
        if (!$create) {
            return new JsonResponse( [
                'state' => 'ISE',
                'value' => 'cannot create account',
              ] , Response::HTTP_INTERNAL_SERVER_ERROR);

        }

        $data = json_decode($request->getContent(), true);

        if (!isset($data['username']) || empty(trim($data['username']))) {
            return new JsonResponse( [
                'state' => 'NED',
                'value' => 'username',
            ] ,Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        if (!isset($data['password']) || empty(trim($data['password']))) {

            return new JsonResponse( [
                'state' => 'NED',
                'value' => 'password',
            ] ,Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        if (strlen($data['password'] <= 5)) {
            return new JsonResponse( [
                'state' => 'LTS',
                'value' => 'password',
             ] , Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        if ($repository->findOneBy(['email' => $data['username']])) {
            return new JsonResponse( [
                'state' => 'NU',
                'value' => 'email',
             ] , Response::HTTP_UNPROCESSABLE_ENTITY);
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

            return new JsonResponse( [
                'state' => 'ISE',
                'value' => $this->json($errors[0])
             ] , Response::HTTP_INTERNAL_SERVER_ERROR);
        }


        $this->entityManager->persist($user);
        $this->entityManager->flush();


        $setting = new Setting();
        $setting->setOwner($user);
        $setting->setDateFormat('UE');
        $setting->setPayment('');
        $setting->setDelayDays(30);
        $setting->setFreeMaintenance(true);
        $setting->setInstallmentPayments(true);
        $setting->setInterfaceLangage('FR');


        $this->entityManager->persist($setting);
        $this->entityManager->flush();

        return new JsonResponse( [
            'state' => 'ok'
         ] , Response::HTTP_OK);
    }
}
