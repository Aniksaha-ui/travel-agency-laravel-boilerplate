<?php

namespace App\Http\Controllers;

use App\Constants\ApiResponseStatus;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected function apiResponse(string $executionStatus, $data = [], string $message = 'success', int $statusCode = 200)
    {
        return response()->json([
            'isExecture' => $executionStatus,
            'data' => $data ?? [],
            'message' => $message,
        ], $statusCode);
    }

    protected function normalizeExecutionStatus($status): string
    {
        if (is_string($status)) {
            return strtoupper($status) === ApiResponseStatus::SUCCESS ? 'success' : 'failed';
        }

        return $status ? 'success' : 'failed';
    }

    protected function serviceResponse(array $response, int $statusCode = 200)
    {
        return $this->apiResponse(
            $this->normalizeExecutionStatus($response['status'] ?? $response['isExecute'] ?? false),
            $response['data'] ?? [],
            $response['message'] ?? 'success',
            $statusCode
        );
    }

    protected function serviceStatusCode(array $response, int $successCode = 200, int $failedCode = 422): int
    {
        return $this->normalizeExecutionStatus($response['status'] ?? $response['isExecute'] ?? false) === 'success'
            ? $successCode
            : $failedCode;
    }

    protected function successResponse($data = [], string $message = 'success', int $statusCode = 200)
    {
        return $this->apiResponse('success', $data, $message, $statusCode);
    }

    protected function failedResponse(string $message = 'Internal Server Error', int $statusCode = 500, $data = [])
    {
        return $this->apiResponse('failed', $data, $message, $statusCode);
    }
}
