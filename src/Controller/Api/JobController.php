<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Entity\Job;
use App\Entity\JobAssignment;
use App\Enum\JobStatus;
use App\Repository\AuditorRepository;
use App\Repository\JobRepository;
use Doctrine\ORM\EntityManagerInterface;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/** REST API for jobs: list, show, assign (OPEN only), complete (ASSIGNED + matching auditor). */
#[Route('/api/jobs', name: 'api_jobs_')]
#[OA\Tag(name: 'Jobs')]
class JobController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        private JobRepository $jobRepository,
        private AuditorRepository $auditorRepository,
        private ValidatorInterface $validator
    ) {
    }

    #[Route('', name: 'list', methods: ['GET'])]
    #[OA\Get(
        path: '/api/jobs',
        summary: 'List jobs',
        description: 'Get a list of jobs, optionally filtered by status',
        parameters: [
            new OA\Parameter(
                name: 'status',
                in: 'query',
                description: 'Filter by job status',
                required: false,
                schema: new OA\Schema(type: 'string', enum: ['OPEN', 'ASSIGNED', 'COMPLETED'])
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'List of jobs',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'items',
                            type: 'array',
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: 'id', type: 'integer', example: 1),
                                    new OA\Property(property: 'title', type: 'string', example: 'Site Inspection A'),
                                    new OA\Property(property: 'description', type: 'string', nullable: true, example: 'Initial inspection'),
                                    new OA\Property(property: 'status', type: 'string', example: 'OPEN'),
                                    new OA\Property(property: 'createdAt', type: 'string', format: 'date-time', example: '2025-01-15T10:00:00+00:00'),
                                ],
                                type: 'object'
                            )
                        )
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(response: 400, description: 'Invalid status parameter')
        ]
    )]
    /** GET /api/jobs — List jobs, optional ?status=OPEN|ASSIGNED|COMPLETED. */
    public function list(Request $request): JsonResponse
    {
        $statusParam = $request->query->get('status');
        $status = null;

        if ($statusParam !== null) {
            try {
                $status = JobStatus::from($statusParam);
            } catch (\ValueError $e) {
                return new JsonResponse(
                    ['message' => 'Invalid status. Allowed values: OPEN, ASSIGNED, COMPLETED'],
                    Response::HTTP_BAD_REQUEST
                );
            }
        }

        $jobs = $this->jobRepository->findByStatus($status);

        $items = array_map(function (Job $job) {
            return [
                'id' => $job->getId(),
                'title' => $job->getTitle(),
                'description' => $job->getDescription(),
                'status' => $job->getStatus()->value,
                'createdAt' => $job->getCreatedAt()?->format('c'),
            ];
        }, $jobs);

        return new JsonResponse(['items' => $items]);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'], requirements: ['id' => '\d+'])]
    #[OA\Get(
        path: '/api/jobs/{id}',
        summary: 'Get a job by ID',
        description: 'Get job details including assignment information if exists',
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Job details',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'id', type: 'integer', example: 1),
                        new OA\Property(property: 'title', type: 'string', example: 'Site Inspection A'),
                        new OA\Property(property: 'description', type: 'string', nullable: true),
                        new OA\Property(property: 'status', type: 'string', example: 'ASSIGNED'),
                        new OA\Property(property: 'createdAt', type: 'string', format: 'date-time'),
                        new OA\Property(
                            property: 'assignment',
                            properties: [
                                new OA\Property(property: 'id', type: 'integer'),
                                new OA\Property(property: 'auditorId', type: 'integer'),
                                new OA\Property(property: 'auditorName', type: 'string'),
                                new OA\Property(property: 'scheduledAt', type: 'string', format: 'date-time'),
                                new OA\Property(property: 'completedAt', type: 'string', format: 'date-time', nullable: true),
                                new OA\Property(property: 'assessment', type: 'string', nullable: true),
                            ],
                            type: 'object',
                            nullable: true
                        ),
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(response: 404, description: 'Job not found')
        ]
    )]
    /** GET /api/jobs/{id} — Job details; includes assignment (and auditor timezone-formatted times) if present. */
    public function show(int $id): JsonResponse
    {
        $job = $this->jobRepository->find($id);

        if (!$job) {
            return new JsonResponse(
                ['message' => 'Job not found'],
                Response::HTTP_NOT_FOUND
            );
        }

        $response = [
            'id' => $job->getId(),
            'title' => $job->getTitle(),
            'description' => $job->getDescription(),
            'status' => $job->getStatus()->value,
            'createdAt' => $job->getCreatedAt()?->format('c'),
        ];

        $assignment = $job->getAssignment();
        if ($assignment) {
            $auditor = $assignment->getAuditor();
            $auditorTimezone = $auditor->getOriginTimezone()->value;

            $scheduledAtInTimezone = $assignment->getScheduledAtUtc()
                ->setTimezone(new \DateTimeZone($auditorTimezone));

            $completedAtInTimezone = null;
            if ($assignment->getCompletedAtUtc()) {
                $completedAtInTimezone = $assignment->getCompletedAtUtc()
                    ->setTimezone(new \DateTimeZone($auditorTimezone))
                    ->format('c');
            }

            $response['assignment'] = [
                'id' => $assignment->getId(),
                'auditorId' => $auditor->getId(),
                'auditorName' => $auditor->getName(),
                'scheduledAt' => $scheduledAtInTimezone->format('c'),
                'completedAt' => $completedAtInTimezone,
                'assessment' => $assignment->getAssessment(),
            ];
        }

        return new JsonResponse($response);
    }

    #[Route('/{id}/assign', name: 'assign', methods: ['POST'], requirements: ['id' => '\d+'])]
    #[OA\Post(
        path: '/api/jobs/{id}/assign',
        summary: 'Assign a job to an auditor',
        description: 'Assign a job to an auditor. Job must be OPEN and not already assigned.',
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'auditorId', type: 'integer', example: 1, description: 'Auditor ID'),
                    new OA\Property(property: 'scheduledAt', type: 'string', format: 'date-time', example: '2025-03-15T09:00:00+01:00', description: 'ISO 8601 datetime with timezone'),
                ],
                type: 'object',
                required: ['auditorId', 'scheduledAt']
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Job assigned successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'id', type: 'integer', example: 1),
                        new OA\Property(property: 'jobId', type: 'integer', example: 1),
                        new OA\Property(property: 'auditorId', type: 'integer', example: 1),
                        new OA\Property(property: 'scheduledAt', type: 'string', format: 'date-time'),
                        new OA\Property(property: 'status', type: 'string', example: 'ASSIGNED'),
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(response: 400, description: 'Validation error - missing or invalid fields'),
            new OA\Response(response: 404, description: 'Job or auditor not found'),
            new OA\Response(response: 409, description: 'Job cannot be assigned (already assigned or not OPEN)')
        ]
    )]
    /** POST /api/jobs/{id}/assign — Assign job to auditor. Allowed only when status=OPEN and no assignment yet. */
    public function assign(int $id, Request $request): JsonResponse
    {
        $job = $this->jobRepository->find($id);

        if (!$job) {
            return new JsonResponse(
                ['message' => 'Job not found'],
                Response::HTTP_NOT_FOUND
            );
        }

        if ($job->getStatus() !== JobStatus::OPEN) {
            return new JsonResponse(
                ['message' => 'Job can only be assigned if status is OPEN'],
                Response::HTTP_CONFLICT
            );
        }

        if ($job->getAssignment() !== null) {
            return new JsonResponse(
                ['message' => 'Job is already assigned'],
                Response::HTTP_CONFLICT
            );
        }

        $data = json_decode($request->getContent(), true);

        if (!is_array($data) || !isset($data['auditorId']) || !isset($data['scheduledAt'])) {
            return new JsonResponse(
                ['message' => 'Missing required fields: auditorId, scheduledAt'],
                Response::HTTP_BAD_REQUEST
            );
        }

        $auditor = $this->auditorRepository->find($data['auditorId']);

        if (!$auditor) {
            return new JsonResponse(
                ['message' => 'Auditor not found'],
                Response::HTTP_NOT_FOUND
            );
        }

        try {
            $scheduledAt = new \DateTimeImmutable($data['scheduledAt']);
        } catch (\Exception $e) {
            return new JsonResponse(
                ['message' => 'Invalid scheduledAt format. Expected ISO 8601 datetime with timezone'],
                Response::HTTP_BAD_REQUEST
            );
        }

        // Store in UTC; response will show times in auditor's timezone
        $scheduledAtUtc = $scheduledAt->setTimezone(new \DateTimeZone('UTC'));

        $assignment = new JobAssignment();
        $assignment->setJob($job);
        $assignment->setAuditor($auditor);
        $assignment->setScheduledAtUtc($scheduledAtUtc);

        $errors = $this->validator->validate($assignment);
        if (count($errors) > 0) {
            $messages = [];
            foreach ($errors as $error) {
                $messages[] = $error->getMessage();
            }
            return new JsonResponse(
                ['message' => 'Validation failed', 'errors' => $messages],
                Response::HTTP_BAD_REQUEST
            );
        }

        $job->setStatus(JobStatus::ASSIGNED);
        $job->setAssignment($assignment);

        $this->em->persist($assignment);
        $this->em->flush();

        $auditorTimezone = $auditor->getOriginTimezone()->value;
        $scheduledAtInTimezone = $scheduledAtUtc->setTimezone(new \DateTimeZone($auditorTimezone));

        return new JsonResponse([
            'id' => $assignment->getId(),
            'jobId' => $job->getId(),
            'auditorId' => $auditor->getId(),
            'scheduledAt' => $scheduledAtInTimezone->format('c'),
            'status' => $job->getStatus()->value,
        ]);
    }

    #[Route('/{id}/complete', name: 'complete', methods: ['POST'], requirements: ['id' => '\d+'])]
    #[OA\Post(
        path: '/api/jobs/{id}/complete',
        summary: 'Complete a job assignment',
        description: 'Complete a job assignment. Job must be ASSIGNED and auditorId must match the assigned auditor.',
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'auditorId', type: 'integer', example: 1, description: 'Auditor ID (must match assigned auditor)'),
                    new OA\Property(property: 'assessment', type: 'string', nullable: true, example: 'All checks passed', description: 'Assessment notes'),
                    new OA\Property(property: 'completedAt', type: 'string', format: 'date-time', nullable: true, example: '2025-03-15T11:30:00+01:00', description: 'ISO 8601 datetime (defaults to now if omitted)'),
                ],
                type: 'object',
                required: ['auditorId']
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Job completed successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'id', type: 'integer'),
                        new OA\Property(property: 'jobId', type: 'integer'),
                        new OA\Property(property: 'auditorId', type: 'integer'),
                        new OA\Property(property: 'scheduledAt', type: 'string', format: 'date-time'),
                        new OA\Property(property: 'completedAt', type: 'string', format: 'date-time'),
                        new OA\Property(property: 'status', type: 'string', example: 'COMPLETED'),
                        new OA\Property(property: 'assessment', type: 'string', nullable: true),
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(response: 400, description: 'Validation error'),
            new OA\Response(response: 404, description: 'Job or assignment not found'),
            new OA\Response(response: 409, description: 'Job cannot be completed (wrong status or wrong auditor)')
        ]
    )]
    /** POST /api/jobs/{id}/complete — Mark job done. Allowed only when ASSIGNED and auditorId matches assignment. */
    public function complete(int $id, Request $request): JsonResponse
    {
        $job = $this->jobRepository->find($id);

        if (!$job) {
            return new JsonResponse(
                ['message' => 'Job not found'],
                Response::HTTP_NOT_FOUND
            );
        }

        if ($job->getStatus() !== JobStatus::ASSIGNED) {
            return new JsonResponse(
                ['message' => 'Job can only be completed if status is ASSIGNED'],
                Response::HTTP_CONFLICT
            );
        }

        $assignment = $job->getAssignment();

        if (!$assignment) {
            return new JsonResponse(
                ['message' => 'Job assignment not found'],
                Response::HTTP_NOT_FOUND
            );
        }

        $data = json_decode($request->getContent(), true);

        if (!is_array($data) || !isset($data['auditorId'])) {
            return new JsonResponse(
                ['message' => 'Missing required field: auditorId'],
                Response::HTTP_BAD_REQUEST
            );
        }

        // Only the auditor who was assigned may complete
        if ($assignment->getAuditor()->getId() !== (int) $data['auditorId']) {
            return new JsonResponse(
                ['message' => 'Only the assigned auditor can complete this job'],
                Response::HTTP_CONFLICT
            );
        }

        if (isset($data['completedAt'])) {
            try {
                $completedAt = new \DateTimeImmutable($data['completedAt']);
            } catch (\Exception $e) {
                return new JsonResponse(
                    ['message' => 'Invalid completedAt format. Expected ISO 8601 datetime with timezone'],
                    Response::HTTP_BAD_REQUEST
                );
            }
        } else {
            $completedAt = new \DateTimeImmutable('now');
        }

        // Store completedAt in UTC
        $completedAtUtc = $completedAt->setTimezone(new \DateTimeZone('UTC'));

        $assignment->setCompletedAtUtc($completedAtUtc);
        $assignment->setAssessment($data['assessment'] ?? null);

        $errors = $this->validator->validate($assignment);
        if (count($errors) > 0) {
            $messages = [];
            foreach ($errors as $error) {
                $messages[] = $error->getMessage();
            }
            return new JsonResponse(
                ['message' => 'Validation failed', 'errors' => $messages],
                Response::HTTP_BAD_REQUEST
            );
        }

        $job->setStatus(JobStatus::COMPLETED);

        $this->em->flush();

        $auditor = $assignment->getAuditor();
        $auditorTimezone = $auditor->getOriginTimezone()->value;
        $scheduledAtInTimezone = $assignment->getScheduledAtUtc()->setTimezone(new \DateTimeZone($auditorTimezone));
        $completedAtInTimezone = $completedAtUtc->setTimezone(new \DateTimeZone($auditorTimezone));

        return new JsonResponse([
            'id' => $assignment->getId(),
            'jobId' => $job->getId(),
            'auditorId' => $auditor->getId(),
            'scheduledAt' => $scheduledAtInTimezone->format('c'),
            'completedAt' => $completedAtInTimezone->format('c'),
            'status' => $job->getStatus()->value,
            'assessment' => $assignment->getAssessment(),
        ]);
    }
}
