<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Factory\Factory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class ListFixtures extends Fixture implements DependentFixtureInterface
{
    /**
     * @var Factory
     */
    private $factory;
    private $faker;

    public function __construct(Factory $factory)
    {

        $this->factory = $factory;
        $this->faker = \Faker\Factory::create('zc_CN');
    }

    public function load(ObjectManager $manager): void
    {
        $users = $manager->getRepository(User::class)->findAll();
        for ($i=0;$i<5;$i++)
        {
            $list = $this->factory->createList($users[rand(0,count($users,0)-1)], $this->faker->word());
            $manager->persist($list);
        }
        $manager->flush();


    }

    public function getDependencies()
    {
        return [
            UserFixtures::class
        ];
    }
}
