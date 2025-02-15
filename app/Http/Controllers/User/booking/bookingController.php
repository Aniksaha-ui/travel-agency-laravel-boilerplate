<?php

namespace App\Http\Controllers\User\booking;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use DB;

class bookingController extends Controller
{
    public function tripBooking(Request $request)
    {
        // Validation rules
        $validator = Validator::make($request->all(), [
            '*.trip_id' => 'required|integer|exists:trips,id',
            '*.seat_id' => 'required|integer|exists:seats,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            DB::beginTransaction(); // Start transaction

            $bookingInfo = $request->all();
            $seatIds = array_column($bookingInfo, "seat_id");
            $seatIdsStr = implode(",", $seatIds);

            // Check if any of the requested seats are already booked
            $existingSeats = DB::table('booking_seats')
                ->whereIn('seat_id', $seatIds)
                ->exists();

            if ($existingSeats) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'One or more selected seats are already booked.',
                ], 409);
            }

            // Insert into bookings table
            $lastBookingId = DB::table('bookings')->insertGetId([
                "user_id" => $request->user()->id,
                "trip_id" => $bookingInfo[0]['trip_id'],
                "seat_ids" => $seatIdsStr,
                "status" => "payment init",
                "created_at" => now(),
                "updated_at" => now()
            ]);

            // Prepare data for booking_seats table
            $bookingSeats = [];
            foreach ($seatIds as $seatId) {
                $bookingSeats[] = [
                    'booking_id' => $lastBookingId,
                    'seat_id' => $seatId,
                    'created_at' => now(),
                    'updated_at' => now()
                ];
            }

            // Bulk insert into booking_seats table
            DB::table('booking_seats')->insert($bookingSeats);

            DB::commit(); // Commit transaction

            return response()->json([
                'status' => 'success',
                'message' => 'Booking successfully created!',
                'data' => [
                    'booking_id' => $lastBookingId,
                    'trip_id' => $bookingInfo[0]['trip_id'],
                    'seat_ids' => $seatIds
                ]
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack(); // Rollback transaction on error
            return response()->json([
                'status' => 'error',
                'message' => 'Something went wrong!',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
