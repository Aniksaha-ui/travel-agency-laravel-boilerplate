<?php

namespace App\Http\Controllers\User\trip;

use App\Http\Controllers\Controller;
use App\Repository\Services\Packages\PackageService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use DB;
class PackageController extends Controller
{
    private $packageService;
    public function __construct(PackageService $packageService)
        {
            $this->packageService = $packageService;
        }
    public function getPackages()
    {
        try {
            $packageData = $this->packageService->index();
            return response()->json([
                'data' => $packageData,
                'message' => 'success'
            ], 200);
        } catch (Exception $exception) {
            Log::error('Error fetching packages: ' . $exception->getMessage());
            return response()->json([
                'message' => 'Failed to retrieve packages'
            ], 500);
        }
    }

    public function singlePackageInformation($packageId){
        Log::info($packageId);
        try{
            $packageData = $this->packageService->singlePackage($packageId);
            return response()->json([
                'data' => $packageData,
                'message' => 'success'
            ], 200);
        } catch (Exception $exception) {
            Log::error('Error fetching single package: ' . $exception->getMessage());
            return response()->json([
                'message' => 'Failed to retrieve single package'
            ], 500);
        }
    }


    public function packageBooking(Request $request){

        $request->validate([
            'user_id' => 'required|exists:users,id',
            'package_id' => 'required|exists:packages,id',
            'adults' => 'required|integer|min:1',
            'children' => 'required|integer|min:0'
        ]);
    
        DB::beginTransaction();
        try {
            $package = DB::table('packages')->where('id', $request->package_id)->first();
            $pricing = DB::table('price_packages')->where('package_id', $package->id)->first();
            $trip = DB::table('trips')->where('id', $package->trip_id)->first();
    
            $totalCost = ($pricing->adult_price * $request->adults) + ($pricing->child_price * $request->children);
    
            // 1. Insert into package_bookings
            $bookingId = DB::table('package_bookings')->insertGetId([
                'user_id' => $request->user_id,
                'package_id' => $package->id,
                'trip_id' => $trip->id,
                'total_adult' => $request->adults,
                'total_child' => $request->children,
                'total_cost' => $totalCost,
                'payment_status' => 'pending',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
    
            // 2. Insert passengers
            for ($i = 0; $i < $request->adults; $i++) {
                DB::table('package_booking_passengers')->insert([
                    'package_booking_id' => $bookingId,
                    'type' => 'adult',
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
    
            for ($i = 0; $i < $request->children; $i++) {
                DB::table('package_booking_passengers')->insert([
                    'package_booking_id' => $bookingId,
                    'type' => 'child',
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
    
            // 3. If includes bus, reserve seats
            if ($package->includes_bus == 1) {
                $totalSeatsNeeded = $request->adults + $request->children;
    
                $availableSeats = DB::table('seat_availablities')
                    ->where('trip_id', $trip->id)
                    ->where('is_available', 1)
                    ->limit($totalSeatsNeeded)
                    ->pluck('id');
    
                if ($availableSeats->count() < $totalSeatsNeeded) {
                    DB::rollBack();
                    return response()->json(['message' => 'Not enough seats available'], 400);
                }
    
                foreach ($availableSeats as $seatId) {
                    $bookingSeatId = DB::table('bookings')->insertGetId([
                        'user_id' => $request->user_id,
                        'trip_id' => $trip->id,
                        'status' => 'confirmed',
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
    
                    DB::table('booking_seats')->insert([
                        'booking_id' => $bookingSeatId,
                        'seat_id' => $seatId,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
    
                    DB::table('seat_availability')->where('id', $seatId)->update(['is_available' => 0]);
                }
            }
    
            DB::commit();
    
            return response()->json([
                'message' => 'Package and seats booked successfully',
                'package_booking_id' => $bookingId
            ], 201);
    
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Booking failed', 'error' => $e->getMessage()], 500);
        }


    }


}
