<?php

namespace App\Http\Controllers\Admin\Reports;

use App\Http\Controllers\Controller;
use App\Repository\Services\Reports\ReportService;
use Illuminate\Http\Request;

class ReportController extends Controller
{

    protected $reportService;
    public function __construct(ReportService $reportService)
    {
        $this->reportService = $reportService;
    }
    public function vehicleWiseSeatTotalReport(Request $request){
        $page = $request->query('page');
        $search = $request->query('search');
      
        $response = $this->reportService->vehicleWiseSeatTotalReport($page,$search);
        return response()->json([
            "data"=> $response,
            "message"=> "success"
        ],200);
    }

    public function vehicleWiseAllSeatReport($id,Request $request){
        $vehicleId = $id;
        $page = $request->query('page');
        $search = $request->query('search');
        $response = $this->reportService->vehicleWiseAllSeatReport($vehicleId,$page,$search);
        return response()->json([
            "data"=> $response,
            "message"=> "success"
        ],200);
    }
}
