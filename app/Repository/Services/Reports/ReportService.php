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
            $accountInformation = DB::table('company_accounts')->get();
            Log::info(json_encode($accountInformation));
            return $accountInformation;
        } catch (Exception $ex) {
            Log::alert($ex->getMessage());
        }
    }

    public function accountHistory($userAccountType)
    {
        try {
            $accountHistory = DB::table('account_history')->where('user_account_type', $userAccountType)->get();
            return $accountHistory;
        } catch (Exception $ex) {
            Log::alert($ex->getMessage());
        }
    }

    public function packageWiseBookingReport()
    {
        try {
            $report =  DB::table('package_bookings as pb')
                ->join('packages as p', 'pb.package_id', '=', 'p.id')
                ->select(
                    'pb.package_id',
                    'p.name as package_name',
                    DB::raw('COUNT(pb.id) as number_of_bookings'),
                    DB::raw('SUM(pb.total_adult) as total_adult'),
                    DB::raw('SUM(pb.total_child) as total_child')
                )
                ->groupBy('pb.package_id', 'p.name')
                ->orderByDesc('number_of_bookings')
                ->get();
            if ($report->count() > 0) {
                return ["status" => true, "data" => $report, "message" => "Report retrieved successfully"];
            } else {
                return ["status" => true, "data" => [], "message" => "No Report found"];
            }
        } catch (Exception $ex) {
            Log::alert($ex->getMessage());
            return ["status" => false, "data" => [], "message" => "server error"];
        }
    }


    function useageOfVehicle($page, $search, $start_date, $end_date)
    {
        try {
            $perPage = 10;
            $query = DB::table('vehicle_trip_trackings')
                ->join('vehicles', 'vehicle_trip_trackings.vehicle_id', '=', 'vehicles.id')
                ->join('trips', 'vehicle_trip_trackings.trip_id', '=', 'trips.id');
            if ($start_date && $end_date) {
                $query = $query->whereBetween('vehicle_trip_trackings.travel_start_date', [$start_date, $end_date]);
            }
            $query = $query->when($search, function ($query, $search) {
                return $query->where('vehicles.vehicle_name', 'like', '%' . $search . '%')
                    ->orWhere('trips.trip_name', 'like', '%' . $search . '%');
            });
            $report = $query->paginate($perPage, ['vehicle_trip_trackings.*', 'trips.trip_name', 'vehicles.vehicle_name'], 'page', $page);
            if ($report->count() > 0) {
                return ["status" => true, "data" => $report, "message" => "Report retrieved successfully"];
            } else {
                return ["status" => true, "data" => [], "message" => "No report found"];
            }
        } catch (Exception $ex) {
            Log::alert($ex->getMessage());
            return ["status" => false, "data" => [], "message" => "server error"];
        }
    }


    public function tripwiseBookingUsers($tripId)
    {
        try {
            $report = DB::table('bookings')
                ->join('users', 'bookings.user_id', '=', 'users.id')
                ->where('bookings.trip_id', $tripId)
                ->where('bookings.status', '!=', 'cancelled')
                ->select('users.name', 'users.email', 'bookings.created_at as booking_date', 'bookings.status')
                ->get();

            if ($report->count() > 0) {
                return ["status" => true, "data" => $report, "message" => "Report retrieved successfully"];
            } else {
                return ["status" => true, "data" => [], "message" => "No Report found"];
            }
        } catch (Exception $ex) {
            Log::alert($ex->getMessage());
            return ["status" => false, "data" => [], "message" => "server error"];
        }
    }
}
