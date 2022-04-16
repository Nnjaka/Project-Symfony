<?php

namespace App\DataFixtures;

use App\Entity\News;
use Faker\Factory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class NewsFixtures extends Fixture implements DependentFixtureInterface
{
    public function loadNews(ObjectManager $manager): void
    {
        $faker = Factory::create();
        for ($i = 0; $i < 20; $i++) {
            $news = new News();
            $news->setTitle($faker->text(50));
            $news->setText($faker->text(200));
            $news->setImage('https://loremflickr.com/640/360');
            $news->setUser($this->getReference('user_reference_' . $i));
            $manager->persist($news);
        }
        $manager->flush();
    }

    public function load(ObjectManager $manager): void
    {
        $this->loadNews($manager);
    }

    public function getDependencies()
    {
        return [
            UserFixtures::class,
        ];
    }
}
