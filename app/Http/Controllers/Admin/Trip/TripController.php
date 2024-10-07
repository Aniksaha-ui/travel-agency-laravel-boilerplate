<?php

namespace App\Http\Controllers\Admin\Trip;

use App\Http\Controllers\Controller;
use App\Repository\Services\Trip\TripService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TripController extends Controller
{
    protected $tripService;
    public function __construct(TripService $tripService)
    {
        $this->tripService = $tripService;
    }

    public function index(Request $request){
        $page = $request->query('page');
        $search = $request->query('search');
      
        $response = $this->tripService->index($page,$search);
        return response()->json([
            "data"=> $response,
            "message"=> "success"
        ],200);
    }

    public function insert(Request $request){
        try{
           $response = $this->tripService->store($request->all());
           if($response==true){
            return response()->json([
                'isExecute' => true,
                'data' => $response,
                'message' => 'New Vehicle Created',
            ],200);
           }
   
           return response()->json([
               'isExecute' => true,
               'message' => 'New Vehicle Cannot be Created'
           ],200);
           
        }catch(Exception $ex){
           Log::error($ex->getMessage());
        }
       }
   
       public function findVehicleById($id){
           try{
               $response = $this->tripService->findById($id);
               if($response){
                   return response()->json([
                       "isExecute"=> true,
                       "data"=> $response,
                       "message"=> "Find Single Vehicle"
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
               $response = $this->tripService->delete($id);
               if($response){
                   return response()->json([
                       "isExecute"=> true,
                       "data"=> $response,
                       "message"=> "Vehicle Deleted"
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