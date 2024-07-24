  #[Route('/register', name: 'api_app_register', methods: 'post')]
    public function register(Request $request, UserRepository $repository, UserPasswordHasherInterface $passwordHasher): Response
    {
        $data = json_decode($request->getContent(), true);

        if (!$data['email'] || $data['email'] == '' || $data['email'] == ' ') {
            return $this->json(['message' => 'Username inexistant ou vide']);
        }
        if (!$data['password'] || $data['password'] == '' || $data['password'] == ' ') {
            return $this->json(['message' => 'Mot de passe inexistant ou vide']);
        }
        if ($repository->findOneBy(['email' => $data['email']])) {
            return $this->json(['message' => "Nom d'utilisateur déjà prit."]);

        }
        $user = new User();
        $user->setEmail($data['email']);
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