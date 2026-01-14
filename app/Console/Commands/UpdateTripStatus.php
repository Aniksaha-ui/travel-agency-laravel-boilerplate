<?php

namespace App\Console\Commands;

use App\Constants\TripStatus;
use App\Repository\Services\Monitoring\TelegramService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class UpdateTripStatus extends Command
{

     public function send(TelegramService $telegram,$message)
    {
        $telegram->sendMessage($message);

        return 1;
    }
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'trip:inactiveTrips';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
         DB::table('trips')
         ->where('status', TripStatus::ACTIVE)
         ->Where('is_active', TripStatus::ACTIVE)
        ->where('departure_time', '<', now())
        ->update(['status' => TripStatus::INACTIVE,'is_active'=>TripStatus::INACTIVE]);
        $msg ="Departed trips become inactive";
        // $this->send(app(TelegramService::class),$msg);   # send notification is off currently
        
        return 0;
    }
}
