<?php

namespace App\Controller;

use App\Dto\Response\InspectorScheduleResponse;
use App\Repository\AssignmentRepository;
use App\Repository\InspectorRepository;
use App\Service\TimezoneService;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/inspectors', name: 'api_inspectors_')]
#[OA\Tag(name: 'Inspectors')]
class InspectorController extends BaseApiController
{
    public function __construct(
        private readonly InspectorRepository $inspectorRepository,
        private readonly AssignmentRepository $assignmentRepository,
        private readonly TimezoneService $timezoneService,
        ValidatorInterface $validator
    ) {
        parent::__construct($validator);
    }

    /**
     * List assignments (schedule) for an inspector. Datetimes in inspector timezone (ISO 8601).
     */
    #[Route('/{id}/schedule', name: 'schedule', methods: ['GET'])]
    #[OA\Get(path: '/api/inspectors/{id}/schedule', summary: 'Get inspector schedule', tags: ['Inspectors'])]
    #[OA\Parameter(name: 'id', in: 'path', required: true, description: 'Inspector ID', schema: new OA\Schema(type: 'integer', example: 1))]
    #[OA\Response(response: 200, description: 'Inspector schedule with assignments',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'inspectorId', type: 'integer', example: 1),
                new OA\Property(property: 'timezone', type: 'string', example: 'Europe/Madrid'),
                new OA\Property(property: 'assignments', type: 'array', items: new OA\Items(properties: [
                    new OA\Property(property: 'id', type: 'integer', example: 1),
                    new OA\Property(property: 'jobId', type: 'integer', example: 1),
                    new OA\Property(property: 'jobTitle', type: 'string', example: 'Inspection at Site A'),
                    new OA\Property(property: 'scheduledAt', type: 'string', format: 'date-time', example: '2025-03-15T09:00:00+01:00'),
                    new OA\Property(property: 'status', type: 'string', example: 'scheduled', enum: ['scheduled', 'completed']),
                    new OA\Property(property: 'assessment', type: 'string', nullable: true),
                    new OA\Property(property: 'completedAt', type: 'string', format: 'date-time', nullable: true),
                ], type: 'object')),
            ],
            type: 'object',
            example: [
                'inspectorId' => 1,
                'timezone' => 'Europe/Madrid',
                'assignments' => [
                    [
                        'id' => 1,
                        'jobId' => 1,
                        'jobTitle' => 'Inspection at Site A',
                        'scheduledAt' => '2025-03-15T09:00:00+01:00',
                        'status' => 'scheduled',
                        'assessment' => null,
                        'completedAt' => null,
                    ],
                ]
            ]
        )
    )]
    #[OA\Response(response: 404, description: 'Inspector not found',
        content: new OA\JsonContent(
            properties: [new OA\Property(property: 'error', type: 'string')],
            type: 'object',
            example: ['error' => 'Inspector not found']
        )
    )]
    public function schedule(int $id): JsonResponse
    {
        $inspector = $this->inspectorRepository->find($id);
        if (!$inspector) {
            return $this->notFoundResponse('Inspector not found');
        }

        $assignments = $this->assignmentRepository->findByInspectorOrderByScheduledAt($inspector);
        $response = InspectorScheduleResponse::fromInspector($inspector, $assignments, $this->timezoneService);

        return $this->jsonResponse($response->toArray());
    }
}
