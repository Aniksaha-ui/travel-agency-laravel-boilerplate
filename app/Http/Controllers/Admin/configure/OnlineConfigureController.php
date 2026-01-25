<?php

namespace App\Http\Controllers\Admin\configure;

use App\Constants\ApiResponseStatus;
use App\Http\Controllers\Controller;
use App\Repository\Services\Configure\ConfigureService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class OnlineConfigureController extends Controller
{



    protected $configureService;
    public function __construct(ConfigureService $configureService)
    {
        $this->configureService = $configureService;
    }
    public function onlineConfigureList(Request $request){
        try{

            $page = $request->query('page');
            $search = $request->query('search');

            $response = $this->configureService->onlineConfigureList($page, $search);
            return response()->json([
                "isExecute" => $response['status'],
                "data" => $response['data'],
                "message" => $response['message']
            ], 200);
        } catch (Exception $ex) {
            Log::error("Router Controller - insert function" . $ex->getMessage());

            return response()->json([
                "status" => ApiResponseStatus::FAILED,
                "message" => "Internal Server Error"
            ], 200);
        }
    }




     public function storeNewConfigure(Request $request)
    {
        try {


        $validator = Validator::make($request->all(), [
            'payment_for'      => 'required|string|max:255',
            'online_payment' => 'required',
        ], [
            'payment_for.required' => 'Route name is required.',
            'online_payment.required'     => 'Origin is required.',
        ]);

        if ($validator->fails()) {
            Log::error("Validation error".$validator->errors()->first());
            return response()->json([
                'isExecute' => ApiResponseStatus::FAILED,
                'message'   => $validator->errors()->first(),
            ], 422);
            
        }


        $alreadyExist = DB::table('payment_method_config')->where('payment_for',$request->payment_for)->exists();
        if($alreadyExist){
            Log::error("already exist");
            return response()->json([
                'isExecute' => ApiResponseStatus::FAILED,
                'message'   =>"Configuration already exist. Please check your list",
            ], 422);

        }

            $response = $this->configureService->store($request->all());
            if ($response) {
                return response()->json([
                    'isExecute' => $response['status'],
                    'data' => $response['data'],
                    'message' => $response['message'],
                ], 200);
            }
        } catch (Exception $ex) {
            Log::error("Online configure Controller - storeNewConfigure function" . $ex->getMessage());
            return response()->json([
                'isExecute' => ApiResponseStatus::FAILED,
                'message' => config("message.server_error")
            ], 500);
        }
    }




     public function findConfigureById($id)
    {
        try {
            $response = $this->configureService->findConfigureById($id);
            if ($response) {
                return response()->json([
                    "isExecute" => $response['status'],
                    "data" => $response['data'],
                    "message" => $response['message']
                ], 200);
            }
        } catch (Exception $ex) {
            Log::error("OnlineConfigureController - findConfigureById function" . $ex->getMessage());
            return response()->json([
                'isExecute' => ApiResponseStatus::FAILED,
                'message' => config("message.server_error")
            ], 500);
        }
    }


    public function updateOnlineConfigure($data){
        try{

        $validator = Validator::make($request->all(), [
            "configuration_id" =>'required',
            'payment_for'      => 'required|string|max:255',
            'online_payment' => 'required',
        ], [
            'payment_for.required' => 'Route name is required.',
            'online_payment.required'     => 'Origin is required.',
        ]);

        if ($validator->fails()) {
            Log::error("Validation error".$validator->errors()->first());
            return response()->json([
                'isExecute' => ApiResponseStatus::FAILED,
                'message'   => $validator->errors()->first(),
            ], 422);
        }





        }catch(Exception $ex){
            Log::error("OnlineConfigureController - updateOnlineConfigure function" . $ex->getMessage());
            return response()->json([
                'isExecute' => ApiResponseStatus::FAILED,
                'message' => config("message.server_error")
            ], 500);
        }
    }



    


}
