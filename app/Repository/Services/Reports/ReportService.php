<?php

namespace App\Repository\Services\Reports;

use Exception;
use Illuminate\Support\Facades\Log;
use DB;

class ReportService
{

    protected $contact;

    /**
     * Get all contacts.
     *
     * @return bool
     */
    public function vehicleWiseSeatTotalReport($page, $search)
    {

        try {
            $perPage = 10;
            $report = DB::table('vehicles')
                ->leftJoin('seats', 'seats.vehicle_id', '=', 'vehicles.id')
                ->select('vehicles.id as vehicle_id', 'vehicles.vehicle_name', 'vehicle_type', DB::raw('COUNT(seats.id) as available_seats'))
                ->when($search, function ($query, $search) {
                    return $query->where('vehicles.vehicle_name', 'like', '%' . $search . '%');
                })
                ->groupBy('vehicles.id', 'vehicles.vehicle_name')
                ->paginate($perPage, ['*'], 'page', $page);

            return $report;
        } catch (Exception $ex) {
            Log::alert($ex->getMessage());
        }
    }

    public function vehicleWiseAllSeatReport($vehicleId, $page, $search)
    {
        try {
            $perPage = 10;
            $report = DB::table('vehicles')
                ->join('seats', 'seats.vehicle_id', '=', 'vehicles.id')
                ->where('seat_number', 'like', '%' . $search . '%')
                ->where('vehicles.id', $vehicleId)
                ->get();
            return $report;
        } catch (Exception $ex) {
            Log::alert($ex->getMessage());
        }
    }

    public function accountBalance()
    {
        try {
            $accountInformation = DB::query('company_accounts')->get();
            return $accountInformation;
        } catch (Exception $ex) {
            Log::alert($ex->getMessage());
        }
    }
}
