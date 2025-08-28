<?php

namespace App\Repository\Services\Transaction;

use App\Helpers\admin\FileManageHelper;
use App\Repository\Interfaces\CommonInterface;
use App\Repository\Services\Vehicles\VehicleService;
use App\route;
use Exception;
use Illuminate\Support\Facades\Log;
use DB;

class TransactionService 
{
    public function transactions($page,$search){
        try{
            $perPage = 100;
            $transactions = DB::table('transactions')
                            ->join('payments', 'transactions.payment_id', '=', 'payments.id')
                            ->join('bookings', 'payments.booking_id', '=', 'bookings.id')
                            ->paginate($perPage, ['transactions.id','transactions.transaction_reference','payments.amount','payments.payment_method','bookings.booking_type as purpose'], 'page', $page);
             if ($transactions->count() > 0) {
                return ["status" => true, "data" => $transactions, "message" => "Transaction information retrieved successfully"];
            } else {
                return ["status" => true, "data" => [], "message" => "No Report found"];
            }

        }catch(Exception $ex){
            Log::error("transactionService transactions function error: " . $ex->getMessage());
        }
    }
}