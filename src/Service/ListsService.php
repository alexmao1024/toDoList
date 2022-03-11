<?php

namespace App\Service;

use App\Entity\TaskList;
use App\Entity\User;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;

class ListsService
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function listGet(int $listId): TaskList|null
    {
        return $this->entityManager->getRepository(TaskList::class)->find($listId);
    }

    public function listsGet(int $userId): Collection|array|null
    {
        /**@var User $user**/
        $user = $this->entityManager->getRepository(User::class)->find($userId);

        return $user->getTaskLists();
    }

    public function listCreate(array $lists,int $userId): array|null
    {
        $user = $this->entityManager->getRepository(User::class)->find($userId);
        $listIds = array();
        foreach ($lists as $key => $listName)
        {
            $list = new TaskList();
            $list->setUser($user);
            $trimName = trim($listName);
            if (!$trimName) {
                throw new \Exception('INVALID_CHARACTER',400);
            }
            if (strlen($trimName)>50)
            {
                throw new \Exception('LENGTH_TOO_LARGE',400);
            }
            $list->setName($trimName);
            $list->setDone(false);
            $this->entityManager->persist($list);
            $this->entityManager->flush();

            $listIds[$key] = $list->getId();
        }
        return $listIds;
    }

    public function listsRemove(array $listIds,string $token)
    {
        foreach ( $listIds as $listId){
            $list = $this->listGet($listId);
            if (!$list)
            {
                throw new \Exception('LISTS_NOT_FOUND',404);
            }

            if ($list->getUser()->getToken() !== $token)
            {
                throw new \Exception('INVALID_TOKEN',401);
            }
            $this->entityManager->remove($list);
        }
        $this->entityManager->flush();
    }

    public function listUpdate(TaskList $list,string $name)
    {
        $trimName = trim($name);
        if (!$trimName) {
            throw new \Exception('INVALID_CHARACTER',400);
        }
        if (strlen($trimName)>50)
        {
            throw new \Exception('LENGTH_TOO_LARGE',400);
        }
        $list->setname($trimName);

        $this->entityManager->flush();
    }

    public function listUpdateDone(TaskList $list,bool $boolean)
    {
        $list->setDone($boolean);

        $this->entityManager->flush();
    }

    public function listsUpdateAllDone(Collection|array $lists,bool $boolean)
    {
        foreach ( $lists as $list )
        {
            /**@var TaskList $list**/
            $list->setDone($boolean);
        }

        $this->entityManager->flush();
    }
}