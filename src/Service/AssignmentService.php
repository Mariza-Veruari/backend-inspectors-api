<?php

namespace App\Service;

use App\Entity\Assignment;
use App\Entity\Job;
use App\Entity\Inspector;
use App\Entity\JobStatus;
use Doctrine\ORM\EntityManagerInterface;

class AssignmentService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly TimezoneService $timezoneService
    ) {
    }

    public function getTimezoneService(): TimezoneService
    {
        return $this->timezoneService;
    }

    /**
     * Create a new assignment for a job and inspector.
     *
     * @throws \InvalidArgumentException if job is not open or inspector timezone is invalid
     */
    public function createAssignment(Job $job, Inspector $inspector, string $scheduleAtIso): Assignment
    {
        if (!$job->isOpen()) {
            throw new \InvalidArgumentException('Job is already assigned');
        }

        if (!$this->timezoneService->isAllowed($inspector->getTimezone())) {
            throw new \InvalidArgumentException('Inspector timezone not allowed');
        }

        $scheduledAtUtc = $this->timezoneService->toUtc($scheduleAtIso, $inspector->getTimezone());

        $assignment = new Assignment();
        $assignment->setJob($job);
        $assignment->setInspector($inspector);
        $assignment->setScheduledAt($scheduledAtUtc);

        $job->setStatus(JobStatus::ASSIGNED);

        $this->em->persist($assignment);
        $this->em->flush();

        return $assignment;
    }
}
