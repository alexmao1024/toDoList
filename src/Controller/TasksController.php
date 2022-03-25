<?php

namespace App\Controller;

use App\Entity\Task;
use App\Entity\User;
use App\Entity\WorkSpace;
use App\Service\ListsService;
use App\Service\TasksService;
use App\Service\WorkspaceService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;
use Symfony\Component\Routing\Annotation\Route;

class TasksController extends AbstractController
{
    private ListsService $listsService;
    private TasksService $tasksService;
    private WorkspaceService $workspaceService;

    public function __construct(ListsService $listsService,TasksService $tasksService,WorkspaceService $workspaceService)
    {
        $this->listsService = $listsService;
        $this->tasksService = $tasksService;
        $this->workspaceService = $workspaceService;
    }

    #[Route('/tasks/{listId}', name: 'Tasks_show',methods: ['GET'])]
    public function tasksShow(Request $request,int $listId): Response
    {
        $response = new Response();
        $workId = $request->query->get('workId');
        $userId = $request->query->get('userId');
        $list = $this->listsService->listGet($listId);
        if (!$list)
        {
            throw new \Exception('LIST_NOT_FOUND',404);
        }

        if ($workId != 0){
            $this->handleWorkspaceToken((int)$workId,(int)$userId,$request,null,$list);
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
            $resultArray[$key]['startTime'] = $task->getStartTime()?$task->getStartTime()->format('Y/m/d H:i:s'):null;
            $resultArray[$key]['endTime'] = $task->getEndTime()?$task->getEndTime()->format('Y/m/d H:i:s'):null;
            $resultArray[$key]['done'] = $task->getDone();
        }

        return $response->setContent(json_encode($resultArray));
    }

    #[Route('/tasks/{listId}', name: 'task_create', methods: ['POST'])]
    public function taskCreate(Request $request,int $listId,HubInterface $hub):Response
    {
        $requestArray = $request->toArray();
        $name = $requestArray['name'];
        $content = $requestArray['content'];
        $startTime = $requestArray['startTime'];
        $endTime = $requestArray['endTime'];
        $userId = $requestArray['userId'];
        $workId = $requestArray['workId'];

        $list = $this->listsService->listGet($listId);
        if (!$list)
        {
            throw new \Exception('LIST_NOT_FOUND',404);
        }
        if ($workId != 0)
        {
            $this->handleWorkspaceToken($workId,$userId,$request,null,$list);
        }

        if ($list->getUser()->getToken() !== $request->query->get('auth'))
        {
            throw new \Exception('INVALID_TOKEN',401);
        }

        $task = $this->tasksService->taskCreate($name, $content, $startTime, $endTime, $listId);

        if ($workId != 0){
            $workspace = $this->workspaceService->findWorkspaceById($workId);

            $allUsers = $this->handleWorkspaceUser($workspace);

            /**@var User $sharedUser**/
            foreach ( $allUsers as $sharedUser) {

                if ($sharedUser->getId() != $userId) {
                    $update = new Update(
                        'https://todolist.com/tasks/workspaces/' . $sharedUser->getId(),
                        json_encode([
                            'class'=>'tasks',
                            'type' => 'create',
                            'id' => $task->getId(),
                            'name' => $name,
                            'content' => $content,
                            'startTime' => $startTime,
                            'endTime' => $endTime,
                            'workId' => $task->getList()->getWorkspace()?->getId(),
                            'listId' => $task->getList()->getId()
                        ])
                    );

                    $hub->publish($update);
                }
            }
        }

        return $this->json([
            'id'=>$task->getId()
        ]);
    }

    #[Route('/tasks/{userId}', name: 'tasks_remove', methods: ['DELETE'])]
    public function tasksRemove(Request $request,int $userId,HubInterface $hub): Response
    {
        $requestArray = $request->toArray();

        $this->tasksService->tasksRemove($requestArray,$request,$userId);

        $workId = $request->query->get('workId');
        if ($workId != 0){
            $workspace = $this->workspaceService->findWorkspaceById($workId);

            $allUsers = $this->handleWorkspaceUser($workspace);

            /**@var User $sharedUser**/
            foreach ( $allUsers as $sharedUser) {

                if ($sharedUser->getId() != $userId) {
                    $update = new Update(
                        'https://todolist.com/tasks/workspaces/' . $sharedUser->getId(),
                        json_encode([
                            'class'=>'tasks',
                            'type'=>'delete',
                            'ids'=>$requestArray
                        ])
                    );

                    $hub->publish($update);
                }
            }
        }

        return $this->json([],200);

    }


