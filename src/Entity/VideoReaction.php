<?php

namespace App\Entity;

use App\Enum\ReactionType;
use App\Repository\VideoReactionRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\UniqueConstraint;

#[ORM\Entity(repositoryClass: VideoReactionRepository::class)]
#[UniqueConstraint(columns: ['video_id', 'reactor_id'])]
class VideoReaction
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(nullable: true, enumType: ReactionType::class)]
    private ?ReactionType $type = null;

    #[ORM\ManyToOne(inversedBy: 'reactions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Video $video = null;

    #[ORM\ManyToOne(inversedBy: 'reactions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $reactor = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): ?ReactionType
    {
        return $this->type;
    }

    public function setType(?ReactionType $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getVideo(): ?Video
    {
        return $this->video;
    }

    public function setVideo(?Video $video): static
    {
        $this->video = $video;

        return $this;
    }

    public function getReactor(): ?User
    {
        return $this->reactor;
    }

    public function setReactor(?User $reactor): static
    {
        $this->reactor = $reactor;

        return $this;
    }
}
