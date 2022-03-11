<?php

namespace App\Controller;

use App\Service\ListsService;
use App\Service\UsersService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ListsController extends AbstractController
{
    private ListsService $listsService;
    private UsersService $usersService;

    public function __construct(ListsService $listsService,UsersService $usersService)
    {
        $this->listsService = $listsService;
        $this->usersService = $usersService;
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
        foreach ( $lists as $key => $list )
        {
            $resultArray[$key]['id'] = $list->getId();
            $resultArray[$key]['name'] = $list->getName();
            $resultArray[$key]['done'] = $list->getDone();
        }

        return $response->setContent(json_encode($resultArray));
    }

    #[Route('/lists/{userId}', name: 'lists_create', methods: ['POST'])]
    public function listsCreate(Request $request,int $userId):Response
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

        $listIds = $this->listsService->listCreate($lists, $userId);

        return $this->json([
            'ids'=>$listIds
        ]);
    }

    #[Route('/lists/{userId}', name: 'lists_remove', methods: ['DELETE'])]
    public function listsRemove(Request $request,int $userId): Response
    {
        $requestArray = $request->toArray();
        $user = $this->usersService->findUserById($userId);
        if (!$user)
        {
            throw new \Exception('USER_NOT_FOUND',404);
        }

        $this->listsService->listsRemove($requestArray,$request->query->get('auth'));

        return $this->json([],200);

    }

    #[Route('/lists/{userId}', name: 'lists_update', methods: ['PATCH'])]
    public function listsUpdate(Request $request,int $userId): Response
    {
        $requestArray = $request->toArray();
        $filter = $requestArray['filter'];
        $modifiedAllDone = $requestArray['modifiedAllDone'];
        $boolean = $requestArray['boolean'];
        $id = $requestArray['id'];
        $name = $requestArray['name'];
        $done = $requestArray['done'];

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
            if ($list->getUser()->getToken() !== $request->query->get('auth'))
            {
                throw new \Exception('INVALID_TOKEN',401);
            }
            if ($filter){
                $this->listsService->listUpdate($list,$name);
            }else{
                $this->listsService->listUpdateDone($list,$done);
            }
        }else{
            if ($user->getToken() !== $request->query->get('auth'))
            {
                throw new \Exception('INVALID_TOKEN',401);
            }
            $lists = $this->listsService->listsGet($userId);
            if (!$lists[0])
            {
                throw new \Exception('ALL_LISTS_NOT_FOUND',404);
            }
            $this->listsService->listsUpdateAllDone($lists,$boolean);
        }
        return $this->json([],200);
    }

}
