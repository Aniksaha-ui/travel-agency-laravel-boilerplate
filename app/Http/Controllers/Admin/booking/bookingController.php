<?php

namespace App\Http\Controllers\Admin\booking;

use App\Http\Controllers\Controller;
use App\Repository\Services\Booking\BookingService;
use Illuminate\Http\Request;
use DB;
use Exception;
use Illuminate\Http\Client\Request as ClientRequest;
use Illuminate\Support\Facades\Log;

class bookingController extends Controller
{
    private $bookingService;
    public function __construct(BookingService $bookingService)
    {
        $this->bookingService = $bookingService;
    }
    public function index(Request $request)
    {
        try {
            $page = $request->query('page');
            $search = $request->query('search');

            $response = $this->bookingService->index($page, $search);
            return $this->successResponse($response);
        } catch (Exception $ex) {
            Log::alert("BookingController - index function" . $ex->getMessage());
            return $this->failedResponse();
        }
    }

    public function tripwiseBooking(Request $request)
    {
        try {
            $response = $this->bookingService->tripwiseBooking($request->all());
            return $this->successResponse($response);
        } catch (Exception $ex) {
            Log::alert("BookingController - tripwiseBooking function" . $ex->getMessage());
            return $this->failedResponse();
        }
    }

    public function dailybookingReport(Request $request)
    {
        try {
            Log::info($request->input('date'));
            $date = $request->input('date') ?? now()->toDateString();
            $response = $this->bookingService->dailybookingReport($date);
            return $this->successResponse($response);
        } catch (Exception $ex) {
            Log::alert("BookingController - dailybookingReport function" . $ex->getMessage());
            return $this->failedResponse();
        }
    }

    public function invoice(Request $request)
    {
        try {
            $bookingId = $request->bookingId;
            $response = $this->bookingService->invoice($bookingId);
            return $this->serviceResponse($response);
        } catch (Exception $ex) {
            Log::alert("BookingController - invoice function" . $ex->getMessage());
            return $this->failedResponse("Internal Server Error.");
        }
    }
}
