<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;
use Response;

trait ApiResponder
{
    private function resolveMessage(int $code): string
    {
        return match ($code) {
            200 => __('success'),
            500 => __('server_error'),
        };
    }

    public function response(mixed $data, string $message, int $status): JsonResponse
    {
        return Response::json([
            'status' => $status,
            'message' => $message,
            'data' => $data,
        ], $status);
    }

    public function success(mixed $data = null, ?string $message = null, int $status = 200): JsonResponse
    {
        return $this->response($data, $message ?? $this->resolveMessage($status), $status);
    }

    public function failure(mixed $data = null, ?string $message = null, int $status = 500): JsonResponse
    {
        return $this->response($data, $message ?? $this->resolveMessage($status), $status);
    }
}
