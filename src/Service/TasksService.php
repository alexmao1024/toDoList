<?php

namespace App\Service;

use App\Entity\Task;
use App\Entity\TaskList;
use DateTime;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

class TasksService
{
    private EntityManagerInterface $entityManager;
    private WorkspaceService $workspaceService;

    public function __construct(EntityManagerInterface $entityManager,WorkspaceService $workspaceService)
    {
        $this->entityManager = $entityManager;
        $this->workspaceService = $workspaceService;
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

    public function taskCreate(string $name,string|null $content,string|null $startTimeStr,string|null $endTimeStr,int $listId): Task|null
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
        $startTime = $startTimeStr? DateTime::createFromFormat('Y/m/d H:i:s', $startTimeStr): null;
        $endTime = $endTimeStr? DateTime::createFromFormat('Y/m/d H:i:s', $endTimeStr): null;
        $task->setStartTime($startTime);
        $task->setEndTime($endTime);
        $task->setDone(false);
        $this->entityManager->persist($task);
        $this->entityManager->flush();
        return $task;
    }


    public function tasksRemove(array $taskIds,Request $request,int $userId)
    {
        foreach ( $taskIds as $taskId){
            $task = $this->taskGet($taskId);
            if (!$task)
            {
                throw new \Exception('TASKS_NOT_FOUND',404);
            }

            $workId = $request->query->get('workId');
            if ($workId!=0)
            {
                $workspace = $this->workspaceService->findWorkspaceById($workId);
                if (!$workspace){
                    throw new \Exception('WORKSPACE_NOT_FOUND',404);
                }
                if ($workspace->getOwner()->getId() == $userId)
                {
                    $request->query->set('auth',$task->getList()->getUser()->getToken());
                }else{
                    foreach ($workspace->getUsers() as $user)
                    {
                        if ($user->getId() == $userId)
                        {
                            $request->query->set('auth',$task->getList()->getUser()->getToken());
                            break;
                        }
                    }
                }
            }
            if ($task->getList()->getUser()->getToken() !== $request->query->get('auth'))
            {
                throw new \Exception('INVALID_TOKEN',401);
            }
            $this->entityManager->remove($task);
        }
        $this->entityManager->flush();
    }

    public function taskUpdate(Task $task,string $name,string|null $content,string|null $startTimeStr,string|null $endTimeStr)
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
        $startTime = $startTimeStr? DateTime::createFromFormat('Y/m/d H:i:s', $startTimeStr): null;
        $endTime = $endTimeStr? DateTime::createFromFormat('Y/m/d H:i:s', $endTimeStr): null;
        $task->setStartTime($startTime);
        $task->setEndTime($endTime);

        $this->entityManager->flush();
    }

    public function taskUpdateDone(Task $task,bool $boolean)
    {
        $task->setDone($boolean);

        $this->entityManager->flush();
    }

    public function tasksUpdateAllDone(Collection|array $tasks,bool $boolean): array
    {
        $taskIds = [];
        foreach ( $tasks as $key => $task )
        {
            /**@var Task $task**/
            $task->setDone($boolean);
            $taskIds[$key] = $task->getId();
        }

        $this->entityManager->flush();

        return $taskIds;
    }
}