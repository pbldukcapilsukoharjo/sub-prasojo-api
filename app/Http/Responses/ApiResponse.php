<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;

class ApiResponse
{
    public static function success(string $message, mixed $data = null, int $code = 200): JsonResponse
    {
        if ($data === null) {
            $data = new \stdClass();
        }

        return response()->json([
            'status' => true,
            'code' => $code,
            'message' => $message,
            'data' => $data,
        ], $code);
    }

    public static function error(string $message, int $code = 400, mixed $data = null): JsonResponse
    {
        return response()->json([
            'status' => false,
            'code' => $code,
            'message' => $message,
            'data' => $data,
        ], $code);
    }

    public static function paginated(string $message, LengthAwarePaginator $paginator, array $extra = []): JsonResponse
    {
        $response = [
            'status' => true,
            'code' => 200,
            'message' => $message,
            'data' => $paginator->items(),
            'meta' => [
                'page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'total_page' => $paginator->lastPage(),
            ],
        ];

        if (!empty($extra)) {
            $response = array_merge($response, $extra);
        }

        return response()->json($response, 200);
    }
}
