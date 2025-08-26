<?php

namespace App\Repository\Services\Users;

use App\Repository\Interfaces\CommonInterface;
use App\route;
use Exception;
use Illuminate\Support\Facades\Log;
use DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;


class HotelService
{

    protected $contact;

    /**
     * Get all hotel.
     *
     * @return array
     */
    public function index($hotelName, $city, $country, $guests)
    {
        try {
            $hotelInformation = DB::table('hotels')
                ->select(
                    'hotels.*',
                )
                ->when($hotelName, function ($query) use ($hotelName) {
                    return $query->where('hotels.name', 'like', '%' . $hotelName . '%');
                })
                ->when($city, function ($query) use ($city) {
                    return $query->where('hotels.city', 'like', '%' . $city . '%');
                })
                ->when($country, function ($query) use ($country) {
                    return $query->where('hotels.country', 'like', '%' . $country . '%');
                })
                ->when($guests, function ($query) use ($guests) {
                    return $query->where('hotel_rooms.max_occupancy', '>=', $guests);
                })
                ->get();

            return [
                'status' => true,
                'data' => $hotelInformation,
                'message' => 'Hotel found successfully',
            ];
        } catch (Exception $ex) {
            Log::alert("Error in HotelService(users)" . $ex->getMessage());
            return ['status' => false, 'data' => [], 'message' => 'Internal Server Error'];
        }
    }

    public function hotelBookings($data)
    {
        try {
            DB::beginTransaction();
            $userId = Auth::user() ? Auth::user()->id : null;
            if (!$userId) {
                DB::rollBack();
                return [
                    'status' => false,
                    'data' => [],
                    'message' => 'User not found',
                ];
            }

            $roomExist = DB::table('hotel_rooms')
                ->where('id', $data['hotel_room_id'])
                ->where('hotel_id', $data['hotel_id'])
                ->exists();


            if (!$roomExist) {
                DB::rollBack();
                return [
                    'status' => false,
                    'data' => [],
                    'message' => 'Room not exist',
                ];
            }
            $alreadyBooked = DB::table('hotel_bookings')
                ->where('hotel_id', $data['hotel_id'])
                ->where('hotel_room_id', $data['hotel_room_id'])
                ->where(function ($query) use ($data) {
                    $query->whereBetween('check_in_date', [$data['check_in_date'], $data['check_out_date']])
                        ->orWhereBetween('check_out_date', [$data['check_in_date'], $data['check_out_date']]);
                })
                ->exists();

            if ($alreadyBooked) {
                DB::rollBack();
                return [
                    'status' => false,
                    'data' => [],
                    'message' => 'Room already booked',
                ];
            }

            $bookingInformation = [
                'user_id' => $userId,
                'hotel_id' => $data['hotel_id'],
                'hotel_room_id' => $data['hotel_room_id'],
                'check_in_date' => $data['check_in_date'],
                'check_out_date' => $data['check_out_date'],
                'total_persons' => $data['total_persons'],
                'total_cost' => $data['total_cost'],
                'booking_type' => 'direct',
                'payment_status' => 'paid',
                'booking_status' => 'confirmed',
            ];

            $hotelBookings = DB::table('hotel_bookings')->insertGetId($bookingInformation);

            if ($hotelBookings) {
                $payloadForHotelCheckin = [
                    'hotel_booking_id' => $hotelBookings,
                    'check_in_time' => $data['check_in_date'],
                    'check_out_time' => $data['check_out_date'],
                    'status' => 'pending',
                ];
                $hotelCheckin = DB::table('checkins')->insert($payloadForHotelCheckin);
                $bookingInformation = [
                    'hotel_booking_id' => $hotelBookings,
                    'status' => 'paid',
                    'user_id' => $userId,
                    'booking_type' => 'hotel booking'
                ];
                $bookings = DB::table('bookings')->insertGetId($bookingInformation);

                $paymentInformation = [
                    'booking_id' => $bookings,
                    'amount' => $data['total_cost'],
                    'payment_method' => $data['payment_method'],
                    'created_at' => now(),
                    'updated_at' => now(),

                ];

                $accountHistoryInformation = [];

                if ($data['payment_method'] == 'card') {
                    $paymentInformation['card'] = $data['card'];
                    $accountHistoryInformation['user_account_no'] = $data['card'];
                }
                if ($data['payment_method'] == 'bkash') {
                    $paymentInformation['bkash'] = $data['bkash'];
                    $accountHistoryInformation['user_account_no'] = $data['bkash'];
                }
                if ($data['payment_method'] == 'nagad') {
                    $paymentInformation['nagad'] = $data['nagad'];
                    $accountHistoryInformation['user_account_no'] = $data['nagad'];
                }

                $payment = DB::table('payments')->insertGetId($paymentInformation);
                $transactionInfo = [
                    'payment_id' => $payment,
                    'transaction_reference' => Str::uuid(),
                    'created_at' => now(),
                    'updated_at' => now()
                ];

                $transaction = DB::table('transactions')->insert($transactionInfo);

                $accountHistoryInformation = [
                    'user_id' => $userId,
                    'user_account_type' => $data['payment_method'],
                    'getaway' => $data['payment_method'],
                    'amount' => $data['total_cost'],
                    'com_account_no' => DB::table('company_accounts')->where('type', $data['payment_method'])->value('account_number'),
                    'transaction_reference' => Str::uuid(),
                    'transaction_type' => 'c',
                    'purpose' => 'hotel booking',
                    'tran_date' => now(),
                    'user_account_no' => $accountHistoryInformation['user_account_no'] ?? null
                ];

                ## Account History
                $accountHistory = DB::table('account_history')->insert($accountHistoryInformation);
                $accountAmountIncrease = DB::table('company_accounts')->where('type', $paymentInformation['payment_method'])->increment('amount', $paymentInformation['amount']);


                if ($hotelBookings && $hotelCheckin && $bookings && $accountHistory && $payment) {
                    DB::commit();
                    return [
                        'status' => true,
                        'data' => $bookings,
                        'message' => 'Hotel booking successfully',
                    ];
                } else {
                    DB::rollBack();
                    return [
                        'status' => false,
                        'data' => [],
                        'message' => 'Hotel booking failed',
                    ];
                }
            } else {
                DB::rollBack();
                return [
                    'status' => false,
                    'data' => [],
                    'message' => 'Hotel booking failed',
                ];
            }
        } catch (Exception $ex) {
            DB::rollBack();
            Log::alert("Error in HotelService(users) hotelBookings Functions" . $ex->getMessage());
            return ['status' => false, 'data' => [], 'message' => 'Internal Server Error'];
        }
    }

    public function hotelCheckinStatusUpdate($hotel_booking_id, $status)
    {
        try {
            $response = DB::table('checkins')->where('hotel_booking_id', $hotel_booking_id)->update(['status' => $status]);
            if ($response) {
                return [
                    'status' => true,
                    'data' => [],
                    'message' => 'Checkin status updated successfully',
                ];
            } else {
                return [
                    'status' => false,
                    'data' => [],
                    'message' => 'Checkin status not updated',
                ];
            }
        } catch (Exception $ex) {
            Log::info('Error in HotelService(users) hotelCheckinStatusUpdate Functions' . $ex->getMessage());
            return ['status' => false, 'data' => [], 'message' => 'Internal Server Error'];
        }
    }
}
