<?php

namespace App\Http\Controllers\Admin\Reports;

use App\Constants\ApiResponseStatus;
use App\Http\Controllers\Controller;
use App\Repository\Services\Reports\ReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

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

        try {
            $response = $this->reportService->packageWiseBookingReport();
            if ($response['status'] == true) {
                return response()->json([
                    "data" => $response['data'],
                    "status" => $response['status'],
                    "message" => $response['message']
                ], 200);
            }
        } catch (\Exception $ex) {
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
            $response = $this->reportService->useageOfVehicle($page, $search, $start_date, $end_date);
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

    public function tripPerformance(Request $request)
    {
        try {
            $page = $request->query('page');
            $search = $request->query('search');

            $response = $this->reportService->tripPerformance($page, $search);
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


    public function packagePerformance(Request $request)
    {
        try {
            $page = $request->query('page');
            $search = $request->query('search');
            $response = $this->reportService->packagePerformance($page, $search);
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


    public function guideEfficencyReport()
    {
        try {
            $response = $this->reportService->guideEfficencyReport();
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

    public function customerValueReport(Request $request)
    {
        try {

            $page = $request->query('page');
            $search = $request->query('search');


            $response = $this->reportService->customerValueReport($page, $search);
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


    public function transactionHistoryReport(Request $request)
    {
        try {

            $page = $request->query('page');
            $search = $request->query('search');
            $response = $this->reportService->transactionHistoryReport($page, $search);
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

    function monthRunningBalanceReport(Request $request)
    {
        try {
            $page = $request->query('page');
            $search = $request->query('search');
            $response = $this->reportService->monthRunningBalanceReport($page, $search);
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


    function dailyBalanceReport(Request $request)
    {
        try {
            $page = $request->query('page');
            $search = $request->query('search');
            $response = $this->reportService->dailyBalanceReport($page, $search);
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

    function financialReport(Request $request)
    {
        try {
            $page = $request->query('page');
            $search = $request->query('search');
            $response = $this->reportService->financialReport($page, $search);
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

    function financialReportById($financialReportId)
    {

        try {

            $response = $this->reportService->financialReportById($financialReportId);
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

    public function bookingSummary(){
        try{
            $response = $this->reportService->bookingSummary();

            if($response && $response['status']){
                return response()->json([
                    "data" => $response['data'],
                    "isExecute" => $response['status'],
                    "message" => $response['message']
                ], 200);
            }
       
        } catch(\Exception $ex){
            Log::info("Error in ReportController - bookingSummary function: " .$ex->getMessage() );
            return response()->json([
                "data" => [],
                "isExecute" => ApiResponseStatus::FAILED,
                "message" => "Internal Server Error"
            ], 500);
        }
    }



       public function salesSummary(){
        try{
            $response = $this->reportService->salesSummary();

            if($response && $response['status']){
                return response()->json([
                    "data" => $response['data'],
                    "isExecute" => $response['status'],
                    "message" => $response['message']
                ], 200);
            }
       
        } catch(\Exception $ex){
            Log::info("Error in ReportController - salesSummary function: " .$ex->getMessage() );
            return response()->json([
                "data" => [],
                "isExecute" => ApiResponseStatus::FAILED,
                "message" => "Internal Server Error"
            ], 500);
        }
    }



}
