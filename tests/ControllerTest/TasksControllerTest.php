<?php

namespace App\Tests\ControllerTest;

use App\Entity\Task;
use App\Entity\TaskList;
use App\Entity\User;
use App\Utils\TruncateEntity;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TasksControllerTest extends WebTestCase
{
    private $client;
    private $databaseTool;
    private $fixtures;
    private $truncateEntity;


    public function setUp(): void
    {
        $this->client = static::createClient();

        $this->truncateEntity = static::getContainer()->get(TruncateEntity::class);
        $this->truncateEntity->truncateEntities([
            TaskList::class,
            User::class,
            Task::class
        ]);

        $this->databaseTool = static::getContainer()->get(DatabaseToolCollection::class)->get();
        $this->fixtures = $this->databaseTool->loadAllFixtures(['usersTest', 'listsTest','tasksTest'])->getReferenceRepository();
        $this->client->catchExceptions(false);
    }

    public function testTasksShow(): void
    {
        $this->expectExceptionMessage('LIST_NOT_FOUND');
        $this->client->request('GET', '/tasks/100');
        $this->assertRouteSame('/tasks',['listId'=>'100']);
    }

    public function testTasksCreate(): void
    {
        $list = $this->fixtures->getReference('list_alex');
        $user = $this->fixtures->getReference('alexmao');
        $task = [
            'name'=>'完成任务',
            'content'=>'完成什么任务',
            'startTime'=>'2022-03-09 13:41:41',
            'endTime'=>'2022-03-09 15:41:41'
        ];
        $jsonBody = json_encode($task);
        $this->expectExceptionMessage('INVALID_TOKEN');
        $this->client->request('POST','/tasks/'.$list->getId().'?auth='.$user->getToken(),[],[],[],$jsonBody);
    }

    public function testTaskUpdate(): void
    {
        $user = $this->fixtures->getReference('alex');
        $task = $this->fixtures->getReference('task_alex');
        $newTask = [
            'name'=>'    ',
            'content'=>'完成什么任务',
            'startTime'=>'2022-03-09 13:41:41',
            'endTime'=>'2022-03-09 15:41:41',
            'done'=>true
        ];
        $jsonBody = json_encode($newTask);
        $this->expectExceptionMessage('INVALID_CHARACTER');
        $this->client->request('PUT','/tasks/'. $task->getId().'?auth='.$user->getToken(),[],[],[],$jsonBody);
    }

    public function testTaskRemove(): void
    {
        $user = $this->fixtures->getReference('alexmao');
        $this->expectExceptionMessage('TASK_NOT_FOUND');
        $this->client->request('DELETE','/tasks/100'.'?auth='.$user->getToken(),[]);
    }

    public function testTasksRemove(): void
    {
        $user = $this->fixtures->getReference('alex');
        $task = $this->fixtures->getReference('task_alex');
        $body = [$task->getId(),200];
        $jsonBody = json_encode($body);
        $this->expectExceptionMessage('TASKS_NOT_FOUND');
        $this->client->request('DELETE','/tasks'.'?auth='.$user->getToken(),[],[],[],$jsonBody);
    }
}
