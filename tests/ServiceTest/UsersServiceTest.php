<?php

namespace App\Tests\ServiceTest;

use App\Entity\Task;
use App\Entity\TaskList;
use App\Entity\User;
use App\Service\UsersService;
use App\Utils\TruncateEntity;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class UsersServiceTest extends KernelTestCase
{
    private $databaseTool;
    private $usersService;
    private $truncateEntity;

    public function setUp(): void
    {
        self::bootKernel();

        $this->truncateEntity = static::getContainer()->get(TruncateEntity::class);
        $this->truncateEntity->truncateEntities([
            User::class,
            TaskList::class,
            Task::class
        ]);

        $this->databaseTool = static::getContainer()->get(DatabaseToolCollection::class)->get();
        $this->usersService = static::getContainer()->get(UsersService::class);
        $this->databaseTool->loadAllFixtures(['usersTest','listsTest','tasksTest']);
    }

    public function testFindUserByName(): void
    {
        $sql = '1\' OR \'1\'=\'1';
        $this->assertEmpty($this->usersService->findUserByName($sql));
    }

    public function testCreateUser(): void
    {
        $this->expectExceptionMessage('INVALID_CHARACTER');
        $this->usersService->createUser('a d m i n', '123123');
        $this->expectExceptionMessage('INVALID_CHARACTER');
        $this->usersService->createUser('中文用户名', '123123');
        $this->expectExceptionMessage('LENGTH_TOO_LARGE');
        $this->usersService->createUser($this->generateUselessData(51), '123123');

        $user = $this->usersService->createUser('admin', '123123');
        $this->assertEquals($user->getId(),substr($user->getToken(),0,1));
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
