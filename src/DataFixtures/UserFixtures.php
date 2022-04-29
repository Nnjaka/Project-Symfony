<?php

namespace App\DataFixtures;

use App\Entity\User;
use Faker\Factory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class UserFixtures extends Fixture
{
    public function loadNews(ObjectManager $manager): void
    {
        $faker = Factory::create();
        for ($i = 0; $i < 20; $i++) {
            $user = new User();
            $user->setFirstName($faker->firstName())
                ->setLastName($faker->lastName())
                ->setEmail($faker->freeEmail())
                ->setPassword($faker->md5());
            $manager->persist($user);
            $this->addReference('user_reference_' . $i, $user);
        }
        $manager->flush();
    }

    public function load(ObjectManager $manager): void
    {
        $this->loadNews($manager);
    }
}
