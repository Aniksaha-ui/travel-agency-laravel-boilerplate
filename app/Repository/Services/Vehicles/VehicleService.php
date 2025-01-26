<?php

namespace App\Repository\Services\Vehicles;
use App\Repository\Interfaces\CommonInterface;
use App\route;
use Exception;
use Illuminate\Support\Facades\Log;
use DB;

class VehicleService implements CommonInterface{

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
           $vehicles = DB::table('vehicles') 
                    ->join('routes', 'vehicles.route_id', '=', 'routes.id')
                    ->where('vehicle_type', 'like', '%' . $search . '%')
                    ->orWhere('vehicle_name', 'like', '%' . $search . '%')
                    ->orWhere('total_seats', 'like', '%' . $search . '%')
                    ->paginate($perPage, ['vehicles.id','vehicle_type','vehicle_name','total_seats','routes.route_name'], 'page', $page);
           return $vehicles;
        }catch(Exception $ex){
            Log::alert($ex->getMessage());
        }
    }

    public function store($request){
        try{
            $vehicleInsert = DB::table('vehicles')->insert($request);
            if($vehicleInsert){
                return true;
            }
            return false;
        }catch(Exception $ex){
            Log::alert("Insert error: ".$ex->getMessage());
        }
    }


    public function findById($id){
        try{
            $findVehicleById = DB::table('vehicles')->where('id',$id)->first();
            return $findVehicleById;
        }catch(Exception $ex){
            Log::alert("Find By Id Error".$ex->getMessage());
        }
    }

    public function delete($id){
        try{
            $response =DB::table('vehicles')->where('id',$id)->delete();
            return $response;
        }catch(Exception $ex){
            Log::alert("Delete Error".$ex->getMessage());
        }
    }

    public function dropdown(){
        try{
           $vehicles = DB::table('vehicles')->select('id','vehicle_name')->get();
           return $vehicles;
        }catch(Exception $ex){
            Log::alert($ex->getMessage());
        }
    }


    public function vehicleBooking($data){
        try{
            $tripId = $data['trip_id'];
            $vehicleId = $data['vehicle_id'];
            $seats = DB::table('seats')->where('vehicle_id',$vehicleId)->get();
            $insertedData = [];
            foreach ($seats as $seat) {
                $insertedData[] = [
                    'trip_id' => $tripId,
                    'seat_id' => $seat->id 
                ];
            }
            $vehicleInsert = DB::table('seat_availablities')->insert($insertedData);
            if($vehicleInsert){
                return true;
            }
            return false;
        }catch(Exception $ex){
            Log::alert($ex->getMessage());
        }
    }


    public function vehicleTripTrackings($data){
        try{
            $tripId = $data['trip_id'];
            $vehicleId = $data['vehicle_id'];
            $tripInformation  = DB::table('trips')->where('id',$tripId)->first();
            $trackingInformationToInsert = [
                'trip_id' => $tripId,
                'vehicle_id' => $vehicleId,
                'travel_start_date' => $tripInformation->departure_time,
                'travel_end_date' => $tripInformation->arrival_time
            ];

            Log::info($trackingInformationToInsert);
            $trackingInformationStore = DB::table('vehicle_trip_trackings')->insert($trackingInformationToInsert);
            Log::info($trackingInformationToInsert);

            if($trackingInformationStore){
                return true;
            }
            return false;
        }catch(Exception $ex){
            Log::alert($ex->getMessage());
        }
    }


}