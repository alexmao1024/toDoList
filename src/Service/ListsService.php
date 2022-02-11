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

    public function listCreate(string $name,int $userId): TaskList|null
    {
        $user = $this->entityManager->getRepository(User::class)->find($userId);
        $list = new TaskList();
        $list->setUser($user);
        $list->setName($name);

        $this->entityManager->persist($list);

        $this->entityManager->flush();

        return $list;
    }

    public function listRemove(TaskList $list)
    {
        $this->entityManager->remove($list);

        $this->entityManager->flush();
    }

    public function listUpdate(TaskList $list,string $name)
    {
        if ($name)
        {
            $list->setname($name);
        }

        $this->entityManager->flush();
    }
}