<?php

namespace App\Http\Controllers\Admin\Vehicles;

use App\Http\Controllers\Controller;
use App\Repository\Services\Vehicles\VehicleService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class VehiclesController extends Controller
{
    protected $vehicleService;
    public function __construct(VehicleService $vehicleService)
    {
        $this->vehicleService = $vehicleService;
    }

    public function index(Request $request)
    {
        try {
            $page = $request->query('page');
            $search = $request->query('search');

            $response = $this->vehicleService->index($page, $search);
            return $this->successResponse($response);
        } catch (Exception $ex) {
            Log::error($ex->getMessage());
            return $this->failedResponse();
        }
    }

    public function insert(Request $request)
    {
        try {
            $response = $this->vehicleService->store($request->all());
            if ($response == true) {
                return $this->successResponse($response, 'New Vehicle Created');
            }
        } catch (Exception $ex) {
            Log::error($ex->getMessage());
            return $this->failedResponse();
        }

        return $this->failedResponse('Vehicle Cannot be Created', 200);
    }

    public function findVehicleById($id)
    {
        try {
            $response = $this->vehicleService->findById($id);
            if ($response) {
                return $this->successResponse($response, "Find Single Vehicle");
            }
        } catch (Exception $ex) {
            Log::error($ex->getMessage());
            return $this->failedResponse();
        }

        return $this->failedResponse("No Data Found", 200);
    }


    public function delete($id)
    {
        try {
            $response = $this->vehicleService->delete($id);
            if ($response) {
                return $this->successResponse($response, "Vehicle Deleted");
            }
        } catch (Exception $ex) {
            Log::error($ex->getMessage());
            return $this->failedResponse();
        }

        return $this->failedResponse("Data Can not be deleted", 200);
    }

    public function dropdown()
    {
        try {
            $response = $this->vehicleService->dropdown();
            return $this->successResponse($response);
        } catch (Exception $ex) {
            Log::error($ex->getMessage());
            return $this->failedResponse();
        }
    }

    public function vehicleBooking(Request $request)
    {
        try {
            $trackingResponse = false;
            $response = $this->vehicleService->vehicleBooking($request->all());
            if (($response['status'] ?? false) == true) {
                $trackingResponse  = $this->vehicleService->vehicleTripTrackings($request->all());
            }

            if (($response['status'] ?? false) == true && $trackingResponse == true) {
                return $this->successResponse($response, $response['message'] ?? 'Vehicle booked successfully.');
            }

            return $this->failedResponse($response['message'] ?? 'Vehicle booking failed.', 200, $response);
        } catch (Exception $ex) {
            Log::error($ex->getMessage());
            return $this->failedResponse();
        }
    }
}
