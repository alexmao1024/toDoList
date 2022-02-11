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
    public function listsShow(int $userId): Response
    {
        $response = new Response();

        $user = $this->usersService->findUserById($userId);

        if (!$user)
        {
            throw new \Exception('Wrong User',401);
        }

        $lists = $this->listsService->listsGet($userId);

        if (!$lists)
        {
            return $this->json([]);
        }

        $resultArray = array();
        foreach ( $lists as $key => $list )
        {
            $resultArray[$key]['listId'] = $list->getId();
            $resultArray[$key]['name'] = $list->getName();
        }

        return $response->setContent(json_encode($resultArray));
    }

    #[Route('/lists/{userId}', name: 'lists_create', methods: ['POST'])]
    public function listsCreate(Request $request,int $userId):Response
    {
        $requestArray = $request->toArray();
        $name = $requestArray[0]['listName'];

        $user = $this->usersService->findUserById($userId);

        if (!$user)
        {
            throw new \Exception('Wrong User',401);
        }

        if (!$name)
        {
            throw new \Exception('Passed an empty argument.',400);
        }

        $list = $this->listsService->listCreate($name, $userId);

        return $this->json([
            'listId'=>$list->getId()
        ]);
    }

    #[Route('/lists/{listId}/{userId}', name: 'lists_remove', methods: ['DELETE'])]
    public function listsRemove(int $listId,int $userId): Response
    {
        $user = $this->usersService->findUserById($userId);

        if (!$user)
        {
            throw new \Exception('Wrong User',401);
        }

        $list = $this->listsService->listGet($listId);
        if (!$list)
        {
            throw new \Exception('No list found for: '.$listId,404);
        }

        if ($list->getUser()->getId() !== $userId)
        {
            throw new \Exception('No permission to delete for: '.$listId, 403);
        }

        $this->listsService->listRemove($list);
        return $this->json([],200);

    }

    #[Route('/lists/{listId}/{userId}', name: 'lists_update', methods: ['PATCH'])]
    public function listsUpdate(Request $request,int $listId,int $userId): Response
    {
        $requestArray = $request->toArray();
        $name = $requestArray[0]['name'];

        $user = $this->usersService->findUserById($userId);

        if (!$user)
        {
            throw new \Exception('Wrong User',401);
        }

        $list = $this->listsService->listGet($listId);
        if (!$list)
        {
            throw new \Exception('No list found for: '.$listId,404);
        }
        if ($list->getUser()->getId() !== $userId)
        {
            throw new \Exception('No permission to modify for: '.$listId, 403);
        }

        $this->listsService->listUpdate($list,$name);

        return $this->json([],200);
    }

}
