<?php

declare(strict_types=1);

namespace App\Entity;

use App\Enum\JobStatus;
use App\Repository\JobRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: JobRepository::class)]
#[ORM\Table(name: 'job')]
class Job
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(length: 20, enumType: JobStatus::class)]
    #[Assert\NotNull]
    private ?JobStatus $status = null;

    #[ORM\OneToOne(mappedBy: 'job', targetEntity: JobAssignment::class, cascade: ['persist', 'remove'])]
    private ?JobAssignment $assignment = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $createdAt = null;

    public function __construct()
    {
        $this->status = JobStatus::OPEN;
        $this->createdAt = new \DateTimeImmutable();
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;
        return $this;
    }

    public function getStatus(): ?JobStatus
    {
        return $this->status;
    }

    public function setStatus(JobStatus $status): static
    {
        $this->status = $status;
        return $this;
    }

    public function getAssignment(): ?JobAssignment
    {
        return $this->assignment;
    }

    public function setAssignment(?JobAssignment $assignment): static
    {
        $this->assignment = $assignment;
        if ($assignment !== null && $assignment->getJob() !== $this) {
            $assignment->setJob($this);
        }
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }
}
