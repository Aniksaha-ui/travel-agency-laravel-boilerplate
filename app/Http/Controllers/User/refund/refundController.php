<?php

namespace App\Http\Controllers\User\refund;

use App\Http\Controllers\Controller;
use App\Repository\Services\Users\RefundService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class refundController extends Controller
{
    private $refundService;

    public function __construct(RefundService $refundService)
    {
        $this->refundService = $refundService;
    }

    public function refund()
    {
        try {

            $userId = auth()->user()->id;
            Log::info("RefundController user id: " . $userId);
            $refunds = $this->refundService->getRefund($userId);

            if ($refunds['status']) {
                return response()->json([
                    'status' => 'success',
                    'data' => $refunds['data'],
                ], 200);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => $refunds['message'],
                ], 500);
            }
        } catch (Exception $ex) {
            Log::alert($ex->getMessage());
            return response()->json(['error' => 'Refund processing failed.'], 500);
        }
    }
}
