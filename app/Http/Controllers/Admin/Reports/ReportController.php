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
    public function vehicleWiseSeatTotalReport(Request $request)
    {
        $page = $request->query('page');
        $search = $request->query('search');

        $response = $this->reportService->vehicleWiseSeatTotalReport($page, $search);
        return response()->json([
            "data" => $response,
            "message" => "success"
        ], 200);
    }

    public function vehicleWiseAllSeatReport($id, Request $request)
    {
        $vehicleId = $id;
        $page = $request->query('page');
        $search = $request->query('search');
        $response = $this->reportService->vehicleWiseAllSeatReport($vehicleId, $page, $search);
        return response()->json([
            "data" => $response,
            "message" => "success"
        ], 200);
    }

    public function accountBalance()
    {
        $response = $this->reportService->accountBalance();
        return response()->json([
            "data" => $response,
            "message" => "success"
        ], 200);
    }


    public function accountHistory($type)
    {
        $response = $this->reportService->accountHistory($type);
        return response()->json([
            "data" => $response,
            "message" => "success"
        ], 200);
    }

    public function packageWiseBooking(Request $request)
    {
    
        try{
            $response = $this->reportService->vehicleWiseBookingReport();
            if($response['status'] ==true){
                return response()->json([
                    "data" => $response['data'],
                    "status" => $response['status'],
                    "message" => $response['message']
                ], 200);
            }
        } catch(\Exception $ex){
            return response()->json([
                "data" => [],
                "status" => false,
                "message" => "Internal Server Error"
            ], 500);
        }
    }

    public function useageOfVehicle(Request $request)
    {
        try {
            $page = $request->query('page');
            $search = $request->query('search');
            $start_date = $request->input('start_date');
            $end_date = $request->input('end_date');
            $response = $this->reportService->useageOfVehicle($page, $search,$start_date, $end_date);
            return response()->json([
                "data" => $response['data'],
                "status" => $response['status'],
                "message" => $response['message']
            ], 200);
        } catch (\Exception $ex) {
            return response()->json([
                "data" => [],
                "status" => false,
                "message" => "Internal Server Error"
            ], 500);
        }
    }

    public function tripwiseBookingUsers($tripId)
    {
        try {
            $response = $this->reportService->tripwiseBookingUsers($tripId);
            return response()->json([
                "data" => $response['data'],
                "status" => $response['status'],
                "message" => $response['message']
            ], 200);
        } catch (\Exception $ex) {
            return response()->json([
                "data" => [],
                "status" => false,
                "message" => "Internal Server Error"
            ], 500);
        }
    }


}