    #[Route('/tasks', name: 'tasks_update', methods: ['PATCH'])]
    public function tasksUpdate(Request $request,HubInterface $hub): Response
    {
        $requestArray = $request->toArray();
        $filter = $requestArray['filter'];
        $modifiedAllDone = $requestArray['modifiedAllDone'];
        $boolean = $requestArray['boolean'];
        $taskId = $requestArray['taskId'];
        $listId = $requestArray['listId'];
        $userId = $requestArray['userId'];
        $workId = $requestArray['workId'];
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
            if ($workId != 0){
                $this->handleWorkspaceToken($workId,$userId,$request,$task,null);
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

            if ($workId != 0){
                $workspace = $this->workspaceService->findWorkspaceById($workId);
                $allUsers = $this->handleWorkspaceUser($workspace);

                /**@var User $sharedUser**/
                foreach ( $allUsers as $sharedUser){

                    if ($sharedUser->getId() != $userId) {
                        $update = new Update(
                            'https://todolist.com/tasks/workspaces/' . $sharedUser->getId(),
                            json_encode([
                                'class'=>'tasks',
                                'type'=>'update',
                                'id'=>$taskId,
                                'name'=>$name,
                                'content'=>$content,
                                'startTime'=>$startTime,
                                'endTime'=>$endTime,
                                'done'=>$done
                            ])
                        );

                        $hub->publish($update);
                    }
                }
            }
        }else{
            $list = $this->listsService->listGet($listId);
            if (!$list)
            {
                throw new \Exception('LIST_NOT_FOUND',404);
            }
            if ($workId != 0) {
                $this->handleWorkspaceToken($workId, $userId, $request, null, $list);
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

            $taskIds = $this->tasksService->tasksUpdateAllDone($tasks, $boolean);

            if ($workId != 0){
                $workspace = $this->workspaceService->findWorkspaceById($workId);
                $allUsers = $this->handleWorkspaceUser($workspace);

                /**@var User $sharedUser**/
                foreach ( $allUsers as $sharedUser){

                    if ($sharedUser->getId() != $userId) {
                        $update = new Update(
                            'https://todolist.com/tasks/workspaces/' . $sharedUser->getId(),
                            json_encode([
                                'class'=>'tasks',
                                'type'=>'updateAllDone',
                                'taskIds'=> $taskIds,
                                'boolean'=> $boolean
                            ])
                        );

                        $hub->publish($update);
                    }
                }
            }

        }
        return $this->json([],200);
    }

    private function handleWorkspaceToken(int $workId,int $userId,Request $request,$task,$list)
    {
        $workspace = $this->workspaceService->findWorkspaceById($workId);
        if (!$workspace){
            throw new \Exception('WORKSPACE_NOT_FOUND',404);
        }
        if ($workspace->getOwner()->getId() == $userId)
        {
            if ($list){
                $request->query->set('auth',$list->getUser()->getToken());
            }
            if ($task){
                $request->query->set('auth',$task->getList()->getUser()->getToken());
            }
        } else{
            foreach ($workspace->getUsers() as $user)
            {
                if ($user->getId() == $userId)
                {
                    if ($list){
                        $request->query->set('auth',$list->getUser()->getToken());
                    }
                    if ($task){
                        $request->query->set('auth',$task->getList()->getUser()->getToken());
                    }
                    break;
                }
            }
        }
    }

    private function handleWorkspaceUser(WorkSpace $workspace): array
    {
        $allUsers = [];
        foreach ( $workspace->getUsers() as $key => $user)
        {
            $allUsers[$key] = $user;
        }
        array_push($allUsers,$workspace->getOwner());
        return $allUsers;
    }
}
