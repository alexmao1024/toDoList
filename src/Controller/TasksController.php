<?php

namespace App\Controller;

use App\Entity\Task;
use App\Service\ListsService;
use App\Service\TasksService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TasksController extends AbstractController
{
    private ListsService $listsService;
    private TasksService $tasksService;

    public function __construct(ListsService $listsService,TasksService $tasksService)
    {
        $this->listsService = $listsService;
        $this->tasksService = $tasksService;
    }

    #[Route('/tasks/{listId}', name: 'Tasks_show',methods: ['GET'])]
    public function tasksShow(Request $request,int $listId): Response
    {
        $response = new Response();

        $list = $this->listsService->listGet($listId);
        if (!$list)
        {
            throw new \Exception('LIST_NOT_FOUND',404);
        }

        if ($list->getUser()->getToken() !== $request->query->get('auth'))
        {
            throw new \Exception('INVALID_TOKEN',401);
        }

        $tasks = $this->tasksService->tasksGet($listId);

        if (!$tasks[0])
        {
            return $this->json([]);
        }

        $resultArray = array();
        /**@var Task $task**/
        foreach ( $tasks as $key => $task )
        {
            $resultArray[$key]['id'] = $task->getId();
            $resultArray[$key]['name'] = $task->getName();
            $resultArray[$key]['content'] = $task->getContent();
            $resultArray[$key]['startTime'] = $task->getStartTime();
            $resultArray[$key]['endTime'] = $task->getEndTime();
            $resultArray[$key]['done'] = $task->getDone();
        }

        return $response->setContent(json_encode($resultArray));
    }

    #[Route('/tasks/{listId}', name: 'task_create', methods: ['POST'])]
    public function taskCreate(Request $request,int $listId):Response
    {
        $requestArray = $request->toArray();
        $name = $requestArray['name'];
        $content = $requestArray['content'];
        $startTime = $requestArray['startTime'];
        $endTime = $requestArray['endTime'];

        $list = $this->listsService->listGet($listId);
        if (!$list)
        {
            throw new \Exception('LIST_NOT_FOUND',404);
        }

        if ($list->getUser()->getToken() !== $request->query->get('auth'))
        {
            throw new \Exception('INVALID_TOKEN',401);
        }

        $task = $this->tasksService->taskCreate($name, $content, $startTime, $endTime, $listId);

        return $this->json([
            'id'=>$task->getId()
        ]);
    }

    #[Route('/tasks', name: 'tasks_remove', methods: ['DELETE'])]
    public function tasksRemove(Request $request): Response
    {
        $requestArray = $request->toArray();

        $this->tasksService->tasksRemove($requestArray,$request->query->get('auth'));

        return $this->json([],200);

    }


    #[Route('/tasks', name: 'tasks_update', methods: ['PATCH'])]
    public function tasksUpdate(Request $request): Response
    {
        $requestArray = $request->toArray();
        $filter = $requestArray['filter'];
        $modifiedAllDone = $requestArray['modifiedAllDone'];
        $boolean = $requestArray['boolean'];
        $taskId = $requestArray['taskId'];
        $listId = $requestArray['listId'];
        $name = $requestArray['name'];
        $content = $requestArray['content'];
        $startTime = $requestArray['startTime'];
        $endTime = $requestArray['endTime'];
        $done = $requestArray['done'];

        if (!$modifiedAllDone){
            $task = $this->tasksService->taskGet($taskId);
            if (!$task)
            {
                throw new \Exception('TASK_NOT_FOUND',404);
            }
            if ($task->getList()->getUser()->getToken() !== $request->query->get('auth'))
            {
                throw new \Exception('INVALID_TOKEN',401);
            }
            if ($filter){
                $this->tasksService->taskUpdate($task,$name,$content,$startTime,$endTime);
            }else{
                $this->tasksService->taskUpdateDone($task,$done);
            }
        }else{
            $list = $this->listsService->listGet($listId);
            if (!$list)
            {
                throw new \Exception('LIST_NOT_FOUND',404);
            }
            if ($list->getUser()->getToken() !== $request->query->get('auth'))
            {
                throw new \Exception('INVALID_TOKEN',401);
            }
            $tasks = $this->tasksService->tasksGet($listId);
            if (!$tasks[0])
            {
                throw new \Exception('ALL_TASKS_NOT_FOUND',404);
            }

            $this->tasksService->tasksUpdateAllDone($tasks,$boolean);
        }
        return $this->json([],200);
    }
}
