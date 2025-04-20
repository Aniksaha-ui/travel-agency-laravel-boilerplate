<?php

namespace App\Http\Controllers\User\trip;

use App\Http\Controllers\Controller;
use App\Repository\Services\Trip\TripService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class tripController extends Controller
{


    protected $tripService;
    public function __construct(TripService $tripService)
    {
        $this->tripService = $tripService;
    }
    public function index(Request $request)
    {
        Log::info("request : " . json_encode($request->all()));
        try {
            
            $trips = $this->tripService->findAllActiveTrips($request->all());
            return response()->json([
                "data" => $trips,
                "message" => "success"
            ], 200);
        } catch (Exception $ex) {
            Log::alert($ex->getMessage());
        }
    }

    public function singleTrip($id)
    {

        try {
            $trip = $this->tripService->findTripById($id);
            return response()->json([
                "data" => $trip,
                "message" => "success"
            ], 200);
        } catch (Exception $ex) {
            Log::alert($ex->getMessage());
        }
    }
}
