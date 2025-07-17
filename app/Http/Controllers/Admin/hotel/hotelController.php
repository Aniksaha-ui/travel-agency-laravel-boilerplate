<?php

namespace App\Http\Controllers\Admin\hotel;

use App\Http\Controllers\Controller;
use App\Repository\Services\Hotel\HotelService;
use Illuminate\Http\Request;
use DB;

class hotelController extends Controller
{

    protected $hotelService;

    public function __construct(HotelService $hotelService)
    {
        $this->hotelService = $hotelService;
    }

    public function store(Request $request)
    {


        try {
            $response = $this->hotelService->store($request->all());
            if ($response['status'] == true) {
                return response()->json([
                    'isExecute' => true,
                    'data' => [],
                    'message' => 'New Hotel Created',
                ], 200);
            } else {
                return response()->json([
                    'isExecute' => false,
                    'data' => [],
                    'message' => $response['message'],
                ], 200);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to create hotel', 'message' => $e->getMessage()], 500);
        }
    }
}
