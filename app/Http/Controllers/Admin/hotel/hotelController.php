<?php

namespace App\Http\Controllers\Admin\hotel;

use App\Http\Controllers\Controller;
use App\Repository\Services\Hotel\HotelService;
use Illuminate\Http\Request;
use DB;
use Exception;
use Illuminate\Support\Facades\Log;

class hotelController extends Controller
{

    protected $hotelService;

    public function __construct(HotelService $hotelService)
    {
        $this->hotelService = $hotelService;
    }

    public function store(Request $request)
    {
        try {
            $response = $this->hotelService->store($request->all());
            return $this->serviceResponse($response, $response['code'] ?? 200);
        } catch (\Exception $e) {
            Log::error("hotelController store: " . $e->getMessage());
            return $this->failedResponse();
        }
    }


    public function update(Request $request, $hotelId)
    {
        try {
            $response = $this->hotelService->update($hotelId, $request->all());
            return $this->serviceResponse($response, $response['code'] ?? 200);
        } catch (\Exception $e) {
            Log::error("hotelController update: " . $e->getMessage());
            return $this->failedResponse();
        }
    }


    public function getHotels(Request $request)
    {
        try {
            $page = $request->query('page');
            $search = $request->query('search');
            $perPage = 10;
            $response = $this->hotelService->getHotels($perPage, $page, $search);
            return $this->serviceResponse($response);
        } catch (\Exception $e) {
            Log::error("hotelController getHotels: " . $e->getMessage());
            return $this->failedResponse();
        }
    }

    public function getHotelById($hotelId)
    {
        try {
            // Fetch hotel details
            $hotel = DB::table('hotels')
                ->where('id', $hotelId)
                ->first();

            if (!$hotel) {
                return $this->failedResponse('Hotel not found', 404);
            }

            // Fetch hotel photos
            $photos = DB::table('hotel_photos')
                ->where('hotel_id', $hotelId)
                ->pluck('photo_url');

            // Fetch room details with seasonal pricing
            $rooms = DB::table('hotel_rooms')
                ->where('hotel_id', $hotelId)
                ->get();



            $roomsWithPricing = [];
            foreach ($rooms as $room) {
                $prices = DB::table('room_prices')
                    ->join('hotel_rooms', 'room_prices.hotel_room_id', '=', 'hotel_rooms.id')
                    ->join('room_types', 'hotel_rooms.room_type_id', '=', 'room_types.id')
                    ->where('hotel_room_id', $room->id)
                    ->select('room_prices.*', 'room_types.type_name')
                    ->orderBy('room_prices.season_start', 'asc')
                    ->get();

                $roomData = [
                    'room_id' => $room->id,
                    'room_size' => $room->room_size,
                    'max_occupancy' => $room->max_occupancy,
                    'amenities' => $room->amenities,
                    'total_rooms' => $room->total_rooms,
                    'prices' => $prices
                ];

                $roomsWithPricing[] = $roomData;
            }

            // Return the hotel details along with rooms and pricing
            return $this->successResponse([
                'hotel' => [
                    'id' => $hotel->id,
                    'name' => $hotel->name,
                    'email' => $hotel->email,
                    'city' => $hotel->city,
                    'website' => $hotel->website,
                    'description' => $hotel->description,
                    'location' => $hotel->location,
                    'star_rating' => $hotel->star_rating,
                    'facilities' => $hotel->facilities,
                    'photos' => $photos,
                    'rooms' => $roomsWithPricing
                ]
            ]);
        } catch (Exception $ex) {
            Log::error("hotelController getHotelById: " . $ex->getMessage());
            return $this->failedResponse();
        }
    }

    public function hotelCheckinList(Request $request)
    {
        try {

            $page = $request->query('page');
            $search = $request->query('search');
            $response = $this->hotelService->hotelCheckinList($page, $search);
            return $this->serviceResponse($response);
        } catch (\Exception $e) {
            Log::error("hotelController hotelCheckinList: " . $e->getMessage());
            return $this->failedResponse();
        }
    }

    public function hotelBooking(Request $request)
    {
        try {
            $page = $request->query('page');
            $search = $request->query('search');

            $response = $this->hotelService->hotelBooking($page, $search);
            return $this->serviceResponse($response);
        } catch (Exception $ex) {
            Log::info("hotelController hotelBooking function" . $ex->getMessage());
            return $this->failedResponse();
        }
    }
}
