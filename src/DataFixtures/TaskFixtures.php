<?php

namespace App\DataFixtures;

use App\Entity\TaskList;
use App\Entity\User;
use App\Factory\Factory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class TaskFixtures extends Fixture implements DependentFixtureInterface,FixtureGroupInterface
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
        $lists = $manager->getRepository(TaskList::class)->findAll();
        for ($i=0;$i<15;$i++)
        {
            $task = $this->factory->createTask($lists[rand(0,count($lists,0)-1)], $this->faker->word(), $this->faker->sentence(),$this->faker->dateTime(),$this->faker->dateTime());
            $manager->persist($task);
        }
        $user = $manager->getRepository(User::class)->findOneBy(['name'=>'alex']);
        $list = $manager->getRepository(TaskList::class)->findOneBy(['user' => $user]);
        $task = $this->factory->createTask($list, $this->faker->word(), $this->faker->sentence(),$this->faker->dateTime(),$this->faker->dateTime());
        $manager->persist($task);
        $manager->flush();
        $this->setReference('task_alex',$task);
    }

    public function getDependencies()
    {
        return [
            ListFixtures::class
        ];
    }

    public static function getGroups(): array
    {
        return ['tasksTest'];
    }
}
