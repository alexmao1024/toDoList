<?php

namespace App\Service;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class UsersService
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function findUserByName(string $username): User|null
    {
        return $this->entityManager->getRepository(User::class)->findOneBy(['name' => $username]);
    }

    public function findUserById(int $userId): User|null
    {
        return $this->entityManager->getRepository(User::class)->find($userId);
    }
}