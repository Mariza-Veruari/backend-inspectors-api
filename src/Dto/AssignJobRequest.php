<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class AssignJobRequest
{
    #[Assert\NotBlank]
    #[Assert\Type('integer')]
    #[Assert\Positive]
    public ?int $inspectorId = null;

    /**
     * Scheduled datetime in inspector's local timezone (ISO 8601).
     */
    #[Assert\NotBlank]
    #[Assert\DateTime(format: \DateTimeInterface::ATOM, message: 'scheduleAt must be a valid ISO 8601 datetime.')]
    public ?string $scheduleAt = null;
}
