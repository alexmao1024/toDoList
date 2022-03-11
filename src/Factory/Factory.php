<?php

namespace App\Factory;

use App\Entity\Task;
use App\Entity\TaskList;
use App\Entity\User;

class Factory
{
    public function createUser(string $username,string $password):user
    {
        $user = new User();
        $user->setName($username);
        $user->setPassword($password);

        return $user;
    }

    public function createList(User $user,string $name,bool $done = false):TaskList
    {
        $list = new TaskList();
        $list->setUser($user);
        $list->setName($name);
        $list->setDone($done);

        return $list;
    }

    public function createTask(TaskList $list,string $name,string $content,\DateTime $startTime,\DateTime $endTime,bool $done = false):Task
    {
        $task = new Task();
        $task->setList($list);
        $task->setName($name);
        $task->setContent($content);
        $task->setStartTime($startTime);
        $task->setEndTime($endTime);
        $task->setDone($done);

        return $task;
    }
}