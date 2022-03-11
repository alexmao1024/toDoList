<?php

namespace App\Tests\ControllerTest;

use App\Entity\Task;
use App\Entity\TaskList;
use App\Entity\User;
use App\Utils\TruncateEntity;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ListsControllerTest extends WebTestCase
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

    public function testListsShow(): void
    {
        $this->expectExceptionMessage('USER_NOT_FOUND');
        $this->client->request('GET', '/lists/100');
        $this->assertRouteSame('/lists',['userId'=>'100']);
    }

    public function testListsCreate(): void
    {
        $user = $this->fixtures->getReference('alexmao');
        $lists = ['','我的娃','2414'];
        $body = ['lists'=>$lists];
        $jsonBody = json_encode($body);
        $this->expectExceptionMessage('POST_FAILED');
        $this->client->request('POST','/lists/'.$user->getId().'?auth='.$user->getToken(),[],[],[],$jsonBody);
    }

    public function testListUpdate(): void
    {
        $user = $this->fixtures->getReference('alex');
        $list = $this->fixtures->getReference('list_alex');
        $body = '{"filter":true,"modifiedAllDone":false,"boolean":null,"id":'.$list->getId().',"name":"   ","done":null}';
        $this->expectExceptionMessage('INVALID_CHARACTER');
        $this->client->request('PATCH','/lists/'.$user->getId().'?auth='.$user->getToken(),[],[],[],$body);
    }

    public function testListUpdateDone():void
    {
        $user = $this->fixtures->getReference('alexmao');
        $body = '{"filter":false,"modifiedAllDone":true,"boolean":true,"id":null,"name":null,"done":null}';
        $this->expectExceptionMessage('ALL_LISTS_NOT_FOUND');
        $this->client->request('PATCH','/lists/'.$user->getId().'?auth='.$user->getToken(),[],[],[],$body);
    }

    public function testListRemove(): void
    {
        $user1 = $this->fixtures->getReference('alexmao');
        $user2 = $this->fixtures->getReference('alex');
        $list = $this->fixtures->getReference('list_alex');
        $this->expectExceptionMessage('INVALID_TOKEN');
        $this->client->request('DELETE','/lists/'.$list->getId().'/'.$user2->getId().'?auth='.$user1->getToken());
    }

    public function testListsRemove(): void
    {
        $user = $this->fixtures->getReference('alex');
        $list = $this->fixtures->getReference('list_alex');
        $body = [$list->getId(),200];
        $jsonBody = json_encode($body);
        $this->expectExceptionMessage('LISTS_NOT_FOUND');
        $this->client->request('DELETE','/lists/'.$user->getId().'?auth='.$user->getToken(),[],[],[],$jsonBody);
    }
}
