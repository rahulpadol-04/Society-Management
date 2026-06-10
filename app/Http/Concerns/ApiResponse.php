<?php

declare(strict_types=1);

namespace App\Http\Concerns;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;

/**
 * Uniform JSON envelope used by every API controller so mobile / SPA clients
 * receive a predictable shape: { success, message, data, meta, errors }.
 */
trait ApiResponse
{
    protected function ok(mixed $data = null, string $message = 'OK', int $status = 200): JsonResponse
    {
        $payload = ['success' => true, 'message' => $message];

        if ($data instanceof LengthAwarePaginator || $data instanceof ResourceCollection) {
            return $this->paginated($data, $message, $status);
        }

        $payload['data'] = $data instanceof JsonResource ? $data->resolve() : $data;

        return response()->json($payload, $status);
    }

    protected function created(mixed $data = null, string $message = 'Created successfully'): JsonResponse
    {
        return $this->ok($data, $message, 201);
    }

    protected function fail(string $message = 'Something went wrong', int $status = 400, mixed $errors = null): JsonResponse
    {
        return response()->json(array_filter([
            'success' => false,
            'message' => $message,
            'errors'  => $errors,
        ], fn ($v) => $v !== null), $status);
    }

    protected function paginated(LengthAwarePaginator $paginator, string $message = 'OK', int $status = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data'    => $paginator->items(),
            'meta'    => [
                'current_page' => $paginator->currentPage(),
                'last_page'    => $paginator->lastPage(),
                'per_page'     => $paginator->perPage(),
                'total'        => $paginator->total(),
            ],
        ], $status);
    }
}
