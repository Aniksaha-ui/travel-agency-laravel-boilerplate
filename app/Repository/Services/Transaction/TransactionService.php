<?php

namespace App\Repository\Services\Transaction;

use App\Constants\BookingStatus;
use App\Helpers\admin\FileManageHelper;
use App\Repository\Interfaces\CommonInterface;
use App\Repository\Services\Vehicles\VehicleService;
use App\route;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TransactionService 
{
    public function transactions($page,$search){
        try{
            $perPage = 10;
            $transactions = DB::table('transactions')
                            ->join('payments', 'transactions.payment_id', '=', 'payments.id')
                            ->join('bookings', 'payments.booking_id', '=', 'bookings.id')
                            ->where("bookings.status","=",BookingStatus::PAID)
                            ->orderBy('transactions.id', 'desc')
                            ->paginate($perPage, ['transactions.id as transaction_id','payments.id as payment_id','transactions.transaction_reference','transactions.bank_tran_id','transactions.card_type','transactions.card_brand','transactions.risk_title','transactions.settlement_status','transactions.bank_approval_id','transactions.cus_email','transactions.customer_name','transactions.created_at','transactions.bank_transaction_id','transactions.settled_amount','transactions.customer_paid_amount','payments.amount','payments.payment_method','bookings.booking_type as purpose','bookings.id as booking_id'], 'page', $page);
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