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



        public function routeWiseSalesSummary(){
        try{
            $response = $this->reportService->routeWiseSalesSummary();

            if($response && $response['status']){
                return response()->json([
                    "data" => $response['data'],
                    "isExecute" => $response['status'],
                    "message" => $response['message']
                ], 200);
            }
       
        } catch(\Exception $ex){
            Log::info("Error in ReportController - routeWiseSalesSummary function: " .$ex->getMessage() );
            return response()->json([
                "data" => [],
                "isExecute" => ApiResponseStatus::FAILED,
                "message" => "Internal Server Error"
            ], 500);
        }
    }


     public function currentMonthTripSales(){
        try{
            $response = $this->reportService->currentMonthTripSales();

            if($response && $response['status']){
                return response()->json([
                    "data" => $response['data'],
                    "isExecute" => $response['status'],
                    "message" => $response['message']
                ], 200);
            }
       
        } catch(\Exception $ex){
            Log::info("Error in ReportController - currentMonthTripSales function: " .$ex->getMessage() );
            return response()->json([
                "data" => [],
                "isExecute" => ApiResponseStatus::FAILED,
                "message" => "Internal Server Error"
            ], 500);
        }
    }

    public function unpaidBookingReport(){
        try{
            $response = $this->reportService->unpaidBookingReport();

            if($response && $response['status']){
                return response()->json([
                    "data" => $response['data'],
                    "isExecute" => $response['status'],
                    "message" => $response['message']
                ], 200);
            }
       
        } catch(\Exception $ex){
            Log::info("Error in ReportController - unpaidBookingReport function: " .$ex->getMessage() );
            return response()->json([
                "data" => [],
                "isExecute" => ApiResponseStatus::FAILED,
                "message" => "Internal Server Error"
            ], 500);
        }
    }

    public function userGrowthReport(){
        try{
            $response = $this->reportService->userGrowthReport();

            if($response && $response['status']){
                return response()->json([
                    "data" => $response['data'],
                    "isExecute" => $response['status'],
                    "message" => $response['message']
                ], 200);
            }
       
        } catch(\Exception $ex){
            Log::info("Error in ReportController - userGrowthReport function: " .$ex->getMessage() );
            return response()->json([
                "data" => [],
                "isExecute" => ApiResponseStatus::FAILED,
                "message" => "Internal Server Error"
            ], 500);
        }
    }

    public function ticketStatusReport(){
        try{
            $response = $this->reportService->ticketStatusReport();

            if($response && $response['status']){
                return response()->json([
                    "data" => $response['data'],
                    "isExecute" => $response['status'],
                    "message" => $response['message']
                ], 200);
            }
       
        } catch(\Exception $ex){
            Log::info("Error in ReportController - ticketStatusReport function: " .$ex->getMessage() );
            return response()->json([
                "data" => [],
                "isExecute" => ApiResponseStatus::FAILED,
                "message" => "Internal Server Error"
            ], 500);
        }
    }

    public function refundStatusReport(){
        try{
            $response = $this->reportService->refundStatusReport();

            if($response && $response['status']){
                return response()->json([
                    "data" => $response['data'],
                    "isExecute" => $response['status'],
                    "message" => $response['message']
                ], 200);
            }
       
        } catch(\Exception $ex){
            Log::info("Error in ReportController - refundStatusReport function: " .$ex->getMessage() );
            return response()->json([
                "data" => [],
                "isExecute" => ApiResponseStatus::FAILED,
                "message" => "Internal Server Error"
            ], 500);
        }
    }

    public function lowOccupancyTripReport(){
        try{
            $response = $this->reportService->lowOccupancyTripReport();

            if($response && $response['status']){
                return response()->json([
                    "data" => $response['data'],
                    "isExecute" => $response['status'],
                    "message" => $response['message']
                ], 200);
            }
       
        } catch(\Exception $ex){
            Log::info("Error in ReportController - lowOccupancyTripReport function: " .$ex->getMessage() );
            return response()->json([
                "data" => [],
                "isExecute" => ApiResponseStatus::FAILED,
                "message" => "Internal Server Error"
            ], 500);
        }
    }

    public function avgBookingValueReport(){
        try{
            $response = $this->reportService->avgBookingValueReport();

            if($response && $response['status']){
                return response()->json([
                    "data" => $response['data'],
                    "isExecute" => $response['status'],
                    "message" => $response['message']
                ], 200);
            }
       
        } catch(\Exception $ex){
            Log::info("Error in ReportController - avgBookingValueReport function: " .$ex->getMessage() );
            return response()->json([
                "data" => [],
                "isExecute" => ApiResponseStatus::FAILED,
                "message" => "Internal Server Error"
            ], 500);
        }
    }

    public function lowPerformingPackages(){
        try{
            $response = $this->reportService->lowPerformingPackages();

            if($response && $response['status']){
                return response()->json([
                    "data" => $response['data'],
                    "isExecute" => $response['status'],
                    "message" => $response['message']
                ], 200);
            }
       
        } catch(\Exception $ex){
            Log::info("Error in ReportController - lowPerformingPackages function: " .$ex->getMessage() );
            return response()->json([
                "data" => [],
                "isExecute" => ApiResponseStatus::FAILED,
                "message" => "Internal Server Error"
            ], 500);
        }
    }

    public function highCancellationPackages(){
        try{
            $response = $this->reportService->highCancellationPackages();

            if($response && $response['status']){
                return response()->json([
                    "data" => $response['data'],
                    "isExecute" => $response['status'],
                    "message" => $response['message']
                ], 200);
            }
       
        } catch(\Exception $ex){
            Log::info("Error in ReportController - highCancellationPackages function: " .$ex->getMessage() );
            return response()->json([
                "data" => [],
                "isExecute" => ApiResponseStatus::FAILED,
                "message" => "Internal Server Error"
            ], 500);
        }
    }

    public function packageProfitMargin(){
        try{
            $response = $this->reportService->packageProfitMargin();

            if($response && $response['status']){
                return response()->json([
                    "data" => $response['data'],
                    "isExecute" => $response['status'],
                    "message" => $response['message']
                ], 200);
            }
       
        } catch(\Exception $ex){
            Log::info("Error in ReportController - packageProfitMargin function: " .$ex->getMessage() );
            return response()->json([
                "data" => [],
                "isExecute" => ApiResponseStatus::FAILED,
                "message" => "Internal Server Error"
            ], 500);
        }
    }
    public function hotelPerformanceReport()
    {
        try {
            $response = $this->reportService->hotelPerformanceReport();
            return response()->json($response, 200);
        } catch (\Exception $ex) {
            return response()->json(["status" => false, "message" => "Internal Server Error"], 500);
        }
    }

    public function roomTypePopularityReport()
    {
        try {
            $response = $this->reportService->roomTypePopularityReport();
            return response()->json($response, 200);
        } catch (\Exception $ex) {
            return response()->json(["status" => false, "message" => "Internal Server Error"], 500);
        }
    }

    public function refundReasonAnalysis()
    {
        try {
            $response = $this->reportService->refundReasonAnalysis();
            return response()->json($response, 200);
        } catch (\Exception $ex) {
            return response()->json(["status" => false, "message" => "Internal Server Error"], 500);
        }
    }

    public function vehicleTypePerformanceReport()
    {
        try {
            $response = $this->reportService->vehicleTypePerformanceReport();
            return response()->json($response, 200);
        } catch (\Exception $ex) {
            return response()->json(["status" => false, "message" => "Internal Server Error"], 500);
        }
    }

    public function packagePassengerSummary()
    {
        try {
            $response = $this->reportService->packagePassengerSummary();
            return response()->json($response, 200);
        } catch (\Exception $ex) {
            return response()->json(["status" => false, "message" => "Internal Server Error"], 500);
        }
    }

    public function cityWiseHotelRevenue()
    {
        try {
            $response = $this->reportService->cityWiseHotelRevenue();
            return response()->json($response, 200);
        } catch (\Exception $ex) {
            return response()->json(["status" => false, "message" => "Internal Server Error"], 500);
        }
    }

    public function monthlyBookingTrend()
    {
        try {
            $response = $this->reportService->monthlyBookingTrend();
            return response()->json($response, 200);
        } catch (\Exception $ex) {
            return response()->json(["status" => false, "message" => "Internal Server Error"], 500);
        }
    }

    public function paymentMethodAnalytics()
    {
        try {
            $response = $this->reportService->paymentMethodAnalytics();
            return response()->json($response, 200);
        } catch (\Exception $ex) {
            return response()->json(["status" => false, "message" => "Internal Server Error"], 500);
        }
    }

    public function guidePerformanceVsCost()
    {
        try {
            $response = $this->reportService->guidePerformanceVsCost();
            return response()->json($response, 200);
        } catch (\Exception $ex) {
            return response()->json(["status" => false, "message" => "Internal Server Error"], 500);
        }
    }

    public function hotelGuestStatusReport()
    {
        try {
            $response = $this->reportService->hotelGuestStatusReport();
            return response()->json($response, 200);
        } catch (\Exception $ex) {
            return response()->json(["status" => false, "message" => "Internal Server Error"], 500);
        }
    }

    public function packageInclusionRevenue()
    {
        try {
            $response = $this->reportService->packageInclusionRevenue();
            return response()->json($response, 200);
        } catch (\Exception $ex) {
            return response()->json(["status" => false, "message" => "Internal Server Error"], 500);
        }
    }

    public function userLoyaltyAnalytics()
    {
        try {
            $response = $this->reportService->userLoyaltyAnalytics();
            return response()->json($response, 200);
        } catch (\Exception $ex) {
            return response()->json(["status" => false, "message" => "Internal Server Error"], 500);
        }
    }

    public function routeEfficiencyAnalytics()
    {
        try {
            $response = $this->reportService->routeEfficiencyAnalytics();
            return response()->json($response, 200);
        } catch (\Exception $ex) {
            return response()->json(["status" => false, "message" => "Internal Server Error"], 500);
        }
    }

    public function bookingLeadTimeAnalysis()
    {
        try {
            $response = $this->reportService->bookingLeadTimeAnalysis();
            return response()->json($response, 200);
        } catch (\Exception $ex) {
            return response()->json(["status" => false, "message" => "Internal Server Error"], 500);
        }
    }

    public function occupancyAlertReport()
    {
        try {
            $response = $this->reportService->occupancyAlertReport();
            return response()->json($response, 200);
        } catch (\Exception $ex) {
            return response()->json(["status" => false, "message" => "Internal Server Error"], 500);
        }
    }
}
