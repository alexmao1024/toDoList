<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\WorkSpace;
use App\Service\ListsService;
use App\Service\UsersService;
use App\Service\WorkspaceService;
use Doctrine\Common\Collections\Collection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;
use Symfony\Component\Routing\Annotation\Route;

class WorkSpacesController extends AbstractController
{
    private UsersService $usersService;
    private WorkspaceService $workspaceService;
    private ListsService $listsService;

    public function __construct(UsersService $usersService,WorkspaceService $workspaceService,ListsService $listsService)
    {
        $this->usersService = $usersService;
        $this->workspaceService = $workspaceService;
        $this->listsService = $listsService;
    }

    #[Route('/workSpaces/{userId}', name: 'workspaces_show',methods: ['GET'])]
    public function workspacesShow(Request $request,int $userId): Response
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

        $workspaces = $user->getSelfWorkSpaces();
        $sharedWorkspaces = $user->getWorkSpaces();

        if (!$workspaces[0])
        {
            if (!$sharedWorkspaces[0])
            {
                return $this->json([]);
            }
            return $response->setContent(json_encode($this->handleReturnArray($sharedWorkspaces)));
        }
        $selfWorkspacesReturn = $this->handleReturnArray($workspaces);
        $sharedWorkspacesReturn = $this->handleReturnArray($sharedWorkspaces);

        $allWorkspaces = array_merge($selfWorkspacesReturn, $sharedWorkspacesReturn);

