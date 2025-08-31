<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;
use Response;

trait ApiResponder
{
    private function resolveMessage(int $code): string
    {
        return match ($code) {
            // Success codes (2xx)
            200 => __('api.success'),
            201 => __('api.created'),
            202 => __('api.accepted'),
            204 => __('api.no_content'),

            // Client error codes (4xx)
            400 => __('api.bad_request'),
            401 => __('api.unauthorized'),
            402 => __('api.payment_required'),
            403 => __('api.forbidden'),
            404 => __('api.not_found'),
            405 => __('api.method_not_allowed'),
            406 => __('api.not_acceptable'),
            409 => __('api.conflict'),
            410 => __('api.gone'),
            422 => __('api.unprocessable_entity'),
            423 => __('api.locked'),
            429 => __('api.too_many_requests'),

            // Server error codes (5xx)
            500 => __('api.server_error'),
            501 => __('api.not_implemented'),
            502 => __('api.bad_gateway'),
            503 => __('api.service_unavailable'),
            504 => __('api.gateway_timeout'),

            // Default fallback
            default => __('api.unknown_error'),
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
