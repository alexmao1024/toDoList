<?php

namespace App\Controller;

use App\Entity\TaskList;
use App\Entity\User;
use App\Entity\WorkSpace;
use App\Service\ListsService;
use App\Service\UsersService;
use App\Service\WorkspaceService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;
use Symfony\Component\Routing\Annotation\Route;

class ListsController extends AbstractController
{
    private ListsService $listsService;
    private UsersService $usersService;
    private WorkspaceService $workspaceService;

    public function __construct(ListsService $listsService,UsersService $usersService,WorkspaceService $workspaceService)
    {
        $this->listsService = $listsService;
        $this->usersService = $usersService;
        $this->workspaceService = $workspaceService;
    }

    #[Route('/lists/{userId}', name: 'lists_show',methods: ['GET'])]
    public function listsShow(Request $request,int $userId): Response
    {
        $response = new Response();

        $user = $this->usersService->findUserById($userId);
        if (!$user)
        {
            throw new \Exception('USER_NOT_FOUND',404);
        }

        if ($user->getToken() !== $request->query->get('auth'))
        {
            throw new \Exception('INVALID_TOKEN',401);
        }

        $lists = $this->listsService->listsGet($userId);

        if (!$lists)
        {
            return $this->json([]);
        }

        $resultArray = array();
        /**@var TaskList $list**/
        foreach ( $lists as $key => $list )
        {
            $resultArray[$key]['id'] = $list->getId();
            $resultArray[$key]['name'] = $list->getName();
            $resultArray[$key]['done'] = $list->getDone();
            $resultArray[$key]['workspaceId'] = $list->getWorkspace()?->getId();
        }

        return $response->setContent(json_encode($resultArray));
    }

    #[Route('/lists/{userId}', name: 'lists_create', methods: ['POST'])]
    public function listsCreate(Request $request,int $userId,HubInterface $hub):Response
    {
        $lists = array();
        $requestArray = $request->toArray();
        $user = $this->usersService->findUserById($userId);
        if (!$user)
        {
            throw new \Exception('USER_NOT_FOUND',404);
        }

        if ($user->getToken() !== $request->query->get('auth'))
        {
            throw new \Exception('INVALID_TOKEN',401);
        }
        foreach ( $requestArray['lists'] as $key => $list )
        {
            $lists[$key] = $list;
            if (!$list)
            {
                throw new \Exception('POST_FAILED',400);
            }
        }

        if ($requestArray['workspaceId']!=0)
        {
            $workspace = $this->workspaceService->findWorkspaceById($requestArray['workspaceId']);
            if (!$workspace){
                throw new \Exception('WORKSPACE_NOT_FOUND',404);
            }
            $listIds = $this->listsService->listCreate($lists, $userId,$workspace);

            $allUsers = $this->handleWorkspaceUser($workspace);

            /**@var User $sharedUser**/
            foreach ( $allUsers as $sharedUser)
            {
                if ($sharedUser->getId() != $userId){

                    $update = new Update(
                        'https://todolist.com/lists/workspaces/'.$sharedUser->getId(),
                        json_encode([
                            'class'=>'lists',
                            'type'=>'create',
                            'ids'=>$listIds,
                            'lists'=>$lists,
                            'workId'=>$workspace->getId()
                        ])
                    );

                    $hub->publish($update);
                }
            }
        }else{
            $listIds = $this->listsService->listCreate($lists, $userId,null);
        }

        return $this->json([
            'ids'=>$listIds
        ]);
    }

    #[Route('/lists/{userId}', name: 'lists_remove', methods: ['DELETE'])]
    public function listsRemove(Request $request,int $userId,HubInterface $hub): Response
    {
        $requestArray = $request->toArray();
        $user = $this->usersService->findUserById($userId);
        if (!$user)
        {
            throw new \Exception('USER_NOT_FOUND',404);
        }

        $this->listsService->listsRemove($requestArray, $request, $userId);

        if ($request->query->get('workId') != 0){
            $workspace = $this->workspaceService->findWorkspaceById($request->query->get('workId'));
            if (!$workspace){
                throw new \Exception('WORKSPACE_NOT_FOUND',404);
            }
            $allUsers = $this->handleWorkspaceUser($workspace);


            /**@var User $sharedUser**/
            foreach ( $allUsers as $sharedUser)
            {

                if ($sharedUser->getId() != $userId){

                    $update = new Update(
                        'https://todolist.com/lists/workspaces/'.$sharedUser->getId(),
                        json_encode([
                            'class'=>'lists',
                            'type'=>'delete',
                            'id'=>$workspace->getId(),
                            'ids'=>$requestArray
                        ])
                    );

                    $hub->publish($update);
                }
            }
        }

        return $this->json([],200);

    }

