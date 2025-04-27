<?php

namespace App\Repository\Services\Booking;

use Exception;
use Illuminate\Support\Facades\Log;
use DB;
use GuzzleHttp\Psr7\Request;

class BookingService
{

    protected $contact;

    public function index($page, $search)
    {
        try {
            $perPage = 10;
            $bookings = DB::table('bookings')
                ->join('users', 'bookings.user_id', '=', 'users.id')
                ->join('trips', 'bookings.trip_id', '=', 'trips.id')
                ->leftJoin('packages', 'bookings.package_id', '=', 'packages.id')
                ->where('bookings.seat_ids', 'like', '%' . $search . '%')
                ->orWhere('trips.trip_name', 'like', '%' . $search . '%')
                ->orWhere('trips.price', 'like', '%' . $search . '%')
                ->paginate($perPage, ['bookings.id', 'bookings.user_id', 'bookings.seat_ids', 'bookings.booking_type', 'bookings.created_at', 'bookings.status', 'bookings.booking_type', 'trips.trip_name', 'trips.price', 'users.name', 'packages.name','users.name','users.email'], 'page', $page);
            return $bookings;
        } catch (Exception $ex) {
            Log::alert($ex->getMessage());
        }
    }

    public function tripwiseBooking($data)
    {
        try {
            $tripId = $data['trip_id'] ?? null;

            if ($tripId != null) {
                $tripSummaries = DB::table('trips as t')
                    ->join('vehicles as v', 't.vehicle_id', '=', 'v.id')
                    ->join('seats as s', 'v.id', '=', 's.vehicle_id')
                    ->join('routes as r', 't.route_id', '=', 'r.id')
                    ->leftJoin('seat_availablities as sa', function ($join) {
                        $join->on('sa.trip_id', '=', 't.id')
                            ->on('sa.seat_id', '=', 's.id');
                    })
                    ->select(
                        't.id as trip_id',
                        't.trip_name as trip_name',
                        't.price as price',
                        't.image as image',
                        't.description as description',
                        't.status as status',
                        'r.route_name',
                        DB::raw('COUNT(s.id) as total_seats'),
                        DB::raw('COUNT(CASE WHEN sa.is_available = 0 THEN 1 END) as booked_seats'),
                        DB::raw('COUNT(CASE WHEN sa.is_available = 1 THEN 1 END) as available_seats'),
                        DB::raw('SUM(CASE WHEN sa.is_available = 0 THEN t.price ELSE 0 END) as revenue')
                    )
                    ->where('sa.trip_id', $tripId)
                    ->groupBy('trip_id', 'sa.trip_id', 't.trip_name', 't.image', 't.description', 't.status', 'r.route_name', 't.price')
                    ->get();

                Log::info($tripSummaries);

                $seats = DB::table('trips as t')
                    ->join('vehicles as v', 't.vehicle_id', '=', 'v.id')
                    ->join('seats as s', 'v.id', '=', 's.vehicle_id')
                    ->leftJoin('seat_availablities as sa', function ($join) {
                        $join->on('sa.trip_id', '=', 't.id')
                            ->on('sa.seat_id', '=', 's.id');
                    })
                    ->select(
                        't.id as trip_id',
                        's.id as seat_id',
                        's.seat_number',
                        'v.vehicle_name',
                        'sa.is_available'
                    )
                    ->where('sa.trip_id', $tripId)
                    ->get()
                    ->unique('seat_id')
                    ->values();


                Log::info(DB::table('trips as t')
                    ->join('vehicles as v', 't.vehicle_id', '=', 'v.id')
                    ->join('seats as s', 'v.id', '=', 's.vehicle_id')
                    ->leftJoin('seat_availablities as sa', function ($join) {
                        $join->on('sa.trip_id', '=', 't.id')
                            ->on('sa.seat_id', '=', 's.id');
                    })
                    ->select(
                        't.id as trip_id',
                        's.id as seat_id',
                        's.seat_number',
                        'v.vehicle_name',
                        'sa.is_available'
                    )
                    ->where('sa.trip_id', $tripId)
                    ->get()
                    ->unique('seat_id')
                    ->values());


                return ["tripSummaries" => $tripSummaries, "seat_layout" => $seats ?? []];
            }

            $tripSummaries = DB::table('trips as t')
                ->join('vehicles as v', 't.vehicle_id', '=', 'v.id')
                ->join('seats as s', 'v.id', '=', 's.vehicle_id')
                ->join('routes as r', 't.route_id', '=', 'r.id')
                ->leftJoin('seat_availablities as sa', function ($join) {
                    $join->on('sa.trip_id', '=', 't.id')
                        ->on('sa.seat_id', '=', 's.id');
                })
                ->select(
                    't.id as trip_id',
                    'r.route_name',
                    DB::raw('COUNT(s.id) as total_seats'),
                    DB::raw('COUNT(CASE WHEN sa.is_available = 0 THEN 1 END) as booked_seats'),
                    DB::raw('COUNT(CASE WHEN sa.is_available = 1 THEN 1 END) as available_seats'),
                    DB::raw('SUM(CASE WHEN sa.is_available = 0 THEN t.price ELSE 0 END) as revenue')
                )
                ->groupBy('t.id', 'r.route_name')
                ->get();

            return $tripSummaries;
        } catch (Exception $ex) {
            Log::alert($ex->getMessage());
        }
    }

    public function dailybookingReport($date)
    {
        try {

            $dailyReport = DB::table('bookings')
                ->join('trips', 'bookings.trip_id', '=', 'trips.id')
                ->join('payments', 'bookings.id', '=', 'payments.booking_id')
                ->select(
                    'trips.trip_name as trip_name',
                    'trips.route_id as trip_route',
                    DB::raw('DATE(bookings.created_at) as booking_date'),
                    DB::raw('COUNT(bookings.id) as total_bookings'),
                    DB::raw('SUM(payments.amount) as total_revenue')
                )
                ->whereDate('bookings.created_at', '=', $date)
                ->groupBy('trip_name', 'booking_date', 'trip_route')
                ->orderBy('booking_date', 'desc')
                ->get();

            Log::info("daily report" . $dailyReport ?? []);

            return $dailyReport;
        } catch (Exception $ex) {
            Log::alert($ex->getMessage());
        }
    }
}
