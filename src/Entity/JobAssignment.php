<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\JobAssignmentRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/** Links one job to one auditor. Datetimes stored in UTC; scheduledAtUtc required, completedAtUtc set on complete. */
#[ORM\Entity(repositoryClass: JobAssignmentRepository::class)]
#[ORM\Table(name: 'job_assignment')]
#[ORM\UniqueConstraint(name: 'unique_job_assignment', columns: ['job_id'])]
class JobAssignment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(targetEntity: Job::class, inversedBy: 'assignment')]
    #[ORM\JoinColumn(name: 'job_id', referencedColumnName: 'id', nullable: false, unique: true)]
    #[Assert\NotNull]
    private ?Job $job = null;

    #[ORM\ManyToOne(targetEntity: Auditor::class, inversedBy: 'assignments')]
    #[ORM\JoinColumn(name: 'auditor_id', referencedColumnName: 'id', nullable: false)]
    #[Assert\NotNull]
    private ?Auditor $auditor = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, name: 'scheduled_at_utc')]
    #[Assert\NotNull]
    private ?\DateTimeImmutable $scheduledAtUtc = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true, name: 'completed_at_utc')]
    private ?\DateTimeImmutable $completedAtUtc = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $assessment = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $createdAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getJob(): ?Job
    {
        return $this->job;
    }

    public function setJob(?Job $job): static
    {
        $this->job = $job;
        return $this;
    }

    public function getAuditor(): ?Auditor
    {
        return $this->auditor;
    }

    public function setAuditor(?Auditor $auditor): static
    {
        $this->auditor = $auditor;
        return $this;
    }

    public function getScheduledAtUtc(): ?\DateTimeImmutable
    {
        return $this->scheduledAtUtc;
    }

    public function setScheduledAtUtc(\DateTimeImmutable $scheduledAtUtc): static
    {
        $this->scheduledAtUtc = $scheduledAtUtc;
        return $this;
    }

    public function getCompletedAtUtc(): ?\DateTimeImmutable
    {
        return $this->completedAtUtc;
    }

    public function setCompletedAtUtc(?\DateTimeImmutable $completedAtUtc): static
    {
        $this->completedAtUtc = $completedAtUtc;
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
