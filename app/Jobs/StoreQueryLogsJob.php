<?php

namespace App\Jobs;

use App\query_logs_table;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class StoreQueryLogsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

   public function __construct(
        public array $queries
    ) {}

    public function handle()
    {
        Log::info(json_encode($this->queries));
        query_logs_table::insert($this->queries);
    }
}
