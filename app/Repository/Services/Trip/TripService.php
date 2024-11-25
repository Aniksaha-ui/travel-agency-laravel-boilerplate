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
            ->where('is_active',1)
            ->orWhere('trip_name', 'like', '%' . $search . '%')
            ->orWhere('price', 'like', '%' . $search . '%')
            ->paginate($perPage, ['trip_name','departure_time','arrival_time','price'], 'page', $page);
        
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