<?php

namespace App\Entity;

use App\Repository\AssignmentRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AssignmentRepository::class)]
class Assignment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Job::class, inversedBy: 'assignments')]
    #[ORM\JoinColumn(nullable: false)]
    private Job $job;

    #[ORM\ManyToOne(targetEntity: Inspector::class, inversedBy: 'assignments')]
    #[ORM\JoinColumn(nullable: false)]
    private Inspector $inspector;

    /**
     * Scheduled datetime in UTC.
     */
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $scheduledAt;

    #[ORM\Column(length: 50)]
    private AssignmentStatus $status = AssignmentStatus::SCHEDULED;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $assessment = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $completedAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getJob(): Job
    {
        return $this->job;
    }

    public function setJob(Job $job): static
    {
        $this->job = $job;
        return $this;
    }

    public function getInspector(): Inspector
    {
        return $this->inspector;
    }

    public function setInspector(Inspector $inspector): static
    {
        $this->inspector = $inspector;
        return $this;
    }

    public function getScheduledAt(): \DateTimeImmutable
    {
        return $this->scheduledAt;
    }

    public function setScheduledAt(\DateTimeImmutable $scheduledAt): static
    {
        $this->scheduledAt = $scheduledAt;
        return $this;
    }

    public function getStatus(): AssignmentStatus
    {
        return $this->status;
    }

    public function setStatus(AssignmentStatus $status): static
    {
        $this->status = $status;
        return $this;
    }

    public function getAssessment(): ?string
    {
        return $this->assessment;
    }

    public function setAssessment(?string $assessment): static
    {
        $this->assessment = $assessment;
        return $this;
    }

    public function getCompletedAt(): ?\DateTimeImmutable
    {
        return $this->completedAt;
    }

    public function setCompletedAt(?\DateTimeImmutable $completedAt): static
    {
        $this->completedAt = $completedAt;
        return $this;
    }

    public function isScheduled(): bool
    {
        return $this->status === AssignmentStatus::SCHEDULED;
    }
}
