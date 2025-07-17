<?php

namespace App\Repository\Services\Hotel;

use Exception;
use Illuminate\Support\Facades\Log;
use DB;

class HotelService
{

    public function store($request)
    {

        try {
            // Insert hotel
            $hotelId = DB::table('hotels')->insertGetId([
                'name' => $request->name,
                'location' => $request->location,
                'star_rating' => $request->star_rating,
                'description' => $request->description,
                'facilities' => $request->facilities,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // Insert hotel photos
            foreach ($request->photos as $photo) {
                DB::table('hotel_photos')->insert([
                    'hotel_id' => $hotelId,
                    'photo_url' => $photo,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }

            // Handle rooms
            foreach ($request->rooms as $room) {
                // Insert or get room_type_id
                $roomType = DB::table('room_types')->where('type_name', $room['type_name'])->first();
                if (!$roomType) {
                    $roomTypeId = DB::table('room_types')->insertGetId([
                        'type_name' => $room['type_name']
                    ]);
                } else {
                    $roomTypeId = $roomType->id;
                }

                // Insert hotel room
                $hotelRoomId = DB::table('hotel_rooms')->insertGetId([
                    'hotel_id' => $hotelId,
                    'room_type_id' => $roomTypeId,
                    'room_size' => $room['room_size'],
                    'max_occupancy' => $room['max_occupancy'],
                    'amenities' => $room['amenities'],
                    'total_rooms' => $room['total_rooms'],
                    'created_at' => now(),
                    'updated_at' => now()
                ]);

                // Insert seasonal pricing
                foreach ($room['prices'] as $price) {
                    DB::table('room_prices')->insert([
                        'hotel_room_id' => $hotelRoomId,
                        'season_start' => $price['season_start'],
                        'season_end' => $price['season_end'],
                        'price_per_night' => $price['price_per_night'],
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                }
            }

            DB::commit();

            return ['status' => true, 'message' => 'Hotel created successfully'];
        } catch (Exception $ex) {
            return ['status' => false, 'message' => $ex->getMessage()];
            DB::rollBack();
            Log::alert($ex->getMessage());
        }
    }
}
