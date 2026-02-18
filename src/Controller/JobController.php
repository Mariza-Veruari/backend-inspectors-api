<?php

namespace App\Controller;

use App\Dto\AssignJobRequest;
use App\Dto\Response\AssignmentResponse;
use App\Dto\Response\JobResponse;
use App\Entity\Job;
use App\Repository\InspectorRepository;
use App\Repository\JobRepository;
use App\Service\AssignmentService;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/jobs', name: 'api_jobs_')]
#[OA\Tag(name: 'Jobs')]
class JobController extends BaseApiController
{
    public function __construct(
        private readonly JobRepository $jobRepository,
        private readonly InspectorRepository $inspectorRepository,
        private readonly AssignmentService $assignmentService,
        private readonly SerializerInterface $serializer,
        ValidatorInterface $validator
    ) {
        parent::__construct($validator);
    }

    /**
     * List open jobs.
     */
    #[Route('', name: 'list', methods: ['GET'])]
    #[OA\Get(path: '/api/jobs', summary: 'List open jobs', tags: ['Jobs'])]
    #[OA\Response(response: 200, description: 'List of open jobs',
        content: new OA\JsonContent(
            schema: new OA\Schema(
                type: 'object',
                required: ['items'],
                properties: [
                    new OA\Property(property: 'items', type: 'array', items: new OA\Items(properties: [
                        new OA\Property(property: 'id', type: 'integer', example: 1),
                        new OA\Property(property: 'title', type: 'string', example: 'Inspection at Site A'),
                        new OA\Property(property: 'status', type: 'string', example: 'open', enum: ['open']),
                    ], type: 'object'))
                ]
            ),
            example: [
                'items' => [
                    ['id' => 1, 'title' => 'Inspection at Site A', 'status' => 'open'],
                    ['id' => 2, 'title' => 'Inspection at Site B', 'status' => 'open'],
                ]
            ]
        )
    )]
    public function list(): JsonResponse
    {
        $jobs = $this->jobRepository->findOpenJobs();
        $items = array_map(
            fn(Job $job) => JobResponse::fromEntity($job)->toArray(),
            $jobs
        );

        return $this->jsonResponse(['items' => $items]);
    }

    /**
     * Assign a job to an inspector and set scheduled date. scheduleAt must be ISO 8601 in the inspector's timezone.
     */
    #[Route('/{id}/assign', name: 'assign', methods: ['POST'])]
    #[OA\Post(path: '/api/jobs/{id}/assign', summary: 'Assign job to inspector', tags: ['Jobs'])]
    #[OA\Parameter(name: 'id', in: 'path', required: true, description: 'Job ID', schema: new OA\Schema(type: 'integer', example: 1))]
    #[OA\RequestBody(
        required: true,
        description: 'Inspector ID and scheduled datetime (inspector timezone, ISO 8601)',
        content: new OA\JsonContent(
            required: ['inspectorId', 'scheduleAt'],
            properties: [
                new OA\Property(property: 'inspectorId', type: 'integer', description: 'Inspector ID', example: 1),
                new OA\Property(property: 'scheduleAt', type: 'string', format: 'date-time', description: 'Scheduled datetime in inspector timezone (e.g. 2025-03-15T09:00:00+01:00)', example: '2025-03-15T09:00:00+01:00'),
            ],
            type: 'object',
            example: ['inspectorId' => 1, 'scheduleAt' => '2025-03-15T09:00:00+01:00']
        )
    )]
    #[OA\Response(response: 200, description: 'Assignment created',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'id', type: 'integer', example: 1),
                new OA\Property(property: 'jobId', type: 'integer', example: 1),
                new OA\Property(property: 'inspectorId', type: 'integer', example: 1),
                new OA\Property(property: 'scheduledAt', type: 'string', format: 'date-time', example: '2025-03-15T09:00:00+01:00'),
                new OA\Property(property: 'status', type: 'string', example: 'scheduled', enum: ['scheduled']),
            ],
            type: 'object',
            example: ['id' => 1, 'jobId' => 1, 'inspectorId' => 1, 'scheduledAt' => '2025-03-15T09:00:00+01:00', 'status' => 'scheduled']
        )
    )]
    #[OA\Response(response: 400, description: 'Validation error or invalid timezone',
        content: new OA\JsonContent(
            schema: new OA\Schema(properties: [
                new OA\Property(property: 'errors', type: 'object', description: 'Field names to error messages'),
            ], type: 'object'),
            example: ['errors' => ['scheduleAt' => 'scheduleAt must be a valid ISO 8601 datetime.']]
        )
    )]
    #[OA\Response(response: 404, description: 'Job or inspector not found',
        content: new OA\JsonContent(
            properties: [new OA\Property(property: 'error', type: 'string')],
            type: 'object',
            example: ['error' => 'Job not found']
        )
    )]
    #[OA\Response(response: 409, description: 'Job already assigned',
        content: new OA\JsonContent(
            properties: [new OA\Property(property: 'error', type: 'string')],
            type: 'object',
            example: ['error' => 'Job is already assigned']
        )
    )]
    public function assign(int $id, Request $request): JsonResponse
    {
        try {
            /** @var AssignJobRequest $dto */
            $dto = $this->serializer->deserialize(
                $request->getContent(),
                AssignJobRequest::class,
                'json'
            );
        } catch (\Throwable $e) {
            return $this->badRequestResponse('Invalid JSON format');
        }

        $errors = $this->validateDto($dto);
        if ($errors !== null) {
            return $this->validationErrorResponse($errors);
        }

        $job = $this->jobRepository->find($id);
        if (!$job) {
            return $this->notFoundResponse('Job not found');
        }

        $inspector = $this->inspectorRepository->find($dto->inspectorId);
        if (!$inspector) {
            return $this->notFoundResponse('Inspector not found');
        }

        try {
            $assignment = $this->assignmentService->createAssignment($job, $inspector, $dto->scheduleAt);
        } catch (\InvalidArgumentException $e) {
            if ($e->getMessage() === 'Job is already assigned') {
                return $this->conflictResponse('Job is already assigned');
            }
            return $this->badRequestResponse($e->getMessage());
        }

        $timezoneService = $this->assignmentService->getTimezoneService();
        $response = AssignmentResponse::fromEntity($assignment, $timezoneService);
        return $this->jsonResponse($response->toArray());
    }
}
