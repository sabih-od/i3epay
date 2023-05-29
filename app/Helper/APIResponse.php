<?php

namespace App\Helper;

use Illuminate\Http\JsonResponse;

class APIResponse
{
    /**
     * API Success Response Helper Method
     *
     * @param string $msg
     * @param array $data
     * @return JsonResponse
     */
    public static function success(string $msg = '', array $data = []): JsonResponse
    {
        return response()->json([
            'message' => $msg,
            'data' => $data,
        ]);
    }

    /**
     * API Error Response Helper Method
     *
     * @param int $status
     * @param string $msg
     * @param array $data
     * @return JsonResponse
     */
    public static function error(string $msg, array $data = [], int $status = 422): JsonResponse
    {
        return response()->json([
            'message' => $msg,
            'data' => $data,
        ], $status);
    }
}