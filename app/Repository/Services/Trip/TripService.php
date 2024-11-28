<?php

namespace App\Repository\Services\Trip;

use App\Repository\Interfaces\CommonInterface;
use App\route;
use Exception;
use Illuminate\Support\Facades\Log;
use DB;

class TripService implements CommonInterface{

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
            $availableSeats = DB::table('trips')
            ->join('vehicles','trips.vehicle_id','=','vehicles.id')
            ->join('routes','trips.route_id','=','routes.id')
            ->where('is_active',1)
            ->orWhere('trip_name', 'like', '%' . $search . '%')
            ->orWhere('price', 'like', '%' . $search . '%')
            ->paginate($perPage, ['trips.id','trip_name','departure_time','arrival_time','price','vehicle_name','route_name'], 'page', $page);
        
           return $availableSeats;
        }catch(Exception $ex){
            Log::alert($ex->getMessage());
        }
    }

    public function store($request){
        try{
            $routeInsert = DB::table('trips')->insert($request);
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
            $route = DB::table('trips')->where('id',$id)->first();
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