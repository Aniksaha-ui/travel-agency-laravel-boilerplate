<?php

namespace App\Http\Controllers\User\booking;

use App\Http\Controllers\Controller;
use App\Repository\Services\Users\BookingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use DB;
use Illuminate\Support\Str;


class bookingController extends Controller
{
    private $bookingService;

    public function __construct(BookingService $bookingService)
    {
        $this->bookingService = $bookingService;
    }

    public function tripBooking(Request $request)
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'seatinfo' => 'required|array|min:1',
            'seatinfo.*.trip_id' => 'required|integer|exists:trips,id',
            'seatinfo.*.seat_id' => 'required|integer|exists:seats,id',
            'seatinfo.*.seat_number' => 'required|string|max:10',
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
                ->join('bookings', 'booking_seats.booking_id', '=', 'bookings.id')
                ->whereIn('seat_id', $seatIds)
                ->where('trip_id', $tripId)
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
                'booking_type' => 'trip',
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


            Log::info($paymentInfo['payment_method']);
            Log::info($paymentInfo['amount']);
            DB::table('company_accounts')->where('type', $paymentInfo['payment_method'])->increment('amount', $paymentInfo['amount']);
            DB::table('account_history')->insert([
                'user_id' => $request->user()->id,
                'user_account_type' => $paymentInfo['payment_method'],
                'user_account_no' => $paymentInfo['payment_method'] == 'card' ? $paymentInfo['card'] : $paymentInfo['payment_method'] == 'nagad' ? $paymentInfo['nagad'] : $paymentInfo['bkash'],
                'getaway' => $paymentInfo['payment_method'],
                'amount' => $paymentInfo['amount'],
                'com_account_no' => DB::table('company_accounts')->where('type', $paymentInfo['payment_method'])->value('account_number'),
                'transaction_reference' => $transactionRef,
                'purpose' => 'booking',
                'tran_date' => now(),
            ]);
            DB::commit();

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

    public function mybookings(Request $request)
    {
        try {
            $userId = $request->user()->id;
            $bookings = $this->bookingService->mybookings($userId);
            return response()->json([
                "data" => $bookings,
                "message" => "success"
            ], 200);
        } catch (Exception $ex) {
            Log::alert($ex->getMessage());
        }
    }

    public function invoice(Request $request)
    {
        try {
            $tripId = $request->input('booking_id');
            $userId = $request->user()->id;
            Log::info('Trip Id comes from front' . $tripId);

            $invoiceInfo = $this->bookingService->invoiceInfo($tripId, $userId);
            return response()->json([
                "data" => $invoiceInfo,
                "message" => "success"
            ], 200);
        } catch (Exception $ex) {
            Log::alert($ex->getMessage());
        }
    }


    public function cancleBooking()
    {
        try {
            $bookingId = request()->input('id');
    
            // Fetch booking and seat info in one query
            $bookingInformation = DB::table('bookings')
                ->where('id', $bookingId)
                ->select('seat_ids', 'status')
                ->first();
    
            if (!$bookingInformation) {
                return response()->json(['message' => 'Booking not found.'], 404);
            }
    
            $seatIds = explode(',', $bookingInformation->seat_ids);
    
            DB::beginTransaction();
    
            try {
                // Update booking status to 'cancle booking'
                DB::table('bookings')
                    ->where('id', $bookingId)
                    ->update(['status' => 'cancelled']); // Fix the typo ('cancle' -> 'cancelled')
    
                // Delete booking seats
                DB::table('booking_seats')
                    ->whereIn('seat_id', $seatIds)
                    ->where('booking_id', $bookingId)
                    ->delete();
    
                // Update seat availability
                DB::table('seat_availablities')
                    ->whereIn('seat_id', $seatIds)
                    ->update(['is_available' => 1]);
    
                // Refund the payment
                $paymentAmount = DB::table('payments')->where('booking_id', $bookingId)->value('amount');
                DB::table('refunds')->insert([
                    'booking_id' => $bookingId,
                    'amount' => $paymentAmount,
                    'reason' => 'Booking cancelled',
                    'status' => 'pending'
                ]);
    
                DB::commit();
    
                return response()->json(['message' => 'Booking cancelled successfully.'], 200);
    
            } catch (Exception $ex) {
                DB::rollBack();
                Log::error('Error during cancellation: ' . $ex->getMessage());
                return response()->json(['message' => 'Failed to cancel booking.'], 500);
            }
    
        } catch (Exception $ex) {
            Log::error('Error fetching booking details: ' . $ex->getMessage());
            return response()->json(['message' => 'Error processing cancellation request.'], 500);
        }
    }
    
}
