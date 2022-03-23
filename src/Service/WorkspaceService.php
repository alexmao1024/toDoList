<?php

namespace App\Service;

use App\Entity\User;
use App\Entity\WorkSpace;
use Doctrine\ORM\EntityManagerInterface;

class WorkspaceService
{
    private EntityManagerInterface $entityManager;
    private UsersService $usersService;

    public function __construct(EntityManagerInterface $entityManager,
                                UsersService $usersService,)
    {
        $this->entityManager = $entityManager;
        $this->usersService = $usersService;
    }

    public function workspaceCreate(string $name,User $user,array $sharedUsers): WorkSpace
    {
        $workSpace = new WorkSpace();
        $trimName = trim($name);
        if (!$trimName) {
            throw new \Exception('INVALID_CHARACTER',400);
        }
        if (strlen($trimName)>50)
        {
            throw new \Exception('LENGTH_TOO_LARGE',400);
        }
        $workSpace->setName($trimName);
        $workSpace->setOwner($user);
        foreach ( $sharedUsers as $sharedUserName )
        {
            $sharedUser = $this->usersService->findUserByName($sharedUserName);
            if (!$sharedUser)
            {
                throw new \Exception('USER_NOT_FOUND',404);
            }
            $workSpace->addUser($sharedUser);
        }

        $this->entityManager->persist($workSpace);
        $this->entityManager->flush();

        return $workSpace;
    }

    public function workspaceListsGet($workId): \Doctrine\Common\Collections\Collection|array
    {
        $workspace = $this->findWorkspaceById($workId);
        return $workspace->getSharedLists();
    }

    public function findWorkspaceById($workId)
    {
        return $this->entityManager->getRepository(WorkSpace::class)->find($workId);
    }

    public function workspaceFlush()
    {
        $this->entityManager->flush();
    }

    public function workspaceRemove(int $workId)
    {
        $workspace = $this->findWorkspaceById($workId);

        $this->entityManager->remove($workspace);
        $this->entityManager->flush();
    }

    public function workspaceUpdate(WorkSpace $workSpace,string $name)
    {
        $workSpace->setName($name);
        $this->entityManager->flush();
    }
}