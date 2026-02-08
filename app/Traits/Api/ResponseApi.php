<?php

namespace App\Traits\Api;

use Illuminate\Http\JsonResponse;

trait ResponseApi
{
    public function successResponse(array $data = [], string $message = 'Success', int $code = 200):JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'message' => $message,
            'data' => $data,
        ], $code);
    }

    public function errorResponse(string $message = 'Error', int $code = 400,array $data = []):JsonResponse
    {
        return response()->json([
            'status' => 'error',
            'message' => $message,
            'data' => $data,
        ], $code);
    }
}
