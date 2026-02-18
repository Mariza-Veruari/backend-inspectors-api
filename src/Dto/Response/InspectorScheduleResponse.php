<?php

namespace App\Dto\Response;

use App\Entity\Inspector;
use App\Service\TimezoneService;

readonly class InspectorScheduleResponse
{
    /**
     * @param AssignmentResponse[] $assignments
     */
    public function __construct(
        public int $inspectorId,
        public string $timezone,
        public array $assignments
    ) {
    }

    public static function fromInspector(Inspector $inspector, array $assignments, TimezoneService $timezoneService): self
    {
        $assignmentResponses = array_map(
            fn($assignment) => AssignmentResponse::fromEntity($assignment, $timezoneService),
            $assignments
        );

        return new self(
            inspectorId: $inspector->getId() ?? 0,
            timezone: $inspector->getTimezone(),
            assignments: $assignmentResponses
        );
    }

    public function toArray(): array
    {
        return [
            'inspectorId' => $this->inspectorId,
            'timezone' => $this->timezone,
            'assignments' => array_map(fn($a) => $a->toArray(), $this->assignments),
        ];
    }
}
