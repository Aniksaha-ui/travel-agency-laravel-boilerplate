<?php

namespace App\Repository\Services\Seat;

use App\Repository\Interfaces\CommonInterface;
use App\route;
use Exception;
use Illuminate\Support\Facades\Log;
use DB;

class SeatService implements CommonInterface{

    protected $contact;

    /**
     * Get all contacts.
     *
     * @return bool
     */
    public function index($page,$search)
    {

        try{
            $perPage = 10;
           $routes = DB::table('seats') 
                    ->join('vehicles', 'seats.vehicle_id', '=', 'vehicles.id')
                    ->where('seat_number', 'like', '%' . $search . '%')
                    ->orWhere('seat_class', 'like', '%' . $search . '%')
                    ->orWhere('seat_type', 'like', '%' . $search . '%')
                    ->orWhere('vehicle_name', 'like', '%' . $search . '%')
                    ->paginate($perPage, ['vehicle_name','seat_number','seat_class','seat_type'], 'page', $page);
           return $routes;
        }catch(Exception $ex){
            Log::alert($ex->getMessage());
        }
    }

    public function store($request){
        try{
            $routeInsert = DB::table('seats')->insert($request);
            if($routeInsert){
                return true;
            }
            return false;
        }catch(Exception $ex){
            Log::alert("Insert error: ".$ex->getMessage());
        }
    }


    public function findById($id){
        try{
            $route = DB::table('seats')->where('id',$id)->first();
            return $route;
        }catch(Exception $ex){
            Log::alert("Find By Id Error".$ex->getMessage());
        }
    }

    public function delete($id){
        try{
            $response =DB::table('seats')->where('id',$id)->delete();
            return $response;
        }catch(Exception $ex){
            Log::alert("Delete Error".$ex->getMessage());
        }
    }


}

