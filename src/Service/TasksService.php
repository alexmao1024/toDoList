<?php

namespace App\Service;

use App\Entity\Task;
use App\Entity\TaskList;
use DateTime;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;

class TasksService
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function taskGet(int $taskId): Task|null
    {
        return $this->entityManager->getRepository(Task::class)->find($taskId);
    }

    public function tasksGet(int $listId): Collection|array|null
    {
        /**@var TaskList $list**/
        $list = $this->entityManager->getRepository(TaskList::class)->find($listId);

        return $list->getTasks();
    }

    public function taskCreate(string $name,string $content,string $startTimeStr,string $endTimeStr,int $listId): Task|null
    {
        $list = $this->entityManager->getRepository(TaskList::class)->find($listId);

        $task = new Task();
        $task->setList($list);
        $trimName = trim($name);
        if (!$trimName) {
            throw new \Exception('INVALID_CHARACTER',400);
        }
        if (strlen($trimName)>50)
        {
            throw new \Exception('LENGTH_TOO_LARGE',400);
        }
        $task->setName($trimName);
        $task->setContent($content);
        $startTime = DateTime::createFromFormat('Y-m-d H:i:s', $startTimeStr);
        $endTime = DateTime::createFromFormat('Y-m-d H:i:s', $endTimeStr);
        $task->setStartTime($startTime);
        $task->setEndTime($endTime);
        $task->setDone(false);
        $this->entityManager->persist($task);
        $this->entityManager->flush();
        return $task;
    }


    public function tasksRemove(array $taskIds,string $token)
    {
        foreach ( $taskIds as $taskId){
            $task = $this->taskGet($taskId);
            if (!$task)
            {
                throw new \Exception('TASKS_NOT_FOUND',404);
            }

            if ($task->getList()->getUser()->getToken() !== $token)
            {
                throw new \Exception('INVALID_TOKEN',401);
            }
            $this->entityManager->remove($task);
        }
        $this->entityManager->flush();
    }

    public function taskUpdate(Task $task,string $name,string $content,string $startTimeStr,string $endTimeStr)
    {
        $trimName = trim($name);
        if (!$trimName) {
            throw new \Exception('INVALID_CHARACTER',400);
        }
        if (strlen($trimName)>50)
        {
            throw new \Exception('LENGTH_TOO_LARGE',400);
        }
        $task->setname($trimName);
        $task->setContent($content);
        $startTime = DateTime::createFromFormat('Y-m-d H:i:s', $startTimeStr);
        $endTime = DateTime::createFromFormat('Y-m-d H:i:s', $endTimeStr);
        $task->setStartTime($startTime);
        $task->setEndTime($endTime);

        $this->entityManager->flush();
    }

    public function taskUpdateDone(Task $task,bool $boolean)
    {
        $task->setDone($boolean);

        $this->entityManager->flush();
    }

    public function tasksUpdateAllDone(Collection|array $tasks,bool $boolean)
    {
        foreach ( $tasks as $task )
        {
            /**@var Task $task**/
            $task->setDone($boolean);
        }

        $this->entityManager->flush();
    }
}