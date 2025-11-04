<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FinancialYear extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'report:financial_year';

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
        Log::info('Financial Year Report Command executed at ' . now());

        try{
            $financialYear = $this->getFinancialYear();
            $alreadyExist = $this->alreadyExist($financialYear);
            if($alreadyExist){
                Log::info('Already generated Financial Report');
            }

            $insertedInformation = [
                'fy_start' => $financialYear['fy_start'],
                'fy_end' => $financialYear['fy_end'],
                'payment_amount' => $this->totalPayment($financialYear),
                'refund' => $this->totalRefund($financialYear),
                'costing' => $this->totalCosting($financialYear)
            ];
            $inserted_fy_report = DB::table('fy_report')->insert($insertedInformation);
            if ($inserted_fy_report) {
                Log::info('Financial Report generated successfully. Insert result: ' . json_encode($inserted_fy_report));
            } else {
                Log::error('Financial Report could not be generated.');
            }

        }catch(Exception $ex){
            Log::error($ex->getMessage());
        }
        return 0;
    }



    function getFinancialYear()
    {
        $today = Carbon::today();
        $year = $today->year;

        // Financial year starts on July 1st
        if ($today->month < 7) {
            $fy_start = Carbon::create($year - 1, 7, 1);
            $fy_end   = Carbon::create($year, 6, 30);
        } else {
            $fy_start = Carbon::create($year, 7, 1);
            $fy_end   = Carbon::create($year + 1, 6, 30);
        }

        return [
            'fy_start' => $fy_start->toDateString(),
            'fy_end'   => $fy_end->toDateString(),
        ];
    }

    function totalPayment($financialYear)
    {

        $paymentAmount = DB::table('payments')
            ->whereBetween('created_at', [$financialYear['fy_start'], $financialYear['fy_end']])
            ->sum('amount');

        return $paymentAmount;
    }


    function totalRefund($financialYear)
    {
        $refundAmount = DB::table('refunds')
            ->whereBetween('created_at', [$financialYear['fy_start'], $financialYear['fy_end']])
            ->where('status','disbursed')
            ->sum('amount');
        return $refundAmount;
    }

    function totalCosting($financialYear)
    {

        $totalCosting = DB::table('account_history')
            ->whereBetween('tran_date', [$financialYear['fy_start'], $financialYear['fy_end']])
            ->where('transaction_type','d')
            ->sum('amount');
        return $totalCosting;
    }

    function alreadyExist($financialYear){
        $isExist = DB::table('fy_report')
            ->where('fy_start', $financialYear['fy_start'])
            ->where('fy_end', $financialYear['fy_end'])
            ->first();
        return $isExist;
    }

}
