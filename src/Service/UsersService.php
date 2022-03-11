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

    public function findUser(string $username,string $password): User|null
    {
        return $this->entityManager->getRepository(User::class)->findOneBy(['name' => $username,'password' => $password]);
    }

    public function findUserById(int $userId): User|null
    {
        return $this->entityManager->getRepository(User::class)->find($userId);
    }

    public function createUser(string $username,string $password): User
    {
        $user = new User();
        $trimName = trim(preg_replace('/\s+/', '', $username));
        if ($trimName !== $username || preg_match ("/[\x{4e00}-\x{9fa5}]/u", $trimName)) {
            throw new \Exception('INVALID_CHARACTER',400);
        }
        if (strlen($trimName)>50)
        {
            throw new \Exception('LENGTH_TOO_LARGE',400);
        }
        $user->setName($trimName);
        $user->setPassword($password);
        $this->entityManager->persist($user);
        $this->entityManager->flush();
        $user->setToken($user->getId());
        $this->entityManager->flush();
        return $user;
    }

    public function findUserByName(string $username) : User|null
    {
        return $this->entityManager->getRepository(User::class)->findOneBy(['name' => $username]);
    }

    public function createToken(User $user)
    {
        $user->setToken($user->getId());
        $this->entityManager->flush();
    }
}