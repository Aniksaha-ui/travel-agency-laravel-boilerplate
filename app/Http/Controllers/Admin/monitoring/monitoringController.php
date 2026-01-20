<?php

namespace App\Http\Controllers\Admin\monitoring;

use App\Http\Controllers\Controller;
use App\Repository\Services\QueryMonitoring\QueryMonitoringService;
use Exception;
use Illuminate\Http\Request;

class monitoringController extends Controller
{
    protected $queryMonitoringService;
    public function __construct(QueryMonitoringService $queryMonitoringService)
    {
        $this->queryMonitoringService = $queryMonitoringService;
    }

    public function monitoring(Request $request)
    {
        $page = $request->query('page');
        $search = $request->query('search');
        try {

            $response = $this->queryMonitoringService->getQueryPerformance();
            return response()->json([
                "isExecute" => $response['status'],
                "data" => $response['data'],
                "message" => $response['message']
            ], 200);
        } catch (Exception $e) {
        }
    }
}
