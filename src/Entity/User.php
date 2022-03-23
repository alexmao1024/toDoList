<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
date_default_timezone_set('Asia/Shanghai');

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
class User
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 50)]
    private $name;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: TaskList::class, orphanRemoval: true)]
    private $taskLists;

    #[ORM\Column(type: 'string', length: 255)]
    private $password;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $token;

    #[ORM\ManyToMany(targetEntity: WorkSpace::class, inversedBy: 'users')]
    private $workSpaces;

    #[ORM\OneToMany(mappedBy: 'owner', targetEntity: WorkSpace::class)]
    private $selfWorkSpaces;

    public function __construct()
    {
        $this->taskLists = new ArrayCollection();
        $this->workSpaces = new ArrayCollection();
        $this->selfWorkSpaces = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Collection|TaskList[]
     */
    public function getTaskLists(): Collection
    {
        return $this->taskLists;
    }

    public function addTaskList(TaskList $taskList): self
    {
        if (!$this->taskLists->contains($taskList)) {
            $this->taskLists[] = $taskList;
            $taskList->setUser($this);
        }

        return $this;
    }

    public function removeTaskList(TaskList $taskList): self
    {
        if ($this->taskLists->removeElement($taskList)) {
            // set the owning side to null (unless already changed)
            if ($taskList->getUser() === $this) {
                $taskList->setUser(null);
            }
        }

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    #[ORM\PrePersist]
    #[ORM\PreUpdate]
    public function setToken($userid): self
    {
        $this->token = $this->createToken($userid);

        return $this;
    }

    public function createToken($userid): string
    {
        $time = time();
        $end_time = time() + 3600;
        $info = $userid . $time . $end_time;
        $signature = hash_hmac('md5', $info, 'secret');
        return $info.$signature;
    }

    /**
     * @return Collection|WorkSpace[]
     */
    public function getWorkSpaces(): Collection
    {
        return $this->workSpaces;
    }

    public function addWorkSpace(WorkSpace $workSpace): self
    {
        if (!$this->workSpaces->contains($workSpace)) {
            $this->workSpaces[] = $workSpace;
        }

        return $this;
    }

    public function removeWorkSpace(WorkSpace $workSpace): self
    {
        $this->workSpaces->removeElement($workSpace);

        return $this;
    }

    /**
     * @return Collection|WorkSpace[]
     */
    public function getSelfWorkSpaces(): Collection
    {
        return $this->selfWorkSpaces;
    }

    public function addSelfWorkSpace(WorkSpace $selfWorkSpace): self
    {
        if (!$this->selfWorkSpaces->contains($selfWorkSpace)) {
            $this->selfWorkSpaces[] = $selfWorkSpace;
            $selfWorkSpace->setOwner($this);
        }

        return $this;
    }

    public function removeSelfWorkSpace(WorkSpace $selfWorkSpace): self
    {
        if ($this->selfWorkSpaces->removeElement($selfWorkSpace)) {
            // set the owning side to null (unless already changed)
            if ($selfWorkSpace->getOwner() === $this) {
                $selfWorkSpace->setOwner(null);
            }
        }

        return $this;
    }

}
