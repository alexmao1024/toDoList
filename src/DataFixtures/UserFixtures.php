<?php

namespace App\DataFixtures;

use App\Factory\Factory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class UserFixtures extends Fixture
{

    /**
     * @var Factory
     */
    private $factory;
    private $faker;

    public function __construct(Factory $factory)
    {

        $this->factory = $factory;
        $this->faker = \Faker\Factory::create();
    }

    public function load(ObjectManager $manager): void
    {
        for ($i=0;$i<8;$i++)
        {
            $user = $this->factory->createUser($this->faker->userName());
            $manager->persist($user);
        }
        $manager->flush();
    }

}
