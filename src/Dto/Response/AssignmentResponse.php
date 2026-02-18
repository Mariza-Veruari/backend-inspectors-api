<?php

namespace App\Dto\Response;

use App\Entity\Assignment;
use App\Service\TimezoneService;

readonly class AssignmentResponse
{
    public function __construct(
        public int $id,
        public int $jobId,
        public ?string $jobTitle,
        public int $inspectorId,
        public string $scheduledAt,
        public ?string $completedAt,
        public string $status,
        public ?string $assessment
    ) {
    }

    public static function fromEntity(Assignment $assignment, TimezoneService $timezoneService): self
    {
        $inspector = $assignment->getInspector();
        $job = $assignment->getJob();
        $inspectorTz = $inspector->getTimezone();

        return new self(
            id: $assignment->getId(),
            jobId: $job->getId(),
            jobTitle: $job->getTitle(),
            inspectorId: $inspector->getId(),
            scheduledAt: $timezoneService->formatForInspector($assignment->getScheduledAt(), $inspectorTz),
            completedAt: $assignment->getCompletedAt()
                ? $timezoneService->formatForInspector($assignment->getCompletedAt(), $inspectorTz)
                : null,
            status: $assignment->getStatus()->value,
            assessment: $assignment->getAssessment()
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'jobId' => $this->jobId,
            'jobTitle' => $this->jobTitle,
            'inspectorId' => $this->inspectorId,
            'scheduledAt' => $this->scheduledAt,
            'completedAt' => $this->completedAt,
            'status' => $this->status,
            'assessment' => $this->assessment,
        ];
    }
}
