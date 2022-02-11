<?php

namespace App\Controller;

use App\Service\UsersService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UsersController extends AbstractController
{

    private UsersService $usersService;

    public function __construct(UsersService $usersService)
    {
        $this->usersService = $usersService;
    }

    #[Route('/login',name: 'login',methods: ['POST'])]
    public function userLogin(Request $request): Response
    {

        $requestArray = $request->toArray();
        $username = $requestArray[0]['username'];

        $user = $this->usersService->findUserByName($username);

        if (!$user){
            throw new \Exception('Login in failed',401);
        }

        return $this->json(
            [
                'userId'=>$user->getId(),
                'username'=>$user->getName()
            ]
        );
    }
}
