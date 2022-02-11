<?php

namespace App\Tests;

use App\Entity\TaskList;
use App\Entity\User;
use App\Factory\Factory;
use PHPUnit\Framework\TestCase;

class FactoryTest extends TestCase
{
    public function testFactory(): void
    {
        $factory = new Factory();
        $user = $factory->createUser('admin');

        $this->assertInstanceOf(User::class,$user);

        $list = $factory->createList($user, '工作');

        $this->assertInstanceOf(TaskList::class,$list);
    }
}
