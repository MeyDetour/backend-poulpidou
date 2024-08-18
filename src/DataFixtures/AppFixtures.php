<?php

namespace App\DataFixtures;

use App\Entity\Note;
use App\Entity\Setting;
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

        $user2 = new User();
        $user2->setPassword('$2y$13$1zFD2rA5vmlRDdx8asgaH.K.3iyaIvlS8HmYMASbcpJB.mNzEnchS');
        $user2->setEmail('larbin@gmail.com');
        $user2->setVerified(false);
        $user2->setFirstName('Mr');
        $user2->setLastName('LARBIN');
        $manager->persist($user2);

        $manager->flush();

        $setting = new Setting();
        $setting->setOwner($user);
        $setting->setDateFormat('UE');
        $setting->setPayment('');
        $setting->setDelayDays(30);
        $setting->setFreeMaintenance(true);
        $setting->setInstallmentPayments(true);
        $setting->setInterfaceLangage('FR');


        $note = new Note();
        $note->setOwner($user);
        $note->setNotes("");
        $note->setRemembers("");
       $manager->persist($note);
       $manager->persist($setting);
       $manager->flush();
    }
}
