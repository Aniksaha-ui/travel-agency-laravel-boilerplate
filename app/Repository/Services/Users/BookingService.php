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
            $bookingInformation = DB::table("bookings")
                ->join('trips', 'bookings.trip_id', "=", "trips.id")
                ->join('users', 'bookings.user_id', "=", "users.id")
                ->select("bookings.*", "trips.trip_name", "users.name", "users.email")
                ->where("user_id", $userId)
                ->get();
            return $bookingInformation;
        } catch (Exception $ex) {
            Log::alert($ex->getMessage());
        }
    }

    public function invoiceInfo($bookingId, $userId)
    {
        try {
            $invoiceInfo = DB::table('bookings')
                ->join('booking_seats', 'booking_seats.booking_id', '=', 'bookings.id')
                ->join('payments', 'payments.booking_id', '=', 'bookings.id')
                ->join('transactions', 'transactions.payment_id', '=', 'payments.id')
                ->join('trips', 'trips.id', '=', 'bookings.trip_id')
                ->join('users', 'users.id', '=', 'bookings.user_id')
                ->join('seats', 'seats.id', '=', 'booking_seats.seat_id')
                ->select(
                    'bookings.id as booking_id',
                    'users.name as user_name',
                    'users.email as user_email',
                    'trips.id as trip_id',
                    'trips.trip_name',
                    'trips.price',
                    'bookings.status as payment_status',
                    'booking_seats.id as seat_id',
                    'payments.payment_method',
                    'payments.nagad',
                    'seats.seat_number',
                    'payments.bkash',
                    'payments.card',
                    'transactions.transaction_reference',
                )
                ->where('bookings.id', $bookingId)
                ->where('bookings.user_id', $userId)
                ->get();

            // Organize into a parent-child structure
            $organizedData = [];

            foreach ($invoiceInfo as $row) {
                $bookingId = $row->booking_id;

                if (!isset($organizedData[$bookingId])) {
                    $organizedData[$bookingId] = [
                        'booking_id' => $bookingId,
                        'transaction_reference' => $row->transaction_reference,
                        'user' => [
                            'name' => $row->user_name,
                            'email' => $row->user_email,
                        ],
                        'trip' => [
                            'trip_id' => $row->trip_id,
                            'trip_name' => $row->trip_name,
                        ],
                        'payment' => [
                            'status' => $row->payment_status,
                            'method' => $row->payment_method,
                            'nagad' => $row->nagad,
                            'bkash' => $row->bkash,
                            'card' => $row->card,
                        ],
                        'seats' => [],
                    ];
                }

                // Add seat data under the booking
                $organizedData[$bookingId]['seats'][] = [
                    'seat_id' => $row->seat_id,
                    'seat_number' => $row->seat_number,
                    'price' => $row->price,
                ];
            }

            // Convert array values into an indexed array
            $organizedData = array_values($organizedData);

            // Output the structured data
            return $organizedData;
        } catch (Exception $ex) {
            Log::alert($ex->getMessage());
        }
    }
}
