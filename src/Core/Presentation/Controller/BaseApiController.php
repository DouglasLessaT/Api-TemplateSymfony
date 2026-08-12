<?php

namespace App\Core\Presentation\Controller;

use App\Core\Application\Query\Pagination;
use App\Core\Application\Query\PaginatedResult;
use App\Core\Domain\Exception\DomainException;
use App\Core\Domain\Exception\ValidationException;
use App\Core\Presentation\Response\ApiResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Base API Controller
 * 
 * Controller base para APIs REST.
 * Fornece métodos helper para respostas padronizadas.
 */
abstract class BaseApiController extends AbstractController
{
    /**
     * Retorna uma resposta de sucesso
     */
    protected function success(mixed $data = null, string $message = 'Success', int $status = Response::HTTP_OK): JsonResponse
    {
        return $this->json(
            ApiResponse::success($data, $message)->toArray(),
            $status
        );
    }

    /**
     * Retorna uma resposta de erro
     */
    protected function error(string $message, int $status = Response::HTTP_BAD_REQUEST, array $errors = []): JsonResponse
    {
        return $this->json(
            ApiResponse::error($message, $errors)->toArray(),
            $status
        );
    }

    /**
     * Retorna uma resposta de validação com erros
     */
    protected function validationError(array $errors, string $message = 'Validation failed'): JsonResponse
    {
        return $this->error($message, Response::HTTP_UNPROCESSABLE_ENTITY, $errors);
    }

    /**
     * Retorna uma resposta 404
     */
    protected function notFound(string $message = 'Resource not found'): JsonResponse
    {
        return $this->error($message, Response::HTTP_NOT_FOUND);
    }

    /**
     * Retorna uma resposta 401
     */
    protected function unauthorized(string $message = 'Unauthorized'): JsonResponse
    {
        return $this->error($message, Response::HTTP_UNAUTHORIZED);
    }

    /**
     * Retorna uma resposta 403
     */
    protected function forbidden(string $message = 'Forbidden'): JsonResponse
    {
        return $this->error($message, Response::HTTP_FORBIDDEN);
    }

    /**
     * Retorna uma resposta 201 (Created)
     */
    protected function created(mixed $data = null, string $message = 'Resource created'): JsonResponse
    {
        return $this->success($data, $message, Response::HTTP_CREATED);
    }

    /**
     * Retorna uma resposta 204 (No Content)
     */
    protected function noContent(): Response
    {
        return new Response('', Response::HTTP_NO_CONTENT);
    }

    /**
     * Retorna uma resposta paginada
     */
    protected function paginated(PaginatedResult $result, string $message = 'Success'): JsonResponse
    {
        return $this->json(
            ApiResponse::paginated($result, $message)->toArray()
        );
    }

    /**
     * Obtém a paginação da request
     */
    protected function getPagination(Request $request): Pagination
    {
        return Pagination::fromQuery($request->query->all());
    }

    /**
     * Trata exceções de domínio e retorna resposta apropriada
     */
    protected function handleDomainException(DomainException $exception): JsonResponse
    {
        if ($exception instanceof ValidationException) {
            return $this->validationError($exception->getErrors(), $exception->getMessage());
        }

        $status = $exception->getCode() ?: Response::HTTP_BAD_REQUEST;
        
        return $this->error(
            $exception->getMessage(),
            $status,
            $exception->getContext()
        );
    }

    /**
     * Obtém o corpo da request como array
     */
    protected function getRequestData(Request $request): array
    {
        $content = $request->getContent();
        
        if (empty($content)) {
            return [];
        }

        $data = json_decode($content, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \InvalidArgumentException('Invalid JSON: ' . json_last_error_msg());
        }

        return $data ?? [];
    }

    /**
     * Valida parâmetros obrigatórios
     */
    protected function validateRequired(array $data, array $required): void
    {
        $missing = [];

        foreach ($required as $field) {
            if (!isset($data[$field]) || $data[$field] === '' || $data[$field] === null) {
                $missing[] = $field;
            }
        }

        if (!empty($missing)) {
            throw ValidationException::fromErrors([
                'required' => 'Missing required fields: ' . implode(', ', $missing)
            ]);
        }
    }
}
