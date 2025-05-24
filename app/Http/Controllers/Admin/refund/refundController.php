<?php

namespace App\Http\Controllers\Admin\refund;

use App\Http\Controllers\Controller;
use App\Repository\Services\Refund\RefundService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class refundController extends Controller
{
    protected $refundService;
    public function __construct(RefundService $seatService)
    {
        $this->refundService = $seatService;
    }
    public function getRefunds(Request $request){
        $page = $request->query('page');
        $search = $request->query('search');
        try{
            $response = $this->refundService->getAllRefunds($page,$search);
            return response()->json([
                "data" => $response['data'],
                "message" => $response['message'],
                "status" => $response['status']
            ]);
         
        } catch(Exception $er){
            return response()->json([
                "data" => [],
                "message" => "Internal Server Error",
                "status" => false
            ]);
        }
    }

    public function disburseRefund(Request $request){
        Log::info("refundController disburseRefund" . json_encode($request->all()));
        try{
            $response = $this->refundService->disburseRefund($request->all());     return response()->json([
                "data" => $response['data'],
                "message" => $response['message'],
                "status" => $response['status']
            ]);
        }catch(Exception $ex){
            return response()->json([
                "data" => [],
                "message" => "Internal Server Error",
                "status" => false
            ]);
        }
    
    }

}
