<?php

namespace App\Repository\Services\Packages;

use Exception;
use Illuminate\Support\Facades\Log;
use DB;
use GuzzleHttp\Psr7\Request;
use Carbon\Carbon;

class PackageService
{

    protected $contact;

    public function index()
    {
        try {

            $packages = DB::table('packages')->get();
            $packagesInformation = [];
            foreach ($packages as $package) {
                $package->inclusions = DB::table('package_inclusions')
                    ->where('package_id', $package->id)
                    ->pluck('item_name');

                $package->exclusions = DB::table('package_exclusions')
                    ->where('package_id', $package->id)
                    ->pluck('item_name');
                $package->pricing = DB::table('price_packages')
                    ->where('package_id', $package->id)
                    ->select('adult_price', 'child_price')
                    ->get();
                $package->trip = DB::table('trips')
                    ->join('routes', 'trips.route_id', '=', 'routes.id')
                    ->where('trips.id', $package->trip_id)
                    ->select('trips.id as trip_id', 'route_id', 'vehicle_id', 'departure_time', 'arrival_time','route_name')
                    ->first();
                $packagesInformation [] = $package;
            }

        return $packagesInformation;
           
        } catch (Exception $ex) {
            Log::alert($ex->getMessage());
        }
    }


    public function singlePackage($packageId){
        try{    
            $package = DB::table('packages')->first();

        if (!$package) {
            return response()->json([
                'status' => false,
                'message' => 'Package not found',
            ], 404);
        }

        $package->inclusions = DB::table('package_inclusions')
            ->where('package_id', $package->id)
            ->pluck('item_name');

        $package->exclusions = DB::table('package_exclusions')
            ->where('package_id', $package->id)
            ->pluck('item_name');

        $package->pricing = DB::table('price_packages')
            ->where('package_id', $package->id)
            ->select('adult_price', 'child_price')
            ->get();

        $package->trip = DB::table('trips')
            ->join('routes', 'trips.route_id', '=', 'routes.id')
            ->where('trips.id', $package->trip_id)
            ->select('trips.id as trip_id', 'route_id', 'vehicle_id', 'departure_time', 'arrival_time','route_name')
            ->first();

        return $package;

        } catch (Exception $ex) {
            Log::alert($ex->getMessage());
        }
    }

}
