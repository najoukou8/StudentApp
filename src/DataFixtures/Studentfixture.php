<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use \App\Entity\Etudiant;
use Faker\Factory;

class Studentfixture extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        
        $faker = Factory::create();  


        for ($i=0; $i<=2000; $i++){
            $student = new Etudiant();
            $student ->setNom($faker->lastName);
            $student ->setPrenom( $faker-> firstName);
            $student ->setEmail( $faker-> email);
            $student->setTel((int)$faker->numerify('##########'));            
            $manager->persist($student);


        }
     

        $manager->flush();
    }
}
