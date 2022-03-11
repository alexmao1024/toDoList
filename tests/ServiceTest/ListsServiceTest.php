<?php

namespace App\Tests\ServiceTest;

use App\Entity\Task;
use App\Entity\TaskList;
use App\Entity\User;
use App\Service\ListsService;
use App\Utils\TruncateEntity;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;


class ListsServiceTest extends KernelTestCase
{
    private $entityManager;
    private $databaseTool;
    private $listsService;
    private $fixtures;
    private $truncateEntity;

    public function setUp(): void
    {
        self::bootKernel();

        $this->entityManager = static::getContainer()->get('doctrine.orm.entity_manager');
        $this->truncateEntity = static::getContainer()->get(TruncateEntity::class);
        $this->truncateEntity->truncateEntities([
            TaskList::class,
            Task::class,
            User::class
        ]);

        $this->databaseTool = static::getContainer()->get(DatabaseToolCollection::class)->get();
        $this->listsService = static::getContainer()->get(ListsService::class);
        $this->fixtures = $this->databaseTool->loadAllFixtures(['usersTest', 'listsTest','tasksTest'])->getReferenceRepository();


    }

    public function testListCreate(): void
    {

        $user1 = $this->fixtures->getReference('alex');
        $user2 = $this->fixtures->getReference('alexmao');

        $listIds1 = $this->listsService->listCreate(['面试','上班'], $user1->getId());
        $list1 = $this->entityManager->getRepository(TaskList::class)->find($listIds1[0]);
        $list2 = $this->entityManager->getRepository(TaskList::class)->find($listIds1[1]);
        $this->assertEquals('面试',$list1->getName());
        $this->assertEquals('上班',$list2->getName());

        $this->expectExceptionMessage('INVALID_CHARACTER');
        $this->listsService->listCreate(['        '], $user2->getId());

        $uselessData = $this->generateUselessData(51);
        $this->expectExceptionMessage('LENGTH_TOO_LARGE');
        $this->listsService->listCreate([$uselessData], $user2->getId());

    }


    public function testListRemove(): void
    {
        $list = $this->fixtures->getReference('list_alex');
        $listsOrigin = $this->entityManager->getRepository(TaskList::class)->findAll();

        $this->listsService->listRemove($list);

        $lists = $this->entityManager->getRepository(TaskList::class)->findAll();
        $this->assertCount(count($listsOrigin,0)-1,$lists);

    }

    public function testListsUpdateAllDone(): void
    {
        $user = $this->fixtures->getReference('alex');
        $lists = $this->listsService->listsGet($user->getId());
        $this->listsService->listsUpdateAllDone($lists,true);

        /**@var User $user**/
        foreach ($user->getTaskLists() as $list)
        {
            $this->assertEquals(true,$list->getDone());
        }
    }

    private function generateUselessData(int $num): string
    {
        $ch = 'qwertyuiopasdfghjklzxcvbnmQWERTYUIOPASDFGHJKLZXCVBNM';
        $str = '';
        for ($i=0;$i<$num;$i++)
        {
            $str .= $ch[rand(0,strlen($ch)-1)];
        }
        return $str;
    }
}
