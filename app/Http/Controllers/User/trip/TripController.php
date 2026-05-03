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
            return $this->successResponse($trips);
        } catch (Exception $ex) {
            Log::alert($ex->getMessage());
            return $this->failedResponse();
        }
    }

    public function singleTrip($id)
    {

        try {
            $trip = $this->tripService->findTripById($id);
            if ($trip) {
                return $this->successResponse($trip);
            }

            return $this->failedResponse("Trip not found", 404);
        } catch (Exception $ex) {
            Log::alert($ex->getMessage());
            return $this->failedResponse();
        }
    }
}
