<?php

namespace App\Repository\Services\Ticket;

use App\Helpers\admin\FileManageHelper;
use Exception;
use Illuminate\Support\Facades\Log;
use DB;
use Illuminate\Support\Facades\Auth;

class TicketService
{
    public function store($ticketData)
    {
        DB::beginTransaction();

        try {
            if (request()->hasFile('attachment')) {
                $documentLink = FileManageHelper::uploadFile('tickets', $ticketData['attachment']);
                $ticketData['attachment'] = $documentLink;
            } else {
                $ticketData['attachment'] = '';
            }


            $ticketData['generate_by'] = Auth::id();
            $ticketInsert = DB::table('tickets')->insert($ticketData);

            if($ticketInsert){

                DB::commit();
                return [
                    'isExecute' => true,
                    'message' => 'Ticket created successfully.',
                    'data'=> []
                ];
            }else {
                    return [
                    'isExecute' => true,
                    'data'=> [],
                    'message' => 'Ticket can not created successfully.'
                ];
            }

        } catch (Exception $e) {
            Log::info("error in TicketService : store function" .$e->getMessage());
            DB::rollBack();
            return [
                'status' => false,
                'message' => 'Failed to create ticket: ' . $e->getMessage()
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



    public function myTickets()
    {
        try {
                $userId = Auth::id();
                $ticketInformation = DB::table('tickets')
                    ->where("generate_by", $userId)
                    ->get();
                    
                return $ticketInformation;

            } catch (Exception $ex) {
                Log::alert($ex->getMessage());
            }
    }







    

}
