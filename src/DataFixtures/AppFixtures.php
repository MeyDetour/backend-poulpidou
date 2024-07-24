<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {    $user = new User();
        $user->setPassword('$2y$13$1zFD2rA5vmlRDdx8asgaH.K.3iyaIvlS8HmYMASbcpJB.mNzEnchS');
        $user->setEmail('meydetour@gmail.com');
        $user->setVerified(false);
        $manager->persist($user);

        $manager->flush();

        $manager->flush();
    }
}
