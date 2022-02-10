<?php

namespace App\Tests;

use App\Entity\Task;
use App\Entity\User;
use App\Factory\Factory;
use App\Repository\TaskRepository;
use App\Repository\UserRepository;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class EntityTest extends KernelTestCase
{

    private $entityManager;

    public function setUp(): void
    {
        self::bootKernel();

        $this->entityManager = static::getContainer()->get('doctrine.orm.entity_manager');

        $this->truncateEntities([
            User::class,
            Task::class
        ]);
    }

    public function testUserEntityManager(): void
    {
        $factory = new Factory();
        $user1 = $factory->createUser('admin1');
        $user2 = $factory->createUser('admin2');
        $user3 = $factory->createUser('admin3');
        $user4 = $factory->createUser('admin4');

        $this->entityManager->persist($user1);
        $this->entityManager->persist($user2);
        $this->entityManager->persist($user3);
        $this->entityManager->persist($user4);

        $this->entityManager->flush();

        $userRepo = static::getContainer()->get(UserRepository::class);

        $this->assertInstanceOf(UserRepository::class,$userRepo);

        $users = $userRepo->findAll();
        $this->assertCount(4,$users);
    }

    public function testTaskEntityManager(): void
    {
        $factory = new Factory();
        $user = $factory->createUser('admin');
        $this->assertInstanceOf(User::class,$user);

        $this->entityManager->persist($user);

        $task1 = $factory->createTask($user, '工作', '上班', '记得别迟到', DateTime::createFromFormat('Y-m-d H:i:s', date('Y-m-d H:i:s')));
        $task2 = $factory->createTask($user, '工作', '吃中饭', '在公司吃中饭', DateTime::createFromFormat('Y-m-d H:i:s', date('Y-m-d H:i:s')));
        $task3 = $factory->createTask($user, '工作', '下班', '早点回家', DateTime::createFromFormat('Y-m-d H:i:s', date('Y-m-d H:i:s')));
        $task4 = $factory->createTask($user, '工作', '加班', '熬夜加班', DateTime::createFromFormat('Y-m-d H:i:s', date('Y-m-d H:i:s')));

        $this->entityManager->persist($task1);
        $this->entityManager->persist($task2);
        $this->entityManager->persist($task3);
        $this->entityManager->persist($task4);

        $this->entityManager->flush();

        $taskRepo = static::getContainer()->get(TaskRepository::class);

        $this->assertInstanceOf(TaskRepository::class,$taskRepo);

        $tasks = $taskRepo->findAll();
        $this->assertCount(4,$tasks);
    }

    private function truncateEntities(array  $entities)
    {
        $connection = $this->entityManager->getConnection();
        $databasePlatform = $connection->getDatabasePlatform();
        if ($databasePlatform->supportsForeignKeyConstraints()){
            $connection->executeQuery('SET FOREIGN_KEY_CHECKS=0');
        }
        foreach ($entities as $entity){
            $query = $databasePlatform->getTruncateTableSQL(
                $this->entityManager->getClassMetadata($entity)->getTableName()
            );
            $connection->executeQuery($query);
        }
        if ($databasePlatform->supportsForeignKeyConstraints()){
            $connection->executeQuery('SET FOREIGN_KEY_CHECKS=1');
        }
    }
}
