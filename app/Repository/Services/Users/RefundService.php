<?php

namespace App\Repository\Services\Users;

use Exception;
use Illuminate\Support\Facades\Log;

use GuzzleHttp\Psr7\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class RefundService
{

    protected $contact;

    public function getRefund($userId)
    {
        try {
            Log::info("RefundService getRefund method called");


            Log::info("RefundService user id: " . $userId);
            $refunds = DB::table('refunds')
                ->join('bookings', 'refunds.booking_id', '=', 'bookings.id')
                ->join('trips', 'bookings.trip_id', '=', 'trips.id')
                ->where('user_id', $userId)
                ->get();
            return ["status" => true, "data" => $refunds];
        } catch (Exception $ex) {
            Log::alert("RefundService" . $ex->getMessage());
            return ["status" => false, "message" => "An error occurred while fetching refunds."];
        }
    }
}
