<?php

declare(strict_types=1);

namespace App\Entity;

use App\Enum\AuditorTimezone;
use App\Repository\AuditorRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/** Inspector who can be assigned to jobs. Email is unique; originTimezone used for datetime display. */
#[ORM\Entity(repositoryClass: AuditorRepository::class)]
#[ORM\Table(name: 'auditor')]
#[UniqueEntity(fields: ['email'], message: 'An auditor with this email already exists.')]
class Auditor
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255, unique: true)]
    #[Assert\NotBlank]
    #[Assert\Email]
    #[Assert\Length(max: 255)]
    private ?string $email = null;

    #[ORM\Column(length: 64, enumType: AuditorTimezone::class)]
    #[Assert\NotNull]
    #[Assert\Choice(choices: AuditorTimezone::ALL, message: 'Invalid timezone. Allowed: Europe/Madrid, America/Mexico_City, Europe/London')]
    private ?AuditorTimezone $originTimezone = null;

    /** @var Collection<int, JobAssignment> */
    #[ORM\OneToMany(mappedBy: 'auditor', targetEntity: JobAssignment::class)]
    private Collection $assignments;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $createdAt = null;

    public function __construct()
    {
        $this->assignments = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;
        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;
        return $this;
    }

    public function getOriginTimezone(): ?AuditorTimezone
    {
        return $this->originTimezone;
    }

    public function setOriginTimezone(AuditorTimezone $originTimezone): static
    {
        $this->originTimezone = $originTimezone;
        return $this;
    }

    /** @return Collection<int, JobAssignment> */
    public function getAssignments(): Collection
    {
        return $this->assignments;
    }

    public function addAssignment(JobAssignment $assignment): static
    {
        if (!$this->assignments->contains($assignment)) {
            $this->assignments->add($assignment);
            $assignment->setAuditor($this);
        }
        return $this;
    }

    public function removeAssignment(JobAssignment $assignment): static
    {
        if ($this->assignments->removeElement($assignment)) {
            if ($assignment->getAuditor() === $this) {
                $assignment->setAuditor(null);
            }
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
