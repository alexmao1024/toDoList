<?php

namespace App\Tests\ControllerTest;

use App\Entity\Task;
use App\Entity\TaskList;
use App\Entity\User;
use App\Utils\TruncateEntity;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class UsersControllerTest extends WebTestCase
{
    private $client;
    private $databaseTool;
    private $truncateEntity;

    public function setUp(): void
    {
        $this->client = static::createClient();

        $this->truncateEntity = static::getContainer()->get(TruncateEntity::class);
        $this->truncateEntity->truncateEntities([
            User::class,
            TaskList::class,
            Task::class
        ]);

        $this->databaseTool = static::getContainer()->get(DatabaseToolCollection::class)->get();
        $this->databaseTool->loadAllFixtures(['usersTest','listsTest','tasksTest']);
        $this->client->catchExceptions(false);
    }

    public function testLogin(): void
    {
        $body1 = '{"username": "adsadsa5524","password": "2315355"}';
        $this->expectExceptionMessage('USERNAME_NOT_FOUND');
        $this->client->request('POST', '/login',[],[],[],$body1);
        $body2 = '{"username": "alexmao","password": "2315355"}';
        $this->expectExceptionMessage('INVALID_PASSWORD');
        $this->client->request('POST', '/login',[],[],[],$body2);
    }

    public function testSignUp(): void
    {
        $body = '{"username": "alex","password": "2315355"}';
        $this->expectExceptionMessage('USERNAME_EXISTS');
        $this->client->request('POST', '/signUp',[],[],[],$body);
    }
}
