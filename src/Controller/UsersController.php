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
        $username = $requestArray['username'];
        $password = $requestArray['password'];

        $user1 = $this->usersService->findUserByName($username);

        if (!$user1){
            throw new \Exception('USERNAME_NOT_FOUND',404);
        }else{
            $user2 = $this->usersService->findUser($username,$password);
            if (!$user2){
                throw new \Exception('INVALID_PASSWORD',401);
            }
        }
        $this->usersService->createToken($user2);

        return $this->json(
            [
                'id' => $user2->getId(),
                'username'=> $user2->getName(),
                'token' => $user2->getToken(),
                'expiresIn' => time() + 3600
            ]
        );
    }

    #[Route('/signUp',name: 'signUp',methods: ['POST'])]
    public function userSignUp(Request $request): Response
    {

        $requestArray = $request->toArray();
        $username = $requestArray['username'];
        $password = $requestArray['password'];

        $user = $this->usersService->findUserByName($username);
        if ($user){
            throw new \Exception('USERNAME_EXISTS',409);
        }

        $newUser = $this->usersService->createUser($username,$password);

        return $this->json(
            [
                'id' => $newUser->getId(),
                'username'=> $newUser->getName(),
                'token' => $newUser->getToken(),
                'expiresIn' => time() + 3600
            ]
        );
    }
}
