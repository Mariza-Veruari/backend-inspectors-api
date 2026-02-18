<?php

namespace App\Controller;

use App\Dto\CompleteAssignmentRequest;
use App\Dto\Response\AssignmentResponse;
use App\Entity\AssignmentStatus;
use App\Entity\JobStatus;
use App\Repository\AssignmentRepository;
use App\Service\TimezoneService;
use Doctrine\ORM\EntityManagerInterface;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/assignments', name: 'api_assignments_')]
#[OA\Tag(name: 'Assignments')]
class AssignmentController extends BaseApiController
{
    public function __construct(
        private readonly AssignmentRepository $assignmentRepository,
        private readonly TimezoneService $timezoneService,
        private readonly EntityManagerInterface $em,
        private readonly SerializerInterface $serializer,
        ValidatorInterface $validator
    ) {
        parent::__construct($validator);
    }

    /**
     * Mark assignment complete and add assessment.
     */
    #[Route('/{id}/complete', name: 'complete', methods: ['POST'])]
    #[OA\Post(path: '/api/assignments/{id}/complete', summary: 'Complete assignment with assessment', tags: ['Assignments'])]
    #[OA\Parameter(name: 'id', in: 'path', required: true, description: 'Assignment ID', schema: new OA\Schema(type: 'integer', example: 1))]
    #[OA\RequestBody(
        required: true,
        description: 'Assessment text (required)',
        content: new OA\JsonContent(
            required: ['assessment'],
            properties: [
                new OA\Property(property: 'assessment', type: 'string', example: 'All checks passed. Minor repairs recommended.'),
            ],
            type: 'object',
            example: ['assessment' => 'All checks passed. Minor repairs recommended.']
        )
    )]
    #[OA\Response(response: 200, description: 'Assignment completed',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'id', type: 'integer', example: 1),
                new OA\Property(property: 'jobId', type: 'integer', example: 1),
                new OA\Property(property: 'inspectorId', type: 'integer', example: 1),
                new OA\Property(property: 'scheduledAt', type: 'string', format: 'date-time', example: '2025-03-15T09:00:00+01:00'),
                new OA\Property(property: 'completedAt', type: 'string', format: 'date-time', example: '2025-03-15T11:30:00+01:00'),
                new OA\Property(property: 'status', type: 'string', example: 'completed', enum: ['completed']),
                new OA\Property(property: 'assessment', type: 'string', example: 'All checks passed.'),
            ],
            type: 'object',
            example: [
                'id' => 1,
                'jobId' => 1,
                'inspectorId' => 1,
                'scheduledAt' => '2025-03-15T09:00:00+01:00',
                'completedAt' => '2025-03-15T11:30:00+01:00',
                'status' => 'completed',
                'assessment' => 'All checks passed.',
            ]
        )
    )]
    #[OA\Response(response: 400, description: 'Validation error',
        content: new OA\JsonContent(
            schema: new OA\Schema(properties: [
                new OA\Property(property: 'errors', type: 'object', description: 'Field names to error messages'),
            ], type: 'object'),
            example: ['errors' => ['assessment' => 'This value should not be blank.']]
        )
    )]
    #[OA\Response(response: 404, description: 'Assignment not found',
        content: new OA\JsonContent(
            properties: [new OA\Property(property: 'error', type: 'string')],
            type: 'object',
            example: ['error' => 'Assignment not found']
        )
    )]
    #[OA\Response(response: 409, description: 'Assignment already completed',
        content: new OA\JsonContent(
            properties: [new OA\Property(property: 'error', type: 'string')],
            type: 'object',
            example: ['error' => 'Assignment already completed']
        )
    )]
    public function complete(int $id, Request $request): JsonResponse
    {
        try {
            /** @var CompleteAssignmentRequest $dto */
            $dto = $this->serializer->deserialize(
                $request->getContent(),
                CompleteAssignmentRequest::class,
                'json'
            );
        } catch (\Throwable $e) {
            return $this->badRequestResponse('Invalid JSON format');
        }

        $errors = $this->validateDto($dto);
        if ($errors !== null) {
            return $this->validationErrorResponse($errors);
        }

        $assignment = $this->assignmentRepository->find($id);
        if (!$assignment) {
            return $this->notFoundResponse('Assignment not found');
        }
        if (!$assignment->isScheduled()) {
            return $this->conflictResponse('Assignment already completed');
        }

        $assignment->setStatus(AssignmentStatus::COMPLETED);
        $assignment->setAssessment($dto->assessment);
        $assignment->setCompletedAt(new \DateTimeImmutable('now', new \DateTimeZone('UTC')));
        $assignment->getJob()->setStatus(JobStatus::COMPLETED);
        $this->em->flush();

        $response = AssignmentResponse::fromEntity($assignment, $this->timezoneService);
        return $this->jsonResponse($response->toArray());
    }
}
