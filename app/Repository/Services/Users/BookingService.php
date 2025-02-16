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
                ->select("bookings.*", "trips.trip_name")
                ->where("user_id", $userId)
                ->get();
            return $bookingInformation;
        } catch (Exception $ex) {
            Log::alert($ex->getMessage());
        }
    }
}
