<?php

namespace App\DataFixtures;

use App\Factory\Factory;
use App\Service\UsersService;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

class UserFixtures extends Fixture implements FixtureGroupInterface
{

    /**
     * @var Factory
     */
    private $factory;
    private $faker;
    private UsersService $usersService;

    public function __construct(Factory $factory,UsersService $usersService)
    {

        $this->factory = $factory;
        $this->faker = \Faker\Factory::create();
        $this->usersService = $usersService;
    }

    public function load(ObjectManager $manager): void
    {
        for ($i=0;$i<8;$i++)
        {
            $user = $this->factory->createUser($this->faker->userName(),$this->faker->password());
            $manager->persist($user);
            switch ($i)
            {
                case 6:
                {
                    $user=$this->usersService->createUser('alex','123123');
                    $this->setReference('alex',$user);
                    break;
                }
                case 7:
                {
                    $user=$this->usersService->createUser('alexmao','123123');
                    $this->setReference('alexmao',$user);
                    $manager->persist($user);
                    break;
                }
            }
        }
        $manager->flush();
    }

    public static function getGroups(): array
    {
        return ['usersTest'];
    }
}
