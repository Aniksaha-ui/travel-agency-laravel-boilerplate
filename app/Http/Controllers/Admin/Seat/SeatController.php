<?php

namespace App\Http\Controllers\Admin\Seat;

use App\Http\Controllers\Controller;
use App\Repository\Services\Seat\SeatService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SeatController extends Controller
{
    protected $seatService;
    public function __construct(SeatService $seatService)
    {
        $this->seatService = $seatService;
    }

    public function index(Request $request)
    {
        $page = $request->query('page');
        $search = $request->query('search');

        $response = $this->seatService->index($page, $search);
        return $this->successResponse($response);
    }

    public function insert(Request $request)
    {
        try {
            $response = $this->seatService->store($request->all());
            if ($response == true) {
                return $this->successResponse($response, 'New Vehicle Created');
            }

            return $this->failedResponse('New Vehicle Cannot be Created', 200);
        } catch (Exception $ex) {
            Log::error("SeatController insert: " . $ex->getMessage());
            return $this->failedResponse();
        }
    }

    public function findVehicleById($id)
    {
        try {
            $response = $this->seatService->findById($id);
            if ($response) {
                return $this->successResponse($response, 'Find Single Vehicle');
            }

            return $this->failedResponse('No Data Found', 200);
        } catch (Exception $ex) {
            Log::error("SeatController findVehicleById: " . $ex->getMessage());
            return $this->failedResponse();
        }
    }

    public function delete($id)
    {
        try {
            $response = $this->seatService->delete($id);
            if ($response) {
                return $this->successResponse($response, 'Vehicle Deleted');
            }

            return $this->failedResponse('Data Can not be deleted', 200);
        } catch (Exception $ex) {
            Log::error("SeatController delete: " . $ex->getMessage());
            return $this->failedResponse();
        }
    }
}
