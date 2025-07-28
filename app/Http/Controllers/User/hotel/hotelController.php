<?php

namespace App\Http\Controllers\User\hotel;

use App\Http\Controllers\Controller;
use App\Repository\Services\Hotel\HotelService;
use App\Repository\Services\Users\HotelService as UsersHotelService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class hotelController extends Controller
{
    private $hotelService;

    public function __construct(UsersHotelService $hotelService)
    {
        $this->hotelService = $hotelService;
    }

    public function index(Request $request)
    {
        try {
            $perPage = 10;
            $hotelName = $request->hotel_name ?? null;
            $city = $request->city ?? null;
            $country = $request->country ?? null;
            $guests = $request->guests ?? null;
            $response = $this->hotelService->index($hotelName, $city, $country, $guests);
            if ($response['status'] == true) {
                return response()->json([
                    'isExecute' => true,
                    'data' => $response['data'],
                    'message' => $response['message'],
                ], 200);
            } else {
                return response()->json([
                    'isExecute' => false,
                    'data' => [],
                    'message' => $response['message'],
                ], 200);
            }
        } catch (Exception $ex) {
            Log::info("hotelController(users)" . $ex->getMessage());
            return response()->json(['error' => 'Failed to create hotel', 'message' => $ex->getMessage()], 500);
        }
    }

    public function hotelBookings(Request $request)
    {
        try {
            $response = $this->hotelService->hotelBookings($request->all());
            if ($response['status'] == true) {
                return response()->json([
                    'isExecute' => true,
                    'data' => $response['data'],
                    'message' => $response['message'],
                ], 200);
            } else {
                return response()->json([
                    'isExecute' => false,
                    'data' => [],
                    'message' => $response['message'],
                ], 200);
            }
        } catch (Exception $ex) {
            Log::info("hotelController(users)" . $ex->getMessage());
            return response()->json(['error' => 'Failed to booking hotel', 'message' => $ex->getMessage()], 500);
        }
    }


    public function hotelCheckinStatusUpdate(Request $request)
    {
        try {
            $hotel_booking_id = $request->hotel_booking_id;
            $status = $request->status;
            $response = $this->hotelService->hotelCheckinStatusUpdate($hotel_booking_id, $status);
            if ($response['status'] == true) {
                return response()->json([
                    'isExecute' => true,
                    'data' => [],
                    'message' => $response['message'],
                ], 200);
            } else {
                return response()->json([
                    'isExecute' => false,
                    'data' => [],
                    'message' => $response['message'],
                ], 200);
            }
        } catch (Exception $ex) {
            Log::error($ex->getMessage());
            return response()->json(['error' => 'Failed to change checkin status', 'message' => $ex->getMessage()], 500);
        }
    }
}
