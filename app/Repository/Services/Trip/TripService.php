<?php

namespace App\Repository\Services\Trip;

use App\Helpers\admin\FileManageHelper;
use App\Repository\Interfaces\CommonInterface;
use App\route;
use Exception;
use Illuminate\Support\Facades\Log;
use DB;

class TripService implements CommonInterface
{

    protected $contact;

    /**
     * Get all contacts.
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function index($page, $search)
    {

        try {
            $perPage = 10;
            $availableSeats = DB::table('trips')
                ->join('vehicles', 'trips.vehicle_id', '=', 'vehicles.id')
                ->join('routes', 'trips.route_id', '=', 'routes.id')
                ->where('is_active', 1)
                ->orWhere('trip_name', 'like', '%' . $search . '%')
                ->orWhere('price', 'like', '%' . $search . '%')
                ->paginate($perPage, ['trips.id', 'trip_name', 'departure_time', 'arrival_time', 'price', 'vehicle_name', 'route_name', 'is_active'], 'page', $page);

            return $availableSeats;
        } catch (Exception $ex) {
            Log::alert($ex->getMessage());
        }
    }

    public function store($request)
    {
        try {

            if (request()->hasFile('image')) {
                $documentLink = FileManageHelper::uploadFile('travel', $request['image']);
            } else {
                $request['image'] = 'images/trips/default.png';
            }
            $insertedData = [
                'trip_name' => $request['trip_name'],
                'departure_time' => $request['departure_time'],
                'arrival_time' => $request['arrival_time'],
                'price' => $request['price'],
                'description' => $request['description'] ?? '',
                'image' => $documentLink ?? $request['image'],
                'status' => 1,
                'vehicle_id' => $request['vehicle_id'],
                'route_id' => $request['route_id'],
                'is_active' => 1,
            ];
            $routeInsert = DB::table('trips')->insert($insertedData);
            Log::info("Trip inserted: " . $routeInsert);
            if ($routeInsert) {
                return true;
            }
            return false;
        } catch (Exception $ex) {
            Log::alert("Insert error: " . $ex->getMessage());
        }
    }



    public function update($request, $id)
    {
        try {

            if (request()->hasFile('image')) {
                $documentLink = FileManageHelper::uploadFile('travel', $request['image']);
            } else {
                $request['image'] = DB::table('trips')->where('id', $id)->value('image');
            }
            $updatedData = [
                'trip_name' => $request['trip_name'],
                'departure_time' => $request['departure_time'],
                'arrival_time' => $request['arrival_time'],
                'price' => $request['price'],
                'description' => $request['description'] ?? '',
                'image' => $documentLink ?? $request['image'],
                'status' => 1,
                'vehicle_id' => $request['vehicle_id'],
                'route_id' => $request['route_id'],
                'is_active' => 1,
            ];
            $routeInsert = DB::table('trips')->where('id', $id)->update($updatedData);
            Log::info("Trip updated: " . $routeInsert);
            if ($routeInsert) {
                return true;
            }
            return false;
        } catch (Exception $ex) {
            Log::alert("Update error: " . $ex->getMessage());
        }
    }


    public function findById($id)
    {
        try {
            $route = DB::table('trips')->where('id', $id)->first();
            return $route;
        } catch (Exception $ex) {
            Log::alert("Find By Id Error" . $ex->getMessage());
        }
    }

    public function delete($id)
    {
        try {
            $response = DB::table('seats')->where('id', $id)->delete();
            return $response;
        } catch (Exception $ex) {
            Log::alert("Delete Error" . $ex->getMessage());
        }
    }

    public function findAllActiveTrips($data)
    {
        try {
            $query = DB::table('trips')
                ->select('trips.id', 'trips.trip_name', 'trips.departure_time', 'trips.arrival_time', 'trips.price', 'trips.description', 'trips.image', 'trips.status');

            if (isset($data['trip_name']) && $data['trip_name']) {
                $query->where('trip_name', 'like', '%' . $data['trip_name'] . '%');
            }
            if (isset($data['start_date']) && $data['start_date']) {
                $query->where('departure_time', '<=', $data['start_date']);
            }
            if (isset($data['end_date']) && $data['end_date']) {
                $query->where('arrival_time', '>=', $data['end_date']);
            }

            $query->where('is_active', 1);
            $query->orderBy('id', 'desc');

            $trips = $query->get();

            return $trips;
        } catch (Exception $ex) {
            Log::alert($ex->getMessage());
        }
    }

    public function findTripById($id)
    {
        try {
            $tripInformation = DB::table('trips')->where('id', $id)->first();
            return $tripInformation;
        } catch (Exception $ex) {
            Log::alert("Find By Id Error" . $ex->getMessage());
        }
    }

    public function inactiveTripByTripId($tripId)
    {
        try {
            $trip = DB::table('trips')->where('id', $tripId)->first();
            if (!$trip) {
                return ["status" => false, "message" => "Trip not found"];
            }
            $inactiveTrip = DB::table('trips')->where('id', $tripId)->update(['is_active' => 0]);
            if ($inactiveTrip) {
                return ["status" => true, "message" => "Trip Inactive successfully"];
            } else {
                return ["status" => false, "message" => "Trip Inactive failed"];
            }
        } catch (Exception $ex) {
            Log::alert("Update error: " . $ex->getMessage());
        }
    }

    public function dropdown()
    {
        try {
            $trips = DB::table('trips')
                ->select('trips.id', 'trips.trip_name', 'trips.departure_time', 'trips.arrival_time', 'trips.price', 'trips.description', 'trips.image', 'trips.status')
                ->whereNotIn('trips.id', function ($query) {
                    $query->select('vehicle_trip_trackings.trip_id')->from('vehicle_trip_trackings');
                })
                ->get();


            return $trips;
        } catch (Exception $ex) {
            Log::alert("Dropdown error: " . $ex->getMessage());
        }
    }
}
