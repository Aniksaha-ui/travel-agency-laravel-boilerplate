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
            Log::info("Trip inserted: ".$routeInsert);
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

    public function findAllActiveTrips($data){
        try{
            $query = DB::table('trips')
                    ->join('seat_availablities', 'trips.id', '=', 'seat_availablities.trip_id')
                    ->join('vehicles', 'trips.vehicle_id', '=', 'vehicles.id')
                    ->join('routes', 'trips.route_id', '=', 'routes.id')
                    ->select('trips.trip_name','trips.departure_time','trips.arrival_time','trips.price','vehicles.vehicle_name','vehicles.vehicle_type','routes.route_name', DB::raw('COUNT(seat_availablities.id) as available_seats'))
                    ->groupBy('trips.id'); 
            
            if (isset($data['trip_name']) && $data['trip_name']) {
                $query->where('trip_name', 'like', '%' . $data['trip_name'] . '%');
            }
            if (isset($data['start_date']) && $data['start_date']) {
                $query->where('departure_time', '<=', $data['start_date']);
            }
            if (isset($data['end_date']) && $data['end_date']) {
                $query->where('arrival_time', '>=', $data['end_date']);
            }

            $trips = $query->get();

            

            return $trips;
        }catch(Exception $ex){
            Log::alert($ex->getMessage());
        }
    }

}