<?php

namespace App\Tests\ServiceTest;

use App\Entity\Task;
use App\Entity\TaskList;
use App\Entity\User;
use App\Service\TasksService;
use App\Utils\TruncateEntity;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class TasksServiceTest extends KernelTestCase
{
    private $entityManager;
    private $databaseTool;
    private $tasksService;
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
        $this->tasksService = static::getContainer()->get(TasksService::class);
        $this->fixtures = $this->databaseTool->loadAllFixtures(['usersTest', 'listsTest','tasksTest'])->getReferenceRepository();


    }

    public function testTaskCreate(): void
    {
        /**@var TaskList $list**/
        $list = $this->fixtures->getReference('list_alex');

        $task = $this->tasksService->taskCreate('   参加  会议    ', '准时参加不迟到', '2022-03-09 10:10:40', '2022-03-09 12:10:40', $list->getId());
        $taskFind = $this->entityManager->getRepository(Task::class)->find($task->getId());
        $this->assertEquals('参加  会议',$taskFind->getName());

        $this->expectExceptionMessage('INVALID_CHARACTER');
        $this->tasksService->taskCreate('  ', '准时参加不迟到', '2022-03-09 10:10:40', '2022-03-09 12:10:40', $list->getId());
        $this->expectExceptionMessage('LENGTH_TOO_LARGE');
        $this->tasksService->taskCreate($this->generateUselessData(51), '准时参加不迟到', '2022-03-09 10:10:40', '2022-03-09 12:10:40', $list->getId());
    }


    public function testTaskUpdate(): void
    {
        /**@var Task $task**/
        $task = $this->fixtures->getReference('task_alex');

        $this->tasksService->taskUpdate($task,'   参加  会议    ', '准时参加不迟到', '2022-03-09 10:10:40', '2022-03-09 12:10:40',true);
        $taskFind = $this->entityManager->getRepository(Task::class)->find($task->getId());
        $this->assertEquals('参加  会议',$taskFind->getName());

        $this->expectExceptionMessage('INVALID_CHARACTER');
        $this->tasksService->taskUpdate($task,'  ', '准时参加不迟到', '2022-03-09 10:10:40', '2022-03-09 12:10:40', true);
        $this->expectExceptionMessage('LENGTH_TOO_LARGE');
        $this->tasksService->taskUpdate($task,$this->generateUselessData(51), '准时参加不迟到', '2022-03-09 10:10:40', '2022-03-09 12:10:40', true);

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
