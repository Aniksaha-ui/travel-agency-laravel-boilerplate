<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Repository\Services\RouteService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class RouteController extends Controller
{
    protected $routeService;
    public function __construct(RouteService $routeService)
    {
        $this->routeService = $routeService;
    }

    public function index(Request $request){
        $page = $request->query('page');
        $search = $request->query('search');
      
        $response = $this->routeService->index($page,$search);
        return response()->json([
            "data"=> $response,
            "message"=> "success"
        ],200);
    }

    public function insert(Request $request){
     try{
        $response = $this->routeService->store($request->all());
        if($response==true){
         return response()->json([
             'isExecute' => true,
             'data' => $response,
             'message' => 'New Route Created',
         ],200);
        }

        return response()->json([
            'isExecute' => true,
            'message' => 'New Route Cannot be Created'
        ],200);
        
     }catch(Exception $ex){
        Log::error($ex->getMessage());
     }
    }

    public function findRouteById($id){
        try{
            $response = $this->routeService->findById($id);
            if($response){
                return response()->json([
                    "isExecute"=> true,
                    "data"=> $response,
                    "message"=> "Find Single Route"
                ],200);
            }else{
                return response()->json([
                    "isExecute"=> true,
                    "message"=> "No Data Found"
                ],200);
            }

        }catch(Exception $ex){
            Log::error($ex->getMessage());
        }
    }


    public function delete($id){
        try{
            $response = $this->routeService->delete($id);
            if($response){
                return response()->json([
                    "isExecute"=> true,
                    "data"=> $response,
                    "message"=> "Route Deleted"
                ],200);
            }else{
                return response()->json([
                    "isExecute"=> true,
                    "message"=> "Data Can not be deleted"
                ],200);
            }
        }catch(Exception $ex){
            Log::error($ex->getMessage());
        }
    }

}
