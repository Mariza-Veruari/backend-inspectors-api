<?php

namespace App\Dto\Response;

use App\Entity\Job;

readonly class JobResponse
{
    public function __construct(
        public int $id,
        public string $title,
        public string $status
    ) {
    }

    public static function fromEntity(Job $job): self
    {
        return new self(
            id: $job->getId(),
            title: $job->getTitle(),
            status: $job->getStatus()->value
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'status' => $this->status,
        ];
    }
}
