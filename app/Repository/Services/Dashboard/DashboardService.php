<?php

namespace App\Repository\Services\Dashboard;

use Exception;
use Illuminate\Support\Facades\Log;
use DB;
use GuzzleHttp\Psr7\Request;
use Carbon\Carbon;

class DashboardService
{

    protected $contact;

    public function index()
    {
        try {

            $totalTours = DB::table('trips')->count();
            $totalVehicles = DB::table('vehicles')->count();
            $totalPayments = DB::table('payments')->count();

            $monthlyPayments = DB::table('payments')
                ->whereMonth('created_at', '=', date('m'))
                ->whereYear('created_at', '=', date('Y'))
                ->sum('amount');

            // origin wise trips count
            $tripData = DB::table('trips')
                ->join('routes', 'trips.route_id', '=', 'routes.id')
                ->select(DB::raw('COUNT(*) as trip_exist'), 'origin')
                ->groupBy('origin')
                ->get();

            //channel wise payment data
            $paymentData = DB::table('payments')
                ->select(DB::raw('SUM(amount) as total_amount'), DB::raw('COUNT(*) as payment_held'), 'payment_method')
                ->groupBy('payment_method')
                ->get();






            return ["totalTours" => $totalTours, "totalVehicles" => $totalVehicles, "totalPayments" => $totalPayments, "monthlyPayments" => $monthlyPayments, "tripData" => $tripData, "paymentData" => $paymentData];
        } catch (Exception $ex) {
            Log::alert($ex->getMessage());
        }
    }
}
