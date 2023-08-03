<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Classement;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        for ($i = 1; $i <= 18; $i++) {
            $classement = new Classement();
            $classement->setPseudo("joueur $i")
                ->setScore("1$i")
                ->setDate(new \DateTime());

            $manager->persist($classement);
        }

        $manager->flush();
    }
}
