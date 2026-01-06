<?php

namespace App\Repository\Services\Packages;

use App\Helpers\admin\FileManageHelper;
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

            $packages = DB::table('packages')->join('trips','packages.trip_id','trips.id')->where('status',1)->get();
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
            Log::alert("Package Service - index function" . $ex->getMessage());
        }
    }


    public function singlePackage($packageId)
    {
        try {
            $package = DB::table('packages')->where('id', $packageId)->first();

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
                ->select('trips.id as trip_id', 'route_id', 'vehicle_id', 'departure_time', 'arrival_time', 'departure_at','arrival_at','route_name')
                ->first();

            Log::info("Package Service - response singlePackage function" . json_encode($package));

            return $package;
        } catch (Exception $ex) {
            Log::alert("Package Service - singlePackage function" . $ex->getMessage());
        }
    }

    public function tripwisePackages($tripId)
    {
        try {
            $packages = DB::table('packages')
                ->join('trips', 'packages.trip_id', '=', 'trips.id')
                ->where('trip_id', $tripId)
                ->select('packages.*', 'trips.trip_name')
                ->get();
            Log::info("Package Service - response singlePackage function" . json_encode($packages));

            return $packages;
        } catch (Exception $ex) {
            Log::alert("packageService - tripwisePackages function" . $ex->getMessage());
        }
    }

    public function getAllPackages($page, $search)
    {
        try {
            $perPage = 10;
            $packages = DB::table('packages')
                ->join('trips', 'packages.trip_id', '=', 'trips.id')
                ->where('trips.trip_name', 'like', '%' . $search . '%')
                ->orWhere('packages.name', 'like', '%' . $search . '%')
                ->paginate($perPage, ['packages.*', 'trips.trip_name'], 'page', $page);
            Log::info("Package Service - response singlePackage function" . json_encode($packages));

            return $packages;
        } catch (Exception $ex) {
            Log::alert("packageService - getAllPackages function" . $ex->getMessage());
        }
    }

    public function store($data)
    {
        try {
            DB::beginTransaction();
            if (!isset($data['image'])) {
                $documentLink = FileManageHelper::uploadFile('packages', $data['image']);
            } else {
                $data['image'] = 'images/trips/default.png';
            }


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
            if (isset($data['pricing']) && is_array($data['pricing'])) {
                foreach ($data['pricing'] as $price) {
                    // Make sure it's an array and has the necessary fields
                    if (isset($price['adult_price']) && isset($price['child_price'])) {
                        DB::table('price_packages')->insert([
                            'package_id' => $packageId,
                            'adult_price' => $price['adult_price'],
                            'child_price' => $price['child_price']
                        ]);
                    } else {
                        Log::alert("Invalid pricing data: " . json_encode($price));
                    }
                }
            }



            if ($data['guide_id']) {
                DB::table('guide_packages')->insert([
                    'package_id' => $packageId,
                    'guide_id' => $data['guide_id']
                ]);
            }

            DB::commit();
            return true;
        } catch (Exception $ex) {
            DB::rollBack();
            Log::alert("packageService - store function" . $ex->getMessage());
            throw $ex;
        }
    }
}
