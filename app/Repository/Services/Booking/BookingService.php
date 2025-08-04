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
                ->leftJoin('users', 'bookings.user_id', '=', 'users.id')
                ->leftJoin('trips', 'bookings.trip_id', '=', 'trips.id')
                ->leftJoin('packages', 'bookings.package_id', '=', 'packages.id')
                ->leftJoin('hotel_bookings', 'hotel_bookings.id', '=', 'bookings.hotel_booking_id')
                ->leftJoin('hotels', 'hotels.id', '=', 'hotel_bookings.hotel_id')
                ->when($search, function ($query, $search) {
                    return $query->where('bookings.seat_ids', 'like', '%' . $search . '%')
                        ->orWhere('trips.trip_name', 'like', '%' . $search . '%')
                        ->orWhere('trips.price', 'like', '%' . $search . '%')
                        ->orWhere('hotels.name', 'like', '%' . $search . '%');
                })
                ->paginate($perPage, ['bookings.id', 'bookings.user_id', 'bookings.seat_ids', 'bookings.booking_type', 'bookings.created_at', 'bookings.status', 'bookings.booking_type', 'trips.trip_name', 'trips.price', 'users.name as username', 'packages.name as package_name', 'users.name', 'users.email', 'hotels.name as hotel_name'], 'page', $page);
            return $bookings;
        } catch (Exception $ex) {
            Log::alert("bookingService-index function" . $ex->getMessage());
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
            Log::alert("BookingService - tripwiseBooking function" . $ex->getMessage());
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

            Log::info("BookingService - response daily report" . $dailyReport ?? []);

            return $dailyReport;
        } catch (Exception $ex) {
            Log::alert("BookingService - dailybookingReport function" . $ex->getMessage());
        }
    }
    public function invoice($bookingId)
    {
        try {
            $invoice = DB::table('bookings')
                ->leftJoin('booking_seats', 'booking_seats.booking_id', '=', 'bookings.id')
                ->leftJoin('payments', 'payments.booking_id', '=', 'bookings.id')
                ->leftJoin('transactions', 'transactions.payment_id', '=', 'payments.id')
                ->leftJoin('trips', 'trips.id', '=', 'bookings.trip_id')
                ->leftJoin('users', 'users.id', '=', 'bookings.user_id')
                ->leftJoin('seats', 'seats.id', '=', 'booking_seats.seat_id')
                ->leftJoin('packages', 'packages.id', '=', 'bookings.package_id')
                ->leftJoin('hotel_bookings', 'hotel_bookings.id', '=', 'bookings.hotel_booking_id')
                ->leftJoin('hotels', 'hotel_bookings.hotel_id', '=', 'hotels.id')
                ->select(
                    'bookings.id as booking_id',
                    'bookings.status as booking_status',
                    'users.name as user_name',
                    'users.email as user_email',
                    'trips.id as trip_id',
                    'trips.trip_name',
                    'trips.price',
                    'trips.departure_time',
                    'trips.arrival_time',
                    'bookings.status as payment_status',
                    'bookings.booking_type',
                    DB::raw('GROUP_CONCAT(seats.seat_number) as seat_numbers'),
                    'bookings.seat_ids as booked_seats',
                    'payments.payment_method',
                    'payments.nagad',
                    'payments.bkash',
                    'payments.card',
                    'transactions.transaction_reference',
                    'packages.name as package_name',
                    'payments.amount as total_payment_amount',
                    'hotels.name as hotel_name',
                    'hotels.city as hotel_city',
                    'hotels.country as hotel_country',
                    'hotel_bookings.check_in_date',
                    'hotel_bookings.check_out_date',
                )
                ->where('bookings.id', $bookingId)
                ->groupBy(
                    'bookings.id',
                    'booking_status',
                    'users.name',
                    'users.email',
                    'trips.id',
                    'trips.trip_name',
                    'trips.price',
                    'bookings.status',
                    'bookings.booking_type',
                    'payments.payment_method',
                    'payments.nagad',
                    'payments.bkash',
                    'payments.card',
                    'transactions.transaction_reference',
                    'packages.name',
                    'trips.departure_time',
                    'trips.arrival_time',
                    'hotels.name'
                )
                ->get();


            if ($invoice->count() > 0) {
                return ["status" => true, "data" => $invoice, "message" => "Invoice retrieved successfully"];
            } else {
                return ["status" => true, "data" => [], "message" => "No Report found"];
            }
        } catch (Exception $ex) {
            Log::alert("BookingService - dailybookingReport function" . $ex->getMessage());
            return ["status" => false, "message" => "Internal Server Error."];
        }
    }
}
