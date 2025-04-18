<?php

namespace App\Entity;

use App\Repository\PostHistoryRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PostHistoryRepository::class)]
#[ORM\HasLifecycleCallbacks]
class PostHistory
{
    use \App\Entity\Trait\Timestampable;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $content = null;

    #[ORM\ManyToOne(inversedBy: 'postHistories')]
    #[ORM\JoinColumn(onDelete: 'SET NULL', nullable: true)]
    private ?User $owner = null;

    #[ORM\Column(nullable: true)]
    private ?bool $isFavorite = false;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): static
    {
        $this->content = $content;

        return $this;
    }



    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(?User $owner): static
    {
        $this->owner = $owner;

        return $this;
    }

    public function isFavorite(): ?bool
    {
        return $this->isFavorite;
    }

    public function setIsFavorite(?bool $isFavorite): static
    {
        $this->isFavorite = $isFavorite;

        return $this;
    }

    public function toggleFavorite(): static
    {
        $this->isFavorite = !$this->isFavorite;
        return $this;
    }
}