        return $response->setContent(json_encode($allWorkspaces));
    }

    #[Route('/workSpaces/{userId}', name: 'workspace_create', methods: ['POST'])]
    public function workspaceCreate(Request $request,int $userId,HubInterface $hub): Response
    {
        $requestArray = $request->toArray();
        $name = $requestArray['name'];
        $sharedUsers = $requestArray['sharedUsers'];
        $user = $this->usersService->findUserById($userId);
        if (!$user)
        {
            throw new \Exception('USER_NOT_FOUND',404);
        }

        if ($user->getToken() !== $request->query->get('auth'))
        {
            throw new \Exception('INVALID_TOKEN',401);
        }

        $workSpace = $this->workspaceService->workspaceCreate($name, $user, $sharedUsers);

        foreach ( $workSpace->getUsers() as $sharedUser)
        {
            $update = new Update(
                'https://todolist.com/workspaces/'.$sharedUser->getId(),
                json_encode([
                    'class'=>'workspace',
                    'type'=>'create',
                    'id'=>$workSpace->getId(),
                    'name'=>$workSpace->getName(),
                    'owner'=>$workSpace->getOwner()->getName(),
                    'sharedUsers'=>$sharedUsers
                ])
            );

            $hub->publish($update);
        }

        return $this->json([
            'id'=>$workSpace->getId()
        ]);
    }

    #[Route('/workSpaces/{userId}', name: 'workspace_update', methods: ['PATCH'])]
    public function workspaceUpdate(Request $request,int $userId,HubInterface $hub): Response
    {
        $requestArray = $request->toArray();
        $ids = $requestArray['ids'];
        $type = $requestArray['type'];
        $name = $requestArray['name'];
        $workId = $requestArray['workId'];
        $names = $requestArray['names'];
        $user = $this->usersService->findUserById($userId);
        if (!$user)
        {
            throw new \Exception('USER_NOT_FOUND',404);
        }

        if ($user->getToken() !== $request->query->get('auth'))
        {
            throw new \Exception('INVALID_TOKEN',401);
        }
        $workspace = $this->workspaceService->findWorkspaceById($workId);
        if (!$workspace){
            throw new \Exception('WORKSPACE_NOT_FOUND',404);
        }

        if ($type){
            $this->handleWorkspaceContent($userId,$type, $ids, $workspace, $request, $names, $hub);
        }

        if ($name){
            if ($workspace->getOwner()->getId() != $userId)
            {
                throw new \Exception('INVALID_TOKEN',401);
            }
            $trimName = trim($name);
            if (!$trimName) {
                throw new \Exception('INVALID_CHARACTER',400);
            }
            if (strlen($trimName)>50)
            {
                throw new \Exception('LENGTH_TOO_LARGE',400);
            }
            $this->workspaceService->workspaceUpdate($workspace,$trimName);

            foreach ( $workspace->getUsers() as $sharedUser)
            {
                $update = new Update(
                    'https://todolist.com/workspaces/'.$sharedUser->getId(),
                    json_encode([
                        'class'=>'workspace',
                        'type'=>'update',
                        'name'=>$workspace->getName(),
                        'id'=>$workspace->getId()
                    ])
                );

                $hub->publish($update);
            }
        }

        return $this->json([]);
    }

    #[Route('/workSpaces/{workId}/{userId}', name: 'workspace_remove', methods: ['DELETE'])]
    public function workspaceRemove(Request $request,int $workId,int $userId,HubInterface $hub): Response
    {
        $user = $this->usersService->findUserById($userId);
        if (!$user)
        {
            throw new \Exception('USER_NOT_FOUND',404);
        }
        if ($user->getToken() !== $request->query->get('auth'))
        {
            throw new \Exception('INVALID_TOKEN',401);
        }
        $workspace = $this->workspaceService->findWorkspaceById($workId);
        $beforeUsers = clone $workspace->getUsers();

        $this->workspaceService->workspaceRemove($workId,$user);

        foreach ( $beforeUsers as $sharedUser) {
            $update = new Update(
                'https://todolist.com/workspaces/' . $sharedUser->getId(),
                json_encode([
                    'class'=>'workspace',
                    'type' => 'delete',
                    'id' => $workId
                ])
            );

            $hub->publish($update);
        }

        return $this->json([],200);
    }

    private function handleReturnArray(Collection $workspaces): array
    {
        $resultArray = array();
        /**@var WorkSpace $workspace**/
        foreach ( $workspaces as $key => $workspace )
        {
            $resultArray[$key]['id'] = $workspace->getId();
            $resultArray[$key]['name'] = $workspace->getName();
            $resultArray[$key]['owner'] = $workspace->getOwner()->getName();
            $sharedLists = [];
            foreach ($workspace->getSharedLists() as $innerKey => $list)
            {
                $sharedLists[$innerKey]['id'] = $list->getId();
                $sharedLists[$innerKey]['name'] = $list->getName();
                $sharedLists[$innerKey]['done'] = $list->getDone();
            }
            $resultArray[$key]['sharedLists'] = $sharedLists;
            $sharedUsernames = [];
            foreach ($workspace->getUsers() as $innerKey => $user)
            {
                $sharedUsernames[$innerKey] = $user->getName();
            }
            $resultArray[$key]['sharedUsers'] = $sharedUsernames;
        }
        return $resultArray;
    }

    private function handleWorkspaceContent(int $userId,string|null $type,array|null $ids,WorkSpace $workspace,Request $request,array|null $names,HubInterface $hub)
    {
        $listNames = [];
        $listDones = [];
        $newIds = [];
        if ($type == 'addLists')
        {
            foreach ( $ids as $key => $id)
            {
                $list = $this->listsService->listGet($id);
                if (!$list)
                {
                    throw new \Exception('LIST_NOT_FOUND',404);
                }
                if (!$list->getWorkspace())
                {
                    $workspace->addSharedList($list);
                    $newIds[$key] = $list->getId();
                    $listNames[$key] = $list->getName();
                    $listDones[$key] = $list->getDone();
                }
            }
            $this->workspaceService->workspaceFlush();

            $this->handleWhichUser($workspace,$workspace->getOwner(),$userId,$workspace->getUsers(),$newIds,$listNames,$listDones,$hub,$type);
        }elseif ($type == 'removeLists')
        {
            foreach ( $ids as $key => $id)
            {
                $list = $this->listsService->listGet($id);
                if (!$list)
                {
                    throw new \Exception('LIST_NOT_FOUND',404);
                }
                if ($list->getWorkspace())
                {
                    $workspace->removeSharedList($list);
                    $newIds[$key] = $list->getId();
                }
            }
            $this->workspaceService->workspaceFlush();

            $this->handleWhichUser($workspace,$workspace->getOwner(),$userId,$workspace->getUsers(),$newIds,null,null,$hub,$type);

        }elseif ($type == 'addUsers')
        {
            $addUsers = [];
            if ($workspace->getOwner()->getToken() !== $request->query->get('auth'))
            {
                throw new \Exception('INVALID_TOKEN',401);
            }
            foreach ( $names as $key => $name)
            {
                $user = $this->usersService->findUserByName($name);
                if (!$user)
                {
                    throw new \Exception('USER_NOT_FOUND',404);
                }
                $addUsers[$key] = $user;
                $workspace->addUser($user);
            }
            if ($workspace->getSharedLists()[0])
            {
                foreach ( $workspace->getSharedLists() as $key => $sharedList)
                {
                    $newIds[$key] = $sharedList->getId();
                    $listNames[$key] = $sharedList->getName();
                    $listDones[$key] = $sharedList->getDone();
                }
            }
            $this->workspaceService->workspaceFlush();

            $this->handleWhichUser($workspace,$workspace->getOwner(),$userId,$addUsers,$newIds,$listNames,$listDones,$hub,$type);
        }elseif ($type == 'removeUsers')
        {
            $removeUsers = [];
            if ($workspace->getOwner()->getToken() !== $request->query->get('auth'))
            {
                throw new \Exception('INVALID_TOKEN',401);
            }
            foreach ( $names as $key => $name)
            {
                $user = $this->usersService->findUserByName($name);
                if (!$user)
                {
                    throw new \Exception('USER_NOT_FOUND',404);
                }
                $removeUsers[$key] = $user;
                $workspace->removeUser($user);
            }
            $this->workspaceService->workspaceFlush();

            $this->handleWhichUser($workspace,$workspace->getOwner(),$userId,$removeUsers,null,null,null,$hub,$type);
        }
    }

    private function handleWhichUser(WorkSpace $workspace,User $owner,int $currentUserId,array|Collection|null $sharedUsers,array|null $newIds,array|null $listNames,array|null $listDones,HubInterface $hub,string $type)
    {
        if ($owner->getId() == $currentUserId)
        {
            foreach ( $sharedUsers as $sharedUser)
            {
                $this->postUpdate($workspace,$sharedUser,$newIds,$listNames,$listDones,$hub,$type);
            }
        }else{
            $this->postUpdate($workspace,$owner,$newIds,$listNames,$listDones,$hub,$type);
            foreach ( $sharedUsers as $sharedUser)
            {
                if ($sharedUser->getId() != $currentUserId){
                    $this->postUpdate($workspace,$sharedUser,$newIds,$listNames,$listDones,$hub,$type);
                }
            }
        }
    }

    private function postUpdate(WorkSpace $workspace,User $sharedUser,array|null $newIds,array|null $listNames,array|null $listDones,HubInterface $hub,string $type)
    {
        $names = [];
        if ($type == 'addUsers'){
            foreach ( $workspace->getUsers() as $key => $user)
            {
                $names[$key] = $user->getName();
            }
        }
        $update = new Update(
            'https://todolist.com/workspaces/'.$sharedUser->getId(),
            json_encode([
                'class'=>'workspace',
                'type'=>'update',
                'id'=>$workspace->getId(),
                'originName'=>$workspace->getName(),
                'owner'=>$workspace->getOwner()->getName(),
                'innerType'=>$type,
                'ids'=>$newIds,
                'listNames'=>$listNames,
                'listDones'=>$listDones,
                'names'=>$names
            ])
        );

        $hub->publish($update);
    }
}
