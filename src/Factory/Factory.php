<?php

namespace App\Factory;

use App\Entity\Task;
use App\Entity\TaskList;
use App\Entity\User;

class Factory
{
    public function createUser(string $username):user
    {
        $user = new User();
        $user->setName($username);

        return $user;
    }

    public function createList(User $user,string $name):TaskList
    {
        $list = new TaskList();
        $list->setUser($user);
        $list->setName($name);

        return $list;
    }

    public function createTask(TaskList $list,string $name,string $context,\DateTime $startTime):Task
    {
        $task = new Task();
        $task->setList($list);
        $task->setName($name);
        $task->setContext($context);
        $task->setStartTime($startTime);

        return $task;
    }
}