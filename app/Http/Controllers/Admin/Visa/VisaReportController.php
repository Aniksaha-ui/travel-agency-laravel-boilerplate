<?php

namespace App\Http\Controllers\Admin\Visa;

use App\Constants\ApiResponseStatus;
use App\Http\Controllers\Controller;
use App\Repository\Services\Visa\VisaApplicationService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class VisaReportController extends Controller
{
    private $visaApplicationService;

    public function __construct(VisaApplicationService $visaApplicationService)
    {
        $this->visaApplicationService = $visaApplicationService;
    }

    public function summary(Request $request)
    {
        try {
            $response = $this->visaApplicationService->getReportSummary(
                $request->query('date_from'),
                $request->query('date_to')
            );

            return response()->json($response, 200);
        } catch (Exception $exception) {
            Log::error('VisaReportController summary error: ' . $exception->getMessage());

            return response()->json([
                'status' => ApiResponseStatus::FAILED,
                'message' => 'Internal Server Error',
            ], 500);
        }
    }
}
