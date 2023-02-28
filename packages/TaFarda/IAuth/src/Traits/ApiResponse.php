<?php

namespace TaFarda\IAuth\Traits;

use Illuminate\Http\JsonResponse;

trait ApiResponse
{
    /**
     * success exception handling callbacks for the application.
     *
     * @param $data
     * @param string|null $message
     * @param int $code
     * @return JsonResponse
     */
    protected function success($data, string $message = null, int $code = 200): JsonResponse
    {
        return response()->json([
            'message' => $message,
            'data' => $data
        ], $code);
    }

    /**
     * create success exception handling callbacks for the application.
     *
     * @param $data
     * @param string|null $message
     * @param int $code
     * @return JsonResponse
     */
    protected function createSuccess($data, string $message = null, int $code = 201): JsonResponse
    {
        return response()->json([
            'message' => $message,
            'data' => $data
        ], $code);
    }

    /**
     * something wrong with URL or parameters.
     *
     * @param string|null $message
     * @param int $code
     * @param $data
     * @return JsonResponse
     */
    protected function badRequestError(string $message = null, int $code = 400, $data = null): JsonResponse
    {
        return response()->json([
            'message' => $message,
            'data' => $data
        ], $code);
    }

    /**
     * not logged in.
     *
     * @param string|null $message
     * @param int $code
     * @param $data
     * @return JsonResponse
     */
    protected function authorizedError(string $message = null, int $code = 401, $data = null): JsonResponse
    {
        return response()->json([
            'message' => $message,
            'data' => $data
        ], $code);
    }

    /**
     * Logged in but access to requested area is forbidden.
     *
     * @param $data
     * @param string|null $message
     * @param int $code
     * @return JsonResponse
     */
    protected function forbiddenError($data, string $message = null, int $code = 403): JsonResponse
    {
        return response()->json([
            'message' => $message,
            'data' => $data
        ], $code);
    }

    /**
     * page or other resource doesn't exist.
     *
     * @param string|null $message
     * @param int $code
     * @param $data
     * @return JsonResponse
     */
    protected function error(string $message = null, int $code = 404, $data = null): JsonResponse
    {
        return response()->json([
            'message' => $message,
            'data' => $data
        ], $code);
    }

    /**
     * validation failed.
     *
     * @param string|null $message
     * @param int $code
     * @param $data
     * @return JsonResponse
     */
    protected function processableError(string $message = null, int $code = 422, $data = null): JsonResponse
    {
        return response()->json([
            'message' => $message,
            'data' => $data
        ], $code);
    }
    /**
     * General server error.
     *
     * @param string|null $message
     * @param int $code
     * @param $data
     * @return JsonResponse
     */
    protected function serverError(string $message = null, int $code = 500, $data = null): JsonResponse
    {
        return response()->json([
            'message' => $message,
            'data' => $data
        ], $code);
    }
}
