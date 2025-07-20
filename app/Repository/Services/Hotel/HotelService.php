<?php

namespace App\Repository\Services\Hotel;

use Exception;
use Illuminate\Support\Facades\Log;
use DB;

class HotelService
{
    public function store($request)
    {
        DB::beginTransaction();

        try {
            // Insert hotel
            $hotelId = DB::table('hotels')->insertGetId([
                'name' => $request['name'],
                'location' => $request['location'],
                'star_rating' => $request['star_rating'],
                'description' => $request['description'],
                'facilities' => $request['facilities'],
                'email' => $request['email'],
                'website' => $request['website'],
                'city' => $request['city'],
                'country' => $request['country'],
                'status' => $request['status'],
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // Insert hotel photos
            foreach ($request['photos'] ?? [] as $photo) {
                DB::table('hotel_photos')->insert([
                    'hotel_id' => $hotelId,
                    'photo_url' => $photo,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }

            // Handle rooms
            foreach ($request['rooms'] ?? [] as $room) {
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
                foreach ($room['prices'] ?? [] as $price) {
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

            return [
                'status' => true,
                'message' => 'Hotel with rooms created successfully.'
            ];
        } catch (Exception $e) {
            DB::rollBack();
            return [
                'status' => false,
                'message' => 'Failed to create hotel: ' . $e->getMessage()
            ];
        }
    }


    public function update($hotelId,  $request)
    {
        DB::beginTransaction();

        try {
            // Update hotel details
            DB::table('hotels')->where('id', $hotelId)->update([
                'name' => $request['name'],
                'location' => $request['location'],
                'star_rating' => $request['star_rating'],
                'description' => $request['description'],
                'facilities' => $request['facilities'],
                'updated_at' => now()
            ]);

            // Update hotel photos (delete old, insert new)
            DB::table('hotel_photos')->where('hotel_id', $hotelId)->delete();

            foreach ($request['photos'] ?? [] as $photo) {
                DB::table('hotel_photos')->insert([
                    'hotel_id' => $hotelId,
                    'photo_url' => $photo,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }

            // Handle rooms and pricing updates
            foreach ($request['rooms'] ?? [] as $room) {
                $roomExists = DB::table('hotel_rooms')->where('id', $room['room_id'])->count();
                if ($roomExists > 0) {
                    DB::table('hotel_rooms')->where('id', $room['room_id'])->update([
                        'room_size' => $room['room_size'],
                        'max_occupancy' => $room['max_occupancy'],
                        'amenities' => $room['amenities'],
                        'total_rooms' => $room['total_rooms'],
                        'updated_at' => now()
                    ]);

                    DB::table('room_prices')->where('hotel_room_id', $room['room_id'])->delete();

                    foreach ($room['prices'] ?? [] as $price) {
                        DB::table('room_prices')->insert([
                            'hotel_room_id' => $room['room_id'],
                            'season_start' => $price['season_start'],
                            'season_end' => $price['season_end'],
                            'price_per_night' => $price['price_per_night'],
                            'created_at' => now(),
                            'updated_at' => now()
                        ]);
                    }
                } else {
                    DB::rollBack();
                    return [
                        'status' => false,
                        'message' => 'Room not found',
                        'code' => 404
                    ];
                }
            }

            DB::commit();

            return [
                'status' => true,
                'message' => 'Hotel updated successfully.',
                'code' => 200
            ];
        } catch (Exception $e) {
            DB::rollBack();
            return [
                'status' => false,
                'message' => 'Failed to update hotel: ' . $e->getMessage(),
                'code' => 500
            ];
        }
    }


    public function getHotels($perPage, $page, $search)
    {
        try {

            $perPage = 10;
            $hotels = DB::table('hotels')
                ->where('name', 'like', '%' . $search . '%')
                ->paginate($perPage, ['hotels.*'], 'page', $page);
            if ($hotels->count() > 0) {
                return ["status" => true, "data" => $hotels, "message" => "Hotels list retrived successfully"];
            } else {
                return ["status" => true, "data" => [], "message" => "No Hotel found"];
            }
        } catch (Exception $ex) {
            Log::info("getguide functions" . $ex->getMessage());
        }
    }
}
