<?php

namespace App\Repository\Services\Users;

use App\Repository\Interfaces\CommonInterface;
use App\route;
use Exception;
use Illuminate\Support\Facades\Log;
use DB;

class BookingService implements CommonInterface
{

    protected $contact;

    /**
     * Get all contacts.
     *
     * @return bool
     */
    public function index($page, $search) {}

    public function store($request) {}


    public function findById($id) {}

    public function delete($id) {}


    public function mybookings($userId)
    {
        try {
            $bookingInformation = DB::table('bookings')
                ->leftJoin('users', 'bookings.user_id', '=', 'users.id')
                ->leftJoin('trips', 'bookings.trip_id', '=', 'trips.id')
                ->leftJoin('packages', 'bookings.package_id', '=', 'packages.id')
                ->leftJoin('hotel_bookings', 'hotel_bookings.id', '=', 'bookings.hotel_booking_id')
                ->leftJoin('hotels', 'hotels.id', '=', 'hotel_bookings.hotel_id')
                ->select(
                    'bookings.*',
                    'trips.trip_name',
                    'users.name',
                    'users.email',
                    'packages.name as package_name',
                    'hotels.name as hotel_name'
                )
                ->where("bookings.user_id", $userId)
                ->get();

            Log::info("booking information" . json_encode($bookingInformation));
            return $bookingInformation;
        } catch (Exception $ex) {
            Log::alert($ex->getMessage());
        }
    }

    public function invoiceInfo($bookingId, $userId)
    {
        try {
            $invoiceInfo = DB::table('bookings')
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
                ->where('bookings.id', $bookingId)
                ->where('bookings.user_id', $userId)
                ->get();
            Log::info("invoice info" . json_encode($invoiceInfo));
            // Organize into a parent-child structure

            return $invoiceInfo;
        } catch (Exception $ex) {
            Log::alert($ex->getMessage());
        }
    }
}