    #[Route('/lists/{userId}', name: 'lists_update', methods: ['PATCH'])]
    public function listsUpdate(Request $request,int $userId,HubInterface $hub): Response
    {
        $requestArray = $request->toArray();
        $filter = $requestArray['filter'];
        $modifiedAllDone = $requestArray['modifiedAllDone'];
        $boolean = $requestArray['boolean'];
        $id = $requestArray['id'];
        $name = $requestArray['name'];
        $done = $requestArray['done'];
        $workId = $requestArray['workId'];

        $user = $this->usersService->findUserById($userId);

        if (!$user)
        {
            throw new \Exception('USER_NOT_FOUND',404);
        }

        if (!$modifiedAllDone){
            $list = $this->listsService->listGet($id);
            if (!$list)
            {
                throw new \Exception('LIST_NOT_FOUND',404);
            }
            if ($workId != 0){
                $this->handleWorkspaceToken($workId,$userId,$request,$list);
            }
            if ($list->getUser()->getToken() !== $request->query->get('auth'))
            {
                throw new \Exception('INVALID_TOKEN',401);
            }
            if ($filter){
                $this->listsService->listUpdate($list,$name);
            }else{
                $this->listsService->listUpdateDone($list,$done);
            }

            if ($workId != 0){
                $workspace = $this->workspaceService->findWorkspaceById($workId);
                $allUsers = $this->handleWorkspaceUser($workspace);

                /**@var User $sharedUser**/
                foreach ( $allUsers as $sharedUser){

                    if ($sharedUser->getId() != $userId) {
                        $update = new Update(
                            'https://todolist.com/lists/workspaces/' . $sharedUser->getId(),
                            json_encode([
                                'class' => 'lists',
                                'type' => 'update',
                                'id' => $id,
                                'name' => $name,
                                'done' => $done,
                                'workId' => $workId
                            ])
                        );

                        $hub->publish($update);
                    }
                }
            }
        }else{
            if ($user->getToken() !== $request->query->get('auth'))
            {
                throw new \Exception('INVALID_TOKEN',401);
            }
            if ($workId != 0){
                $workLists = $this->workspaceService->workspaceListsGet($workId);
                if (!$workLists[0])
                {
                    throw new \Exception('ALL_LISTS_NOT_FOUND',404);
                }
                $listIds = $this->listsService->listsUpdateAllDone($workLists, $boolean);
            }else{
                $lists = $this->listsService->listsGet($userId);
                if (!$lists[0])
                {
                    throw new \Exception('ALL_LISTS_NOT_FOUND',404);
                }
                $listIds = $this->listsService->listsUpdateAllDone($lists, $boolean);
            }

            if ($workId != 0){
                $workspace = $this->workspaceService->findWorkspaceById($workId);
                $allUsers = $this->handleWorkspaceUser($workspace);

                /**@var User $sharedUser**/
                foreach ( $allUsers as $sharedUser){

                    if ($sharedUser->getId() != $userId) {
                        $update = new Update(
                            'https://todolist.com/lists/workspaces/' . $sharedUser->getId(),
                            json_encode([
                                'class' => 'lists',
                                'type' => 'updateAllDone',
                                'listIds' => $listIds,
                                'boolean' => $boolean
                            ])
                        );

                        $hub->publish($update);
                    }
                }
            }
        }


        return $this->json([],200);
    }


    private function handleWorkspaceToken(int $workId,int $userId,Request $request,$list)
    {
        $workspace = $this->workspaceService->findWorkspaceById($workId);
        if (!$workspace){
            throw new \Exception('WORKSPACE_NOT_FOUND',404);
        }
        if ($workspace->getOwner()->getId() == $userId)
        {
            $request->query->set('auth',$list->getUser()->getToken());
        }else{
            foreach ($workspace->getUsers() as $user)
            {
                if ($user->getId() == $userId)
                {
                    $request->query->set('auth',$list->getUser()->getToken());
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
