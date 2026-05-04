<?php

namespace App\Http\Controllers\Admin\refund;

use App\Http\Controllers\Controller;
use App\Repository\Services\Refund\RefundService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class refundController extends Controller
{
    protected $refundService;
    public function __construct(RefundService $seatService)
    {
        $this->refundService = $seatService;
    }

    public function getRefunds(Request $request)
    {
        $page = $request->query('page');
        $search = $request->query('search');
        Log::info("jt");
        try {
            $response = $this->refundService->getAllRefunds($page, $search);
            return $this->serviceResponse($response);
        } catch (Exception $er) {
            Log::error("refundController getRefunds: " . $er->getMessage());
            return $this->failedResponse();
        }
    }

    public function disburseRefund(Request $request)
    {
        Log::info("refundController disburseRefund" . json_encode($request->all()));
        try {
            $response = $this->refundService->disburseRefund($request->all());
            return $this->serviceResponse($response);
        } catch (Exception $ex) {
            Log::error("refundController disburseRefund: " . $ex->getMessage());
            return $this->failedResponse();
        }
    }
}
