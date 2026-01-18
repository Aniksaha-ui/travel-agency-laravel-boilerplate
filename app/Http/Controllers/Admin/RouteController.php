<?php

namespace App\Http\Controllers\Admin;

use App\Constants\ApiResponseStatus;
use App\Http\Controllers\Controller;
use App\Repository\Services\RouteService;
use Exception;
use Facade\FlareClient\Api;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class RouteController extends Controller
{
    protected $routeService;
    public function __construct(RouteService $routeService)
    {
        $this->routeService = $routeService;
    }

    public function index(Request $request)
    {

        try {
            $page = $request->query('page');
            $search = $request->query('search');

            $response = $this->routeService->index($page, $search);
            return response()->json([
                "isExecute" => $response['status'],
                "data" => $response['data'],
                "message" => $response['message']
            ], 200);
        } catch (Exception $ex) {
            return response()->json([
                "status" => ApiResponseStatus::FAILED,
                "message" => "Internal Server Error"
            ], 200);
        }
    }

    public function insert(Request $request)
    {
        try {


        $validator = Validator::make($request->all(), [
            'origin'      => 'required|string|max:255',
            'destination' => 'required|string|max:255',
            'route_name'  => 'required|string|max:255',
        ], [
            'route_name.required' => 'Route name is required.',
            'origin.required'     => 'Origin is required.',
            'origin.email'        => 'Origin must be a valid email.',
            'destination.required'=> 'Destination is required.',
        ]);

        if ($validator->fails()) {
            Log::error("Validation error".$validator->errors()->first());
            return response()->json([
                'isExecute' => ApiResponseStatus::FAILED,
                'message'   => $validator->errors()->first(),
            ], 422);
        }
            $response = $this->routeService->store($request->all());
            if ($response) {
                return response()->json([
                    'isExecute' => $response['status'],
                    'data' => $response['data'],
                    'message' => $response['message'],
                ], 200);
            }
        } catch (Exception $ex) {
            Log::error("Router Controller - insert function" . $ex->getMessage());
            return response()->json([
                'isExecute' => ApiResponseStatus::FAILED,
                'message' => config("message.server_error")
            ], 500);
        }
    }

    public function findRouteById($id)
    {
        try {
            $response = $this->routeService->findById($id);
            if ($response) {
                return response()->json([
                    "isExecute" => $response['status'],
                    "data" => $response['data'],
                    "message" => $response['message']
                ], 200);
            }
        } catch (Exception $ex) {
            Log::error("Router Controller - findRouteById function" . $ex->getMessage());
            return response()->json([
                'isExecute' => ApiResponseStatus::FAILED,
                'message' => config("message.server_error")
            ], 500);
        }
    }


    public function delete($id)
    {
        try {
            $response = $this->routeService->delete($id);
            if ($response) {
                return response()->json([
                    "isExecute" => $response['status'],
                    "data" => $response['data'],
                    "message" => $response['message']
                ], 200);
            }
        } catch (Exception $ex) {
            Log::error("Router Controller - findRouteById function" . $ex->getMessage());
            return response()->json([
                'isExecute' => ApiResponseStatus::FAILED,
                'message' => config("message.server_error")
            ], 500);
        }
    }


    public function dropdown()
    {
        try{
            $response = $this->routeService->dropdownList();
            return response()->json([
                "data" => $response,
                "message" => "success"
            ], 200);

        }catch(Exception $ex){
            Log::error("Route Controller - dropdown function" . $ex->getMessage());
            return response()->json([
                'isExecute' => ApiResponseStatus::FAILED,
                'message' => config("message.server_error")
            ], 500);
        }

     
    }
}
