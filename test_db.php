<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    $rooms = DB::table('hotel_rooms')->get(['id', 'room_type_id']);
    foreach ($rooms as $room) {
        echo "Room ID: {$room->id}, Type ID: {$room->room_type_id}\n";
    }
    
    $allPrices = DB::table('room_prices')
        ->orderBy('hotel_room_id')
        ->orderBy('season_start')
        ->get();
    echo "All Prices:\n";
    foreach ($allPrices as $price) {
        echo "ID: {$price->id}, Room: {$price->hotel_room_id}, Start: {$price->season_start}, End: {$price->season_end}\n";
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
