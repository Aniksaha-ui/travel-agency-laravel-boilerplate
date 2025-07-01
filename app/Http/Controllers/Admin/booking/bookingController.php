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
        $page = $request->query('page');
        $search = $request->query('search');

        $response = $this->bookingService->index($page, $search);
        return response()->json([
            "data" => $response,
            "message" => "success"
        ], 200);
    }

    public function tripwiseBooking(Request $request)
    {

        $response = $this->bookingService->tripwiseBooking($request->all());
        return response()->json([
            "data" => $response,
            "message" => "success"
        ], 200);
    }

    public function dailybookingReport(Request $request)
    {
        Log::info($request->input('date'));
        $date = $request->input('date') ?? now()->toDateString();
        $response = $this->bookingService->dailybookingReport($date);
        return response()->json([
            "data" => $response,
            "message" => "success"
        ], 200);
    }

    public function invoice(Request $request)
    {
        try {
            $bookingId = $request->bookingId;
            $response = $this->bookingService->invoice($bookingId);
            if ($response['status'] == true) {
                return response()->json([
                    "data" => $response['data'],
                    "message" => $response['message'],
                    "status" => $response['status']
                ]);
            } else {
                return response()->json([
                    "data" => [],
                    "message" => $response['message'],
                    "status" => $response['status']
                ]);
            }
        } catch (Exception $ex) {
            Log::alert("BookingController - invoice function" . $ex->getMessage());
            return ["status" => false, "message" => "Internal Server Error."];
        }
    }
}
