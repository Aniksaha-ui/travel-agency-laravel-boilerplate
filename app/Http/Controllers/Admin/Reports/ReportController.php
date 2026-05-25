<?php

namespace App\Http\Controllers\Admin\Reports;

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
        try {
            $page = $request->query('page');
            $search = $request->query('search');

            $response = $this->reportService->vehicleWiseSeatTotalReport($page, $search);
            return $this->successResponse($response);
        } catch (\Exception $ex) {
            Log::info("Error in ReportController - vehicleWiseSeatTotalReport function: " . $ex->getMessage());
            return $this->failedResponse();
        }
    }

    public function vehicleWiseAllSeatReport($id, Request $request)
    {
        $vehicleId = $id;
        $page = $request->query('page');
        $search = $request->query('search');
        $response = $this->reportService->vehicleWiseAllSeatReport($vehicleId, $page, $search);
        return $this->successResponse($response);
    }

    public function accountBalance()
    {
        try {
            $response = $this->reportService->accountBalance();
            return $this->successResponse($response);
        } catch (\Exception $ex) {
            Log::info("Error in ReportController - accountBalance function: " . $ex->getMessage());
            return $this->failedResponse();
        }
    }


    public function accountHistory($type)
    {
        $response = $this->reportService->accountHistory($type);
        return $this->successResponse($response);
    }

    public function packageWiseBooking(Request $request)
    {

        try {
            $response = $this->reportService->packageWiseBookingReport();
            return $this->serviceResponse($response);
        } catch (\Exception $ex) {
            return $this->failedResponse();
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
            return $this->serviceResponse($response);
        } catch (\Exception $ex) {
            return $this->failedResponse();
        }
    }

    public function tripwiseBookingUsers($tripId)
    {
        try {
            $response = $this->reportService->tripwiseBookingUsers($tripId);
            return $this->serviceResponse($response);
        } catch (\Exception $ex) {
            return $this->failedResponse();
        }
    }

    public function tripPerformance(Request $request)
    {
        try {
            $page = $request->query('page');
            $search = $request->query('search');

            $response = $this->reportService->tripPerformance($page, $search);
            return $this->serviceResponse($response);
        } catch (\Exception $ex) {
            return $this->failedResponse();
        }
    }


    public function packagePerformance(Request $request)
    {
        try {
            $page = $request->query('page');
            $search = $request->query('search');
            $response = $this->reportService->packagePerformance($page, $search);
            return $this->serviceResponse($response);
        } catch (\Exception $ex) {
            return $this->failedResponse();
        }
    }


    public function guideEfficencyReport()
    {
        try {
            $response = $this->reportService->guideEfficencyReport();
            return $this->serviceResponse($response);
        } catch (\Exception $ex) {
            return $this->failedResponse();
        }
    }

    public function customerValueReport(Request $request)
    {
        try {

            $page = $request->query('page');
            $search = $request->query('search');


            $response = $this->reportService->customerValueReport($page, $search);
            return $this->serviceResponse($response);
        } catch (\Exception $ex) {
            return $this->failedResponse();
        }
    }


    public function transactionHistoryReport(Request $request)
    {
        try {

            $page = $request->query('page');
            $search = $request->query('search');
            $response = $this->reportService->transactionHistoryReport($page, $search);
            return $this->serviceResponse($response);
        } catch (\Exception $ex) {
            return $this->failedResponse();
        }
    }

    function monthRunningBalanceReport(Request $request)
    {
        try {
            $page = $request->query('page');
            $search = $request->query('search');
            $response = $this->reportService->monthRunningBalanceReport($page, $search);
            return $this->serviceResponse($response);
        } catch (\Exception $ex) {
            return $this->failedResponse();
        }
    }


    function dailyBalanceReport(Request $request)
    {
        try {
            $page = $request->query('page');
            $search = $request->query('search');
            $month = $request->query('month');
            $fromDate = $request->query('from_date');
            $toDate = $request->query('to_date');
            $response = $this->reportService->dailyBalanceReport($page, $search, $month, $fromDate, $toDate);
            return $this->serviceResponse($response);
        } catch (\Exception $ex) {
            return $this->failedResponse();
        }
    }

    function financialReport(Request $request)
    {
        try {
            $page = $request->query('page');
            $search = $request->query('search');
            $response = $this->reportService->financialReport($page, $search);
            return $this->serviceResponse($response);
        } catch (\Exception $ex) {
            return $this->failedResponse();
        }
    }

    function financialReportById($financialReportId)
    {

        try {

            $response = $this->reportService->financialReportById($financialReportId);
            return $this->serviceResponse($response);
        } catch (\Exception $ex) {
            return $this->failedResponse();
        }
    }

    public function bookingSummary()
    {
        try {
            $response = $this->reportService->bookingSummary();
            return $this->serviceResponse($response);
        } catch (\Exception $ex) {
            Log::info("Error in ReportController - bookingSummary function: " . $ex->getMessage());
            return $this->failedResponse();
        }
    }



    public function salesSummary()
    {
        try {
            $response = $this->reportService->salesSummary();
            return $this->serviceResponse($response);
        } catch (\Exception $ex) {
            Log::info("Error in ReportController - salesSummary function: " . $ex->getMessage());
            return $this->failedResponse();
        }
    }



    public function routeWiseSalesSummary()
    {
        try {
            $response = $this->reportService->routeWiseSalesSummary();
            return $this->serviceResponse($response);
        } catch (\Exception $ex) {
            Log::info("Error in ReportController - routeWiseSalesSummary function: " . $ex->getMessage());
            return $this->failedResponse();
        }
    }


    public function currentMonthTripSales()
    {
        try {
            $response = $this->reportService->currentMonthTripSales();
            return $this->serviceResponse($response);
        } catch (\Exception $ex) {
            Log::info("Error in ReportController - currentMonthTripSales function: " . $ex->getMessage());
            return $this->failedResponse();
        }
    }

    public function unpaidBookingReport()
    {
        try {
            $response = $this->reportService->unpaidBookingReport();
            return $this->serviceResponse($response);
        } catch (\Exception $ex) {
            Log::info("Error in ReportController - unpaidBookingReport function: " . $ex->getMessage());
            return $this->failedResponse();
        }
    }

    public function userGrowthReport()
    {
        try {
            $response = $this->reportService->userGrowthReport();
            return $this->serviceResponse($response);
        } catch (\Exception $ex) {
            Log::info("Error in ReportController - userGrowthReport function: " . $ex->getMessage());
            return $this->failedResponse();
        }
    }

    public function ticketStatusReport()
    {
        try {
            $response = $this->reportService->ticketStatusReport();
            return $this->serviceResponse($response);
        } catch (\Exception $ex) {
            Log::info("Error in ReportController - ticketStatusReport function: " . $ex->getMessage());
            return $this->failedResponse();
        }
    }

    public function refundStatusReport()
    {
        try {
            $response = $this->reportService->refundStatusReport();
            return $this->serviceResponse($response);
        } catch (\Exception $ex) {
            Log::info("Error in ReportController - refundStatusReport function: " . $ex->getMessage());
            return $this->failedResponse();
        }
    }

    public function lowOccupancyTripReport()
    {
        try {
            $response = $this->reportService->lowOccupancyTripReport();
            return $this->serviceResponse($response);
        } catch (\Exception $ex) {
            Log::info("Error in ReportController - lowOccupancyTripReport function: " . $ex->getMessage());
            return $this->failedResponse();
        }
    }

    public function avgBookingValueReport()
    {
        try {
            $response = $this->reportService->avgBookingValueReport();
            return $this->serviceResponse($response);
        } catch (\Exception $ex) {
            Log::info("Error in ReportController - avgBookingValueReport function: " . $ex->getMessage());
            return $this->failedResponse();
        }
    }

    public function lowPerformingPackages()
    {
        try {
            $response = $this->reportService->lowPerformingPackages();
            return $this->serviceResponse($response);
        } catch (\Exception $ex) {
            Log::info("Error in ReportController - lowPerformingPackages function: " . $ex->getMessage());
            return $this->failedResponse();
        }
    }

    public function highCancellationPackages()
    {
        try {
            $response = $this->reportService->highCancellationPackages();
            return $this->serviceResponse($response);
        } catch (\Exception $ex) {
            Log::info("Error in ReportController - highCancellationPackages function: " . $ex->getMessage());
            return $this->failedResponse();
        }
    }

    public function packageProfitMargin()
    {
        try {
            $response = $this->reportService->packageProfitMargin();
            return $this->serviceResponse($response);
        } catch (\Exception $ex) {
            Log::info("Error in ReportController - packageProfitMargin function: " . $ex->getMessage());
            return $this->failedResponse();
        }
    }
    public function hotelPerformanceReport()
    {
        try {
            $response = $this->reportService->hotelPerformanceReport();
            return $this->serviceResponse($response);
        } catch (\Exception $ex) {
            return $this->failedResponse();
        }
    }

    public function roomTypePopularityReport()
    {
        try {
            $response = $this->reportService->roomTypePopularityReport();
            return $this->serviceResponse($response);
        } catch (\Exception $ex) {
            return $this->failedResponse();
        }
    }

    public function refundReasonAnalysis()
    {
        try {
            $response = $this->reportService->refundReasonAnalysis();
            return $this->serviceResponse($response);
        } catch (\Exception $ex) {
            return $this->failedResponse();
        }
    }

    public function vehicleTypePerformanceReport()
    {
        try {
            $response = $this->reportService->vehicleTypePerformanceReport();
            return $this->serviceResponse($response);
        } catch (\Exception $ex) {
            return $this->failedResponse();
        }
    }

    public function packagePassengerSummary()
    {
        try {
            $response = $this->reportService->packagePassengerSummary();
            return $this->serviceResponse($response);
        } catch (\Exception $ex) {
            return $this->failedResponse();
        }
    }

    public function cityWiseHotelRevenue()
    {
        try {
            $response = $this->reportService->cityWiseHotelRevenue();
            return $this->serviceResponse($response);
        } catch (\Exception $ex) {
            return $this->failedResponse();
        }
    }

    public function monthlyBookingTrend()
    {
        try {
            $response = $this->reportService->monthlyBookingTrend();
            return $this->serviceResponse($response);
        } catch (\Exception $ex) {
            return $this->failedResponse();
        }
    }

    public function paymentMethodAnalytics()
    {
        try {
            $response = $this->reportService->paymentMethodAnalytics();
            return $this->serviceResponse($response);
        } catch (\Exception $ex) {
            return $this->failedResponse();
        }
    }

    public function guidePerformanceVsCost()
    {
        try {
            $response = $this->reportService->guidePerformanceVsCost();
            return $this->serviceResponse($response);
        } catch (\Exception $ex) {
            return $this->failedResponse();
        }
    }

    public function hotelGuestStatusReport()
    {
        try {
            $response = $this->reportService->hotelGuestStatusReport();
            return $this->serviceResponse($response);
        } catch (\Exception $ex) {
            return $this->failedResponse();
        }
    }

    public function packageInclusionRevenue()
    {
        try {
            $response = $this->reportService->packageInclusionRevenue();
            return $this->serviceResponse($response);
        } catch (\Exception $ex) {
            return $this->failedResponse();
        }
    }

    public function userLoyaltyAnalytics()
    {
        try {
            $response = $this->reportService->userLoyaltyAnalytics();
            return $this->serviceResponse($response);
        } catch (\Exception $ex) {
            return $this->failedResponse();
        }
    }

    public function routeEfficiencyAnalytics()
    {
        try {
            $response = $this->reportService->routeEfficiencyAnalytics();
            return $this->serviceResponse($response);
        } catch (\Exception $ex) {
            return $this->failedResponse();
        }
    }

    public function bookingLeadTimeAnalysis()
    {
        try {
            $response = $this->reportService->bookingLeadTimeAnalysis();
            return $this->serviceResponse($response);
        } catch (\Exception $ex) {
            return $this->failedResponse();
        }
    }

    public function occupancyAlertReport()
    {
        try {
            $response = $this->reportService->occupancyAlertReport();
            return $this->serviceResponse($response);
        } catch (\Exception $ex) {
            return $this->failedResponse();
        }
    }
    public function monthlyDailyBalanceReportsList(Request $request)
    {
        try {
            $page = $request->query('page');
            $search = $request->query('search');
            $response = $this->reportService->getMonthlyDailyBalanceReports($page, $search);
            return $this->serviceResponse($response);
        } catch (\Exception $ex) {
            Log::info("Error in ReportController - monthlyDailyBalanceReportsList function: " . $ex->getMessage());
            return $this->failedResponse();
        }
    }
}
