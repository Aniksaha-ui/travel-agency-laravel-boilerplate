<?php

namespace App\Http\Controllers\User\booking;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use DB;
use Illuminate\Support\Str;


class bookingController extends Controller
{

    public function tripBooking(Request $request)
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'seatinfo' => 'required|array|min:1',
            'seatinfo.*.trip_id' => 'required|integer|exists:trips,id',
            'seatinfo.*.seat_id' => 'required|integer|exists:seats,id',
            'seatinfo.*.seat_number' => 'required|string|max:10',
            'seatinfo.*.vehicle_name' => 'required|string|max:255',
            'seatinfo.*.is_available' => 'required|boolean|in:1',
            'paymentinfo' => 'required|array',
            'paymentinfo.amount' => 'required|numeric|min:1',
            'paymentinfo.payment_method' => 'required|string|in:card,bkash,nagad,internet_banking',
            'paymentinfo.bkash' => 'nullable|string|max:50|required_if:paymentinfo.payment_method,bkash',
            'paymentinfo.nagad' => 'nullable|string|max:50|required_if:paymentinfo.payment_method,nagad',
            'paymentinfo.card' => 'nullable|string|max:50|required_if:paymentinfo.payment_method,card',
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

            $seatInfo = $request->input('seatinfo');
            $seatIds = array_column($seatInfo, "seat_id");
            $tripId = $seatInfo[0]['trip_id']; // Assuming all seats belong to the same trip
            $paymentInfo = $request->input('paymentinfo');

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
                "trip_id" => $tripId,
                "seat_ids" => implode(",", $seatIds),
                "status" => "payment init",
                "created_at" => now(),
                "updated_at" => now()
            ]);

            // Prepare data for booking_seats table
            $bookingSeats = [];
            foreach ($seatInfo as $seat) {
                $bookingSeats[] = [
                    'booking_id' => $lastBookingId,
                    'seat_id' => $seat['seat_id'],
                    'created_at' => now(),
                    'updated_at' => now()
                ];
            }

            // Bulk insert into booking_seats table
            DB::table('booking_seats')->insert($bookingSeats);

            // Insert into payments table
            $lastPaymentId = DB::table('payments')->insertGetId([
                "booking_id" => $lastBookingId,
                "amount" => $paymentInfo['amount'],
                "payment_method" => $paymentInfo['payment_method'],
                "bkash" => $paymentInfo['bkash'] ?? null,
                "nagad" => $paymentInfo['nagad'] ?? null,
                "card" => $paymentInfo['card'] ?? null,
                "created_at" => now(),
                "updated_at" => now()
            ]);

            // Generate a unique transaction reference
            $transactionRef = strtoupper(Str::random(10));

            // Insert into transactions table
            DB::table('transactions')->insert([
                "payment_id" => $lastPaymentId,
                "transaction_reference" => $transactionRef,
                "created_at" => now(),
                "updated_at" => now()
            ]);

            // Update seat availability
            DB::table('seat_availablities')
                ->where('trip_id', $tripId)
                ->whereIn('seat_id', $seatIds)
                ->update(['is_available' => 0]);

            DB::commit(); // Commit transaction

            return response()->json([
                'status' => 'success',
                'message' => 'Booking successfully created!',
                'data' => [
                    'booking_id' => $lastBookingId,
                    'trip_id' => $tripId,
                    'seats' => $seatInfo,
                    'payment_id' => $lastPaymentId,
                    'transaction_reference' => $transactionRef
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
