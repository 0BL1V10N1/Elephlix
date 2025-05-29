<?php

namespace App\Entity;

use App\Repository\VideoRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: VideoRepository::class)]
class Video
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\Length(min: 1, max: 100)]
    #[Groups(['video:list', 'video:detail'])]
    private ?string $title = null;

    #[ORM\Column(length: 255, unique: true)]
    #[Groups(['video:list', 'video:detail'])]
    private ?string $slug;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Assert\Length(min: 1, max: 50_000)]
    #[Groups(['video:detail'])]
    private ?string $description = null;

    #[ORM\Column]
    #[Groups(['video:list', 'video:detail'])]
    private int $views;

    #[ORM\Column]
    #[Groups(['video:list', 'video:detail'])]
    private ?\DateTimeImmutable $uploadedAt;

    /**
     * @var Collection<int, Tag>
     */
    #[ORM\ManyToMany(targetEntity: Tag::class, mappedBy: 'videos')]
    #[Groups(['video:detail'])]
    private Collection $tags;

    #[ORM\ManyToOne(inversedBy: 'videos')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['video:list', 'video:detail'])]
    private ?User $author = null;

    /**
     * @var Collection<int, Comment>
     */
    #[ORM\OneToMany(targetEntity: Comment::class, mappedBy: 'video', orphanRemoval: true)]
    #[Groups(['video:detail'])]
    private Collection $comments;

    /**
     * @var Collection<int, VideoReaction>
     */
    #[ORM\OneToMany(targetEntity: VideoReaction::class, mappedBy: 'video', orphanRemoval: true)]
    #[Groups(['video:detail'])]
    private Collection $reactions;

    public function __construct()
    {
        $this->uploadedAt = new \DateTimeImmutable();
        $this->tags = new ArrayCollection();
        $this->comments = new ArrayCollection();
        $this->reactions = new ArrayCollection();
        $this->slug = Uuid::v4()->toBase58();
        $this->views = 0;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getViews(): int
    {
        return $this->views;
    }

    public function incrementViews(): static
    {
        ++$this->views;

        return $this;
    }

    public function getUploadedAt(): ?\DateTimeImmutable
    {
        return $this->uploadedAt;
    }

    /**
     * @return Collection<int, Tag>
     */
    public function getTags(): Collection
    {
        return $this->tags;
    }

    public function addTag(Tag $tag): static
    {
        if (!$this->tags->contains($tag)) {
            $this->tags->add($tag);
            $tag->addVideo($this);
        }

        return $this;
    }

    public function removeTag(Tag $tag): static
    {
        if ($this->tags->removeElement($tag)) {
            $tag->removeVideo($this);
        }

        return $this;
    }

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function setAuthor(?User $author): static
    {
        $this->author = $author;

        return $this;
    }

    /**
     * @return Collection<int, Comment>
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Comment $comment): static
    {
        if (!$this->comments->contains($comment)) {
            $this->comments->add($comment);
            $comment->setVideo($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): static
    {
        if ($this->comments->removeElement($comment)) {
            // set the owning side to null (unless already changed)
            if ($comment->getVideo() === $this) {
                $comment->setVideo(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, VideoReaction>
     */
    public function getReactions(): Collection
    {
        return $this->reactions;
    }

    public function addReaction(VideoReaction $reaction): static
    {
        if (!$this->reactions->contains($reaction)) {
            $this->reactions->add($reaction);
            $reaction->setVideo($this);
        }

        return $this;
    }

    public function removeReaction(VideoReaction $reaction): static
    {
        if ($this->reactions->removeElement($reaction)) {
            // set the owning side to null (unless already changed)
            if ($reaction->getVideo() === $this) {
                $reaction->setVideo(null);
            }
        }

        return $this;
    }
}
