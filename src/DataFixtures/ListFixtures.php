<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Factory\Factory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class ListFixtures extends Fixture implements DependentFixtureInterface,FixtureGroupInterface
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
            $list = $this->factory->createList($users[rand(0,count($users,0)-3)], $this->faker->word());
            $manager->persist($list);
        }
        $user = $manager->getRepository(User::class)->findOneBy(['name'=>'alex']);
        $list = $this->factory->createList($user, $this->faker->word());
        $this->setReference('list_alex',$list);
        $manager->persist($list);

        $manager->flush();


    }

    public function getDependencies()
    {
        return [
            UserFixtures::class
        ];
    }

    public static function getGroups(): array
    {
        return ['listsTest'];
    }
}
