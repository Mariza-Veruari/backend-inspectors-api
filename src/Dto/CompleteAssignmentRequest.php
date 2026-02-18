<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class CompleteAssignmentRequest
{
    #[Assert\NotBlank(allowNull: false)]
    #[Assert\Type('string')]
    public ?string $assessment = null;
}
