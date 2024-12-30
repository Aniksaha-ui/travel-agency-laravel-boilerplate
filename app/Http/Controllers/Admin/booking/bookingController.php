<?php

namespace App\Http\Controllers\Admin\booking;

use App\Http\Controllers\Controller;
use App\Repository\Services\Booking\BookingService;
use Illuminate\Http\Request;
use DB;
class bookingController extends Controller
{
    private $bookingService;
    public function __construct(BookingService $bookingService)
    {
        $this->bookingService = $bookingService;
    }
    public function index(Request $request){
        $page = $request->query('page');
        $search = $request->query('search');
      
        $response = $this->bookingService->index($page,$search);
        return response()->json([
            "data"=> $response,
            "message"=> "success"
        ],200);
    }

    public function tripwiseBooking(Request $request){
       
        $response = $this->bookingService->tripwiseBooking($request->all());
        return response()->json([
            "data"=> $response,
            "message"=> "success"
        ],200);
    }

    public function dailybookingReport(Request $request){
     
        $date = $request->input('date') ?? now()->toDateString(); 
        $response = $this->bookingService->dailybookingReport($date);
        return response()->json([
            "data"=> $response,
            "message"=> "success"
        ],200);
    }
}
