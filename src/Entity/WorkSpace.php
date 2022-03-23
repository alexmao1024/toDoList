<?php

namespace App\Entity;

use App\Repository\WorkSpaceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: WorkSpaceRepository::class)]
class WorkSpace
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 255)]
    private $name;

    #[ORM\ManyToMany(targetEntity: User::class, mappedBy: 'workSpaces')]
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    private $users;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'selfWorkSpaces')]
    #[ORM\JoinColumn(nullable: false,onDelete: 'CASCADE')]
    private $owner;

    #[ORM\OneToMany(mappedBy: 'workspace', targetEntity: TaskList::class)]
    private $sharedLists;

    public function __construct()
    {
        $this->users = new ArrayCollection();
        $this->sharedLists = new ArrayCollection();
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
     * @return Collection|User[]
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user): self
    {
        if (!$this->users->contains($user)) {
            $this->users[] = $user;
            $user->addWorkSpace($this);
        }

        return $this;
    }

    public function removeUser(User $user): self
    {
        if ($this->users->removeElement($user)) {
            $user->removeWorkSpace($this);
        }

        return $this;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(?User $owner): self
    {
        $this->owner = $owner;

        return $this;
    }

    /**
     * @return Collection|TaskList[]
     */
    public function getSharedLists(): Collection
    {
        return $this->sharedLists;
    }

    public function addSharedList(TaskList $sharedList): self
    {
        if (!$this->sharedLists->contains($sharedList)) {
            $this->sharedLists[] = $sharedList;
            $sharedList->setWorkspace($this);
        }

        return $this;
    }

    public function removeSharedList(TaskList $sharedList): self
    {
        if ($this->sharedLists->removeElement($sharedList)) {
            // set the owning side to null (unless already changed)
            if ($sharedList->getWorkspace() === $this) {
                $sharedList->setWorkspace(null);
            }
        }

        return $this;
    }
}
