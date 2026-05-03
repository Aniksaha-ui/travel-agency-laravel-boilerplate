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
        try {
            $page = $request->query('page');
            $search = $request->query('search');

            $response = $this->tripService->index($page, $search);
            return $this->successResponse($response);
        } catch (Exception $ex) {
            Log::error($ex->getMessage());
            return $this->failedResponse();
        }
    }

    public function insert(Request $request)
    {
        try {
            Log::info($request->all());
            $response = $this->tripService->store($request->all());
            Log::info('orginal res ' . json_encode($response));
            return $this->apiResponse(
                $this->normalizeExecutionStatus($response['isExecute'] ?? false),
                $response['data'] ?? [],
                $response['message'] ?? 'Trip not inserted'
            );
        } catch (Exception $ex) {
            Log::error($ex->getMessage());
            return $this->failedResponse();
        }
    }


    public function update(Request $request, $id)
    {
        try {
            Log::info($request->all());
            $response = $this->tripService->update($request->all(), $id);
            if ($response) {
                return $this->successResponse($response, "Trip Updated");
            }
            return $this->failedResponse("Data Can not be Updated", 200);
        } catch (Exception $ex) {
            Log::error($ex->getMessage());
            return $this->failedResponse();
        }
    }


    public function findTripById($id)
    {
        try {
            $response = $this->tripService->findById($id);
            if ($response) {
                return $this->successResponse($response, "Find Single Trip");
            }
            return $this->failedResponse("No Data Found", 200);
        } catch (Exception $ex) {
            Log::error($ex->getMessage());
            return $this->failedResponse();
        }
    }


    public function delete($id)
    {
        try {
            $response = $this->tripService->delete($id);
            if ($response) {
                return $this->successResponse($response, "Trip Deleted");
            }
            return $this->failedResponse("Data Can not be deleted", 200);
        } catch (Exception $ex) {
            Log::error($ex->getMessage());
            return $this->failedResponse();
        }
    }


    public function inactiveTripByTripId($tripId)
    {
        try {

            Log::info($tripId);
            $response = $this->tripService->inactiveTripByTripId($tripId);
            if (is_array($response)) {
                return $this->serviceResponse([
                    'status' => $response['status'] ?? false,
                    'data' => $response['data'] ?? [],
                    'message' => $response['message'] ?? 'Trip Updated',
                ]);
            }
            return $this->failedResponse("Data Can not be Updated", 200);
        } catch (Exception $ex) {
            Log::error($ex->getMessage());
            return $this->failedResponse();
        }
    }

    public function dropdown()
    {
        try {
            $response = $this->tripService->dropdown();
            if ($response) {
                return $this->successResponse($response, "Trip Dropdown");
            }
            return $this->failedResponse("No Data Found", 200);
        } catch (Exception $ex) {
            Log::error($ex->getMessage());
            return $this->failedResponse();
        }
    }


    public function singleTrip($id)
    {
        try {
            $response = $this->tripService->singleTrip($id);
            if ($response) {
                return $this->successResponse($response, "Single Trip");
            }
            return $this->failedResponse("No Data Found", 200);
        } catch (Exception $ex) {
            Log::error($ex->getMessage());
            return $this->failedResponse();
        }
    }
}
