<?php

namespace App\Http\Controllers\Admin\Trip;

use App\Http\Controllers\Controller;
use App\Repository\Services\Trip\TripService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TripController extends Controller
{
    protected $tripService;
    public function __construct(TripService $tripService)
    {
        $this->tripService = $tripService;
    }

    public function index(Request $request)
    {
        $page = $request->query('page');
        $search = $request->query('search');

        $response = $this->tripService->index($page, $search);
        return response()->json([
            "data" => $response,
            "message" => "success"
        ], 200);
    }

    public function insert(Request $request)
    {
        try {
            Log::info($request->all());
            $response = $this->tripService->store($request->all());
            if ($response == true) {
                return response()->json([
                    'isExecute' => true,
                    'data' => $response,
                    'message' => 'New Trip Created',
                ], 200);
            }

            return response()->json([
                'isExecute' => true,
                'message' => 'New Trip Cannot be Created'
            ], 200);
        } catch (Exception $ex) {
            Log::error($ex->getMessage());
        }
    }


    public function update(Request $request, $id)
    {
        try {
            Log::info($request->all());
            $response = $this->tripService->update($request->all(), $id);
            if ($response) {
                return response()->json([
                    "isExecute" => true,
                    "data" => $response,
                    "message" => "Trip Updated"
                ], 200);
            } else {
                return response()->json([
                    "isExecute" => true,
                    "message" => "Data Can not be Updated"
                ], 200);
            }
        } catch (Exception $ex) {
            Log::error($ex->getMessage());
        }
    }


    public function findTripById($id)
    {
        try {
            $response = $this->tripService->findById($id);
            if ($response) {
                return response()->json([
                    "isExecute" => true,
                    "data" => $response,
                    "message" => "Find Single Trip"
                ], 200);
            } else {
                return response()->json([
                    "isExecute" => true,
                    "message" => "No Data Found"
                ], 200);
            }
        } catch (Exception $ex) {
            Log::error($ex->getMessage());
        }
    }


    public function delete($id)
    {
        try {
            $response = $this->tripService->delete($id);
            if ($response) {
                return response()->json([
                    "isExecute" => true,
                    "data" => $response,
                    "message" => "Trip Deleted"
                ], 200);
            } else {
                return response()->json([
                    "isExecute" => true,
                    "message" => "Data Can not be deleted"
                ], 200);
            }
        } catch (Exception $ex) {
            Log::error($ex->getMessage());
        }
    }


    public function inactiveTripByTripId($tripId)
    {
        try {

            Log::info($tripId);
            $response = $this->tripService->inactiveTripByTripId($tripId);
            if ($response) {
                return response()->json([
                    "isExecute" => true,
                    "data" => $response,
                    "message" => "Trip Updated"
                ], 200);
            } else {
                return response()->json([
                    "isExecute" => true,
                    "message" => "Data Can not be Updated"
                ], 200);
            }
        } catch (Exception $ex) {
            Log::error($ex->getMessage());
        }
    }

    public function dropdown()
    {
        try {
            $response = $this->tripService->dropdown();
            if ($response) {
                return response()->json([
                    "isExecute" => true,
                    "data" => $response,
                    "message" => "Trip Dropdown"
                ], 200);
            } else {
                return response()->json([
                    "isExecute" => true,
                    "message" => "No Data Found"
                ], 200);
            }
        } catch (Exception $ex) {
            Log::error($ex->getMessage());
        }
    }
}
