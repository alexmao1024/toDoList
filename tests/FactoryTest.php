<?php

namespace App\Tests;

use App\Entity\Task;
use App\Entity\User;
use App\Factory\Factory;
use DateTime;
use PHPUnit\Framework\TestCase;

class FactoryTest extends TestCase
{
    public function testFactory(): void
    {
        $factory = new Factory();
        $user = $factory->createUser('admin');

        $this->assertInstanceOf(User::class,$user);

        $task = $factory->createTask($user, '工作', '去上班', '记得按时上班不要迟到', DateTime::createFromFormat('Y-m-d H:i:s', date('Y-m-d H:i:s')));

        $this->assertInstanceOf(Task::class,$task);
    }
}
