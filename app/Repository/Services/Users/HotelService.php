<?php

namespace App\Repository\Services\Users;

use App\Repository\Interfaces\CommonInterface;
use App\route;
use Exception;
use Illuminate\Support\Facades\Log;
use DB;
use Illuminate\Support\Facades\Auth;

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
            $hotelInformation = DB::table('hotel_rooms')
                ->join('room_prices', 'hotel_rooms.id', '=', 'room_prices.hotel_room_id')
                ->join('hotels', 'hotels.id', '=', 'hotel_rooms.hotel_id')
                ->join('room_types', 'hotel_rooms.room_type_id', '=', 'room_types.id')
                ->select(
                    'hotels.*',
                    'hotel_rooms.id',
                    'hotel_rooms.max_occupancy',
                    'hotel_rooms.id as hotel_room_number',
                    'hotel_rooms.room_size',
                    'room_prices.season_start',
                    'room_prices.season_end',
                    'room_prices.price_per_night',
                    'room_types.type_name'
                )
                ->when($hotelName, function ($query) use ($hotelName) {
                    return $query->where('hotels.hotel_name', 'like', '%' . $hotelName . '%');
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
            }
            if ($hotelBookings && $hotelCheckin) {
                DB::commit();
                return [
                    'status' => true,
                    'data' => $hotelBookings,
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
