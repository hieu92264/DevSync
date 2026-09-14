<?php

namespace App\Shared\Traits;

use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

trait ApiResponse
{
    protected function apiResponse(
        mixed $metadata = null,
        ?string $message = null,
        int $statusCode = Response::HTTP_OK,
        ?Throwable $exception = null,
    ): JsonResponse {
        $payload = [
            'message' => $message ?? 'Success',
            'status_code' => $statusCode,
            'metadata' => $metadata,
            'path' => request()->getPathInfo(),
            'timestamp' => now()->toISOString(),
        ];

        if ($exception !== null && config('app.debug')) {
            $payload['debug'] = [
                'exception' => $exception::class,
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
            ];
        }

        return response()->json($payload, $statusCode);
    }

    protected function success(
        mixed $metadata = null,
        ?string $message = null,
        int $statusCode = Response::HTTP_OK,
    ): JsonResponse {
        return $this->apiResponse($metadata, $message, $statusCode);
    }

    protected function error(
        mixed $metadata = null,
        ?string $message = null,
        int $statusCode = Response::HTTP_BAD_REQUEST,
        ?Throwable $exception = null,
    ): JsonResponse {
        return $this->apiResponse(
            $metadata,
            $message ?? 'An error occurred.',
            $statusCode,
            $exception,
        );
    }
}
