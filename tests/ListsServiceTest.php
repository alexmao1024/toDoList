<?php

namespace App\Tests;

use App\Entity\TaskList;
use App\Entity\User;
use App\Factory\Factory;
use App\Service\ListsService;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ListsServiceTest extends KernelTestCase
{
    private $entityManager;

    public function setUp(): void
    {
        self::bootKernel();

        $this->entityManager = static::getContainer()->get('doctrine.orm.entity_manager');

        $this->truncateEntities([
            TaskList::class
        ]);
    }

    public function testListCreate(): void
    {
        $listsService = static::getContainer()->get(ListsService::class);
        $listsService->listCreate('面试',2);

        $lists = $this->entityManager->getRepository(TaskList::class)->findAll();
        $this->assertCount(1,$lists);
    }

    public function testListRemove(): void
    {
        $user1 = $this->entityManager->getRepository(User::class)->find(1);
        $user2 = $this->entityManager->getRepository(User::class)->find(2);
        $factory = new Factory();
        $list1 = $factory->createList($user1, '工作');
        $list2 = $factory->createList($user1, '睡觉');
        $list3 = $factory->createList($user2, '工作');
        $list4 = $factory->createList($user2, '睡觉');

        $this->entityManager->persist($list1);
        $this->entityManager->persist($list2);
        $this->entityManager->persist($list3);
        $this->entityManager->persist($list4);

        $this->entityManager->flush();

        $lists = $this->entityManager->getRepository(TaskList::class)->findAll();
        $this->assertCount(4,$lists);

        $listsService = static::getContainer()->get(ListsService::class);

        $listsService->listRemove($list3);

        $lists = $this->entityManager->getRepository(TaskList::class)->findAll();
        $this->assertCount(3,$lists);
    }

    public function testListUpdate(): void
    {
        $user1 = $this->entityManager->getRepository(User::class)->find(1);
        $user2 = $this->entityManager->getRepository(User::class)->find(2);
        $factory = new Factory();
        $list1 = $factory->createList($user1, '工作');
        $list2 = $factory->createList($user1, '睡觉');
        $list3 = $factory->createList($user2, '工作');
        $list4 = $factory->createList($user2, '睡觉');

        $this->entityManager->persist($list1);
        $this->entityManager->persist($list2);
        $this->entityManager->persist($list3);
        $this->entityManager->persist($list4);

        $this->entityManager->flush();

        $listsService = static::getContainer()->get(ListsService::class);

        $listsService->listUpdate($list3,'吃饭');

        $this->assertEquals('吃饭',$list3->getName());
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
