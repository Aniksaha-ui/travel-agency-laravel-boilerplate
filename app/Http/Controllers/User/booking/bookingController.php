<?php

namespace App\Http\Controllers\User\booking;

use App\Constants\ApiResponseStatus;
use App\Constants\BookingStatus;
use App\Constants\BookingType;
use App\Constants\PaymentForOnline;
use App\Constants\TripStatus;
use App\Http\Controllers\Controller;
use App\Repository\Services\SSLPayment\SSLPaymentService;
use App\Repository\Services\Users\BookingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use DB;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;


class bookingController extends Controller
{
    private $bookingService;
    private $sslPayment;

    public function __construct(BookingService $bookingService, SSLPaymentService $sslPayment)
    {
        $this->bookingService = $bookingService;
        $this->sslPayment = $sslPayment;
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
                'status' => 'FAILED',
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            DB::beginTransaction(); 
            $isOnlinePayment = DB::table('payment_method_config')->where('payment_for',PaymentForOnline::TRIP)->value('online_payment');

            $seatInfo = $request->input('seatinfo');
            $seatIds = array_column($seatInfo, "seat_id");
            $tripId = $seatInfo[0]['trip_id']; 
            $paymentInfo = $request->input('paymentinfo');

            $tripActive = DB::table('trips')->where('id',$tripId)->where('is_active',TripStatus::ACTIVE)->first();

            if(!$tripActive){
                return response()->json([
                'status' => 'FAILED',
                'message' => 'Trip is not available now!',
                ], 422);
            }

            // Check if any of the requested seats are already booked
            $existingSeats = DB::table('booking_seats')
                ->join('bookings', 'booking_seats.booking_id', '=', 'bookings.id')
                ->whereIn('seat_id', $seatIds)
                ->where('bookings.status', "!=",BookingStatus::PENDING)
                ->where('trip_id', $tripId)
                ->exists();

            if ($existingSeats) {
                return response()->json([
                    'status' => 'FAILED',
                    'message' => 'One or more selected seats are already booked.',
                ], 422);
            }

            // Insert into bookings table
            $lastBookingId = DB::table('bookings')->insertGetId([
                "user_id" => $request->user()->id,
                "trip_id" => $tripId,
                "seat_ids" => implode(",", $seatIds),
                "status" => $isOnlinePayment == 1 ? "pending" : "paid",
                'booking_type' => BookingType::TRIP,
                "created_at" => now(),
                "updated_at" => now()
            ]);

           

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
            $transactionRef =DB::table('transactions')->insertGetId([
                "payment_id" => $lastPaymentId,
                "transaction_reference" => $transactionRef,
                "created_at" => now(),
                "updated_at" => now()
            ]);

           if(isset($isOnlinePayment) && $isOnlinePayment==1){
                Log::info("online payment is enabled");
                $post_data = [
                        'store_id' => env('STORE_ID'),
                        'store_passwd' => env('STORE_PASSWORD'),
                        'total_amount' => $paymentInfo['amount'],
                        'currency' => 'BDT',
                        'tran_id' => $transactionRef,
                        'success_url' => route('payment.success'),
                        'fail_url' => route('payment.fail'),
                        'cancel_url' => route('payment.cancel'),
                        'emi_option' => 0,
                        'cus_name' => Auth::user()->name,
                        'cus_email' => Auth::user()->email,
                        'cus_add1' => "Dhaka,Bangladesh",
                        'cus_city' => 'Dhaka',
                        'cus_state' => '7800',
                        'cus_postcode' =>  '7800',
                        'cus_country' => 'Bangladesh',
                        'cus_phone' => "01628781323",
                        'shipping_method' => 'NO',
                        'product_name' => 'Ticket Booking Id #' . $lastBookingId,
                        'product_category' => 'Education',
                        'product_profile' => 'general',
                        'value_a' => $lastBookingId, // Custom field to track order
                        'value_b' => json_encode($request->all()),
                    ];
                    
                Log::info("init request".json_encode($post_data));

                $initPayment = $this->sslPayment->initSSLTransaction($post_data);

                if(isset($initPayment) && $initPayment['status'] == 'success'){
                    $msg = "Last time trip booking information" . "Payment Id: ". $lastPaymentId . ".Transaction Reference: " .$transactionRef ;
                    Log::info($msg);
                    DB::commit();   
                    return response()->json([
                        'status' => ApiResponseStatus::SUCCESS,
                        'message' => 'Booking successfully created!',
                        'data' => [
                            "redirected_url" => $initPayment['url'],
                            'booking_id' => $lastBookingId,
                            'trip_id' => $tripId,
                            'seats' => $seatInfo,
                            'payment_id' => $lastPaymentId,
                            'transaction_reference' => $transactionRef
                        ]
                    ], 201);
                } 
           }


        #if online payment is not enabled

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



           DB::table('seat_availablities')
                ->where('trip_id', $tripId)
                ->whereIn('seat_id', $seatIds)
                ->update(['is_available' => 0]);
       
            DB::table('company_accounts')->where('type', $paymentInfo['payment_method'])->increment('amount', $paymentInfo['amount']);
            DB::table('account_history')->insert([
                'user_id' => $request->user()->id,
                'user_account_type' => $paymentInfo['payment_method'],
                'user_account_no' => $paymentInfo['payment_method'] == 'card'
                    ? $paymentInfo['card']
                    : ($paymentInfo['payment_method'] == 'nagad'
                        ? $paymentInfo['nagad']
                        : $paymentInfo['bkash']),
                'getaway' => $paymentInfo['payment_method'],
                'amount' => $paymentInfo['amount'],
                'com_account_no' => DB::table('company_accounts')->where('type', $paymentInfo['payment_method'])->value('account_number'),
                'transaction_reference' => $transactionRef,
                'purpose' => 'booking',
                'tran_date' => now(),
            ]);

            $msg = "Last time trip booking information" . "Payment Id: ". $lastPaymentId . ".Transaction Reference: " .$transactionRef ;
            Log::info($msg);
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


            DB::rollBack();
            return response()->json([
                    'status' => ApiResponseStatus::FAILED,
                    'message' => 'Booking successfully created!',
                    'data' => []
                ], 422);



       
        } catch (\Exception $e) {
            DB::rollBack(); // Rollback transaction on error
            return response()->json([
                'status' => 'FAILED',
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

    public function successPayment(Request $request){
       try {

            
            $tran_id = $request->tran_id;

            $verifyURL = env('IS_SANDBOX')
                ? "https://dev-securepay.sslcommerz.com/validator/api/validationserverAPI.php"
                : "https://dev-securepay.sslcommerz.com/validator/api/validationserverAPI.php";



            $verifyResponse = Http::get($verifyURL, [
                'val_id' => $request->val_id,
                'store_id' => env('STORE_ID'),
                'store_passwd' => env('STORE_PASSWORD'),
                'v' => 1,
                'format' => 'json'
            ]);

            $verifyData = $verifyResponse->json();
            // dd($verifyData);



            if ($verifyData['status'] === 'VALID' || $verifyData['status'] === 'VALIDATED') {

                //information from verify data 
              $transactionInfo = [
                                'val_id'               => $verifyData['val_id'] ?? null,
                                'settled_amount'       => $verifyData['store_amount'] ?? 0,
                                'customer_paid_amount' => $verifyData['amount'] ?? 0,
                                'bank_transaction_id'  => $verifyData['bank_tran_id'] ?? null,
                                'card_type'            => $verifyData['card_type'] ?? null,
                                'card_brand'           => $verifyData['card_brand'] ?? null,
                                'risk_title'           => $verifyData['risk_title'] ?? null,
                                'settlement_status'    => $verifyData['settlement_status'] ?? null,
                                'bank_approval_id'     => $verifyData['bank_approval_id'] ?? null,
                                'customer_name'        => $verifyData['cus_name'] ?? null,
                                'customer_email'       => $verifyData['cus_email'] ?? null,
                            ];
                $transactionUpdate = DB::table('transactions')->where('id',$tran_id)->update($transactionInfo);

                $bookingInfo =  json_decode($verifyData['value_b'],true);
                $bookingId = $verifyData['tran_id'] ?? null;
                // $userId = $verifyData['value_c'];

                $booking_type = DB::table('bookings')->where('id',$bookingId)->value('booking_type');

               
                if($booking_type=="trip"){
                  $response =  $this->tripBookingProcess($bookingId, $bookingInfo);
                  if ($response) {
                        $frontendUrl = env('FRONTEND_URL') . '/my-bookings';
                        return redirect()->away($frontendUrl);
                  }
                }
            }


            

    }catch(Exception $ex){
        dd($ex->getMessage());
    }
    }


    public function tripBookingProcess($bookingId,$bookingInfo){
        
        try{

            #inputted information
            $paymentInfo = $bookingInfo['paymentinfo'];
            $seatInfo = $bookingInfo['seatinfo'];
            $seatIds = array_column($seatInfo, "seat_id");
            $tripId = $seatInfo[0]['trip_id']; 

            #account information 
            $accountInsertInformation = [
                'user_id'                    => 1,
                'user_account_type'          => $paymentInfo['payment_method'],
                'user_account_no'            => $paymentInfo['payment_method'] == 'card' ? $paymentInfo['card']
                                                : ($paymentInfo['payment_method'] == 'nagad'
                                                    ? $paymentInfo['nagad']
                                                    : $paymentInfo['bkash']),
                'getaway'                   => $paymentInfo['payment_method'] ?? 'visa',
                'amount'                     => $paymentInfo['amount'],
                'com_account_no'             => DB::table('company_accounts')->where('type', $paymentInfo['payment_method'])->value('account_number'),
                'transaction_reference'      => $bookingId,
                'purpose'                    => 'Trip booking',
                'tran_date'                  => now(),
            ];

            //add money to account
            $accountAmountUpdate = DB::table('company_accounts')->where('type', $paymentInfo['payment_method'])->increment('amount', $paymentInfo['amount']);
           
         
             //account history
            $accountInfo = DB::table('account_history')->insert($accountInsertInformation);

            $updateBookingPaymentStatus = DB::table('bookings')->where('id', $bookingId)->update(['status' => 'paid']);

            
            if ($updateBookingPaymentStatus) {
                
                $bookingSeats = [];
                foreach ($seatInfo as $seat) {
                    $bookingSeats[] = [
                        'booking_id' => $bookingId,
                        'seat_id' => $seat['seat_id'],
                        'created_at' => now(),
                        'updated_at' => now()
                    ];
                }

                // Bulk insert into booking_seats table
                $assignSeats = DB::table('booking_seats')->insert($bookingSeats);  

                 // Update seat availability
                $seatAvailabilityUpdate = DB::table('seat_availablities')
                ->where('trip_id', $tripId)
                ->whereIn('seat_id', $seatIds)
                ->update(['is_available' => 0]);
                 
                return true;
            }
            return true;


        }catch(Exception $ex){
            dd($ex->getMessage());

        }


    }



}
