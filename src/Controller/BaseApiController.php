<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

abstract class BaseApiController extends AbstractController
{
    public function __construct(
        protected readonly ValidatorInterface $validator
    ) {
    }

    /**
     * Validate a DTO and return errors if any.
     *
     * @return array<string, string>|null Returns null if valid, array of errors otherwise
     */
    protected function validateDto(object $dto): ?array
    {
        $errors = $this->validator->validate($dto);
        if (count($errors) === 0) {
            return null;
        }

        $messages = [];
        foreach ($errors as $error) {
            $messages[$error->getPropertyPath()] = $error->getMessage();
        }

        return $messages;
    }

    /**
     * Create a validation error response.
     */
    protected function validationErrorResponse(array $errors): JsonResponse
    {
        return new JsonResponse(['errors' => $errors], Response::HTTP_BAD_REQUEST);
    }

    /**
     * Create a not found error response.
     */
    protected function notFoundResponse(string $message): JsonResponse
    {
        return new JsonResponse(['error' => $message], Response::HTTP_NOT_FOUND);
    }

    /**
     * Create a conflict error response.
     */
    protected function conflictResponse(string $message): JsonResponse
    {
        return new JsonResponse(['error' => $message], Response::HTTP_CONFLICT);
    }

    /**
     * Create a bad request error response.
     */
    protected function badRequestResponse(string $message): JsonResponse
    {
        return new JsonResponse(['error' => $message], Response::HTTP_BAD_REQUEST);
    }

    /**
     * Create a success JSON response.
     */
    protected function jsonResponse(array $data, int $statusCode = Response::HTTP_OK): JsonResponse
    {
        return new JsonResponse($data, $statusCode);
    }
}
