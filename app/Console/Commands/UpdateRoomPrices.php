<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class UpdateRoomPrices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'room-prices:update-next-term {hotel_room_id?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Pulls room price information from the previous year and stores it for the next year/term';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $hotelRoomId = $this->argument('hotel_room_id');
        $this->info('Starting Room Prices Update...');

        // Get rooms to process
        $roomsQuery = DB::table('hotel_rooms');
        if ($hotelRoomId) {
            $roomsQuery->where('id', $hotelRoomId);
        }
        $rooms = $roomsQuery->get();

        if ($rooms->isEmpty()) {
            $this->warn('No rooms found to process.');
            return 0;
        }

        $totalAdded = 0;

        foreach ($rooms as $room) {
            // Find the latest pricing term for this room
            $latestPrice = DB::table('room_prices')
                ->where('hotel_room_id', $room->id)
                ->orderBy('season_end', 'desc')
                ->first();

            if (!$latestPrice) {
                $this->warn("No existing pricing found for Room ID: {$room->id}. Skipping.");
                continue;
            }

            $latestYear = Carbon::parse($latestPrice->season_start)->year;
            $this->info("Processing Room ID: {$room->id} - Latest year found: {$latestYear}");

            // Fetch all seasonal prices for that specific year
            $pricesToReplicate = DB::table('room_prices')
                ->where('hotel_room_id', $room->id)
                ->whereYear('season_start', $latestYear)
                ->get();

            foreach ($pricesToReplicate as $price) {
                $nextSeasonStart = Carbon::parse($price->season_start)->addYear();
                $nextSeasonEnd = Carbon::parse($price->season_end)->addYear();

                // Check if the price already exists for the next term to avoid duplicates
                $exists = DB::table('room_prices')
                    ->where('hotel_room_id', $room->id)
                    ->where('season_start', $nextSeasonStart->toDateString())
                    ->where('season_end', $nextSeasonEnd->toDateString())
                    ->exists();

                if (!$exists) {
                    DB::table('room_prices')->insert([
                        'hotel_room_id' => $room->id,
                        'season_start' => $nextSeasonStart->toDateString(),
                        'season_end' => $nextSeasonEnd->toDateString(),
                        'price_per_night' => $price->price_per_night,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                    $totalAdded++;
                }
            }
        }

        $this->info("Success! Added {$totalAdded} new room prices.");
        Log::info("Room Prices Update Sync: Added {$totalAdded} new prices.");

        return 0;
    }
}
