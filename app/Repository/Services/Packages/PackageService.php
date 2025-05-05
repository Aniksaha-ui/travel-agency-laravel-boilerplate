<?php

namespace App\Repository\Services\Packages;

use Exception;
use Illuminate\Support\Facades\Log;
use DB;
use GuzzleHttp\Psr7\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB as FacadesDB;

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
                    ->select('trips.id as trip_id', 'route_id', 'vehicle_id', 'departure_time', 'arrival_time', 'route_name')
                    ->first();
                $packagesInformation[] = $package;
            }

            return $packagesInformation;
        } catch (Exception $ex) {
            Log::alert($ex->getMessage());
        }
    }


    public function singlePackage($packageId)
    {
        try {
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
                ->select('trips.id as trip_id', 'route_id', 'vehicle_id', 'departure_time', 'arrival_time', 'route_name')
                ->first();

            return $package;
        } catch (Exception $ex) {
            Log::alert($ex->getMessage());
        }
    }

    public function tripwisePackages($tripId)
    {
        try {
            $packages = DB::table('packages')
                ->join('trips', 'packages.trip_id', '=', 'trips.id')
                ->where('trip_id', $tripId)
                ->get();
            return $packages;
        } catch (Exception $ex) {
            Log::alert($ex->getMessage());
        }
    }

    public function getAllPackages($page, $search)
    {
        try {
            $perPage = 10;
            $routes = DB::table('packages')
                ->join('trips', 'packages.trip_id', '=', 'trips.id')
                ->where('trips.trip_name', 'like', '%' . $search . '%')
                ->orWhere('packages.name', 'like', '%' . $search . '%')
                ->paginate($perPage, ['packages.*', 'trips.trip_name'], 'page', $page);
            return $routes;
        } catch (Exception $ex) {
            Log::alert($ex->getMessage());
        }
    }

    public function store($data)
    {
        try {
            DB::beginTransaction();

            $packageId = DB::table('packages')->insertGetId([
                "name" => $data['name'],
                "trip_id" => $data['trip_id'],
                "includes_meal" => $data['includes_meal'],
                "includes_hotel" => $data['includes_hotel'],
                "includes_bus" => $data['includes_bus'],
                "description" => $data['description'],
                "image" => $data['image'],
                "created_at" => Carbon::now(),
                "updated_at" => Carbon::now()
            ]);
            if ($data['inclusions']) {

                foreach ($data['inclusions'] as $inclusion) {
                    DB::table('package_inclusions')->insert([
                        'package_id' => $packageId,
                        'item_name' => $inclusion
                    ]);
                }
            }
            if ($data['exclusions']) {
                foreach ($data['exclusions'] as $exclusion) {
                    DB::table('package_exclusions')->insert([
                        'package_id' => $packageId,
                        'item_name' => $exclusion
                    ]);
                }
            }
            if ($data['pricing']) {
                foreach ($data['pricing'] as $price) {
                    DB::table('price_packages')->insert([
                        'package_id' => $packageId,
                        'adult_price' => $price['adult_price'],
                        'child_price' => $price['child_price']
                    ]);
                }
            }
            DB::commit();
            return true;
        } catch (Exception $ex) {
            DB::rollBack();
            Log::alert($ex->getMessage());
            throw $ex;
        }
    }
}
