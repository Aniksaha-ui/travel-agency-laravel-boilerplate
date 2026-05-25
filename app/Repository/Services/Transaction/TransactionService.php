<?php

namespace App\Repository\Services\Transaction;

use App\Constants\BookingStatus;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TransactionService 
{
    public function transactions($page, $search, $fromDate = null, $toDate = null){
        try{
            if ($fromDate && $toDate && Carbon::parse($toDate)->lt(Carbon::parse($fromDate))) {
                return [
                    "status" => false,
                    "data" => [],
                    "message" => "To date must be greater than or equal to from date"
                ];
            }

            $perPage = 10;
            $transactions = DB::table('transactions')
                            ->join('payments', 'transactions.payment_id', '=', 'payments.id')
                            ->join('bookings', 'payments.booking_id', '=', 'bookings.id')
                            ->where("bookings.status","=",BookingStatus::PAID)
                            ->when($search, function ($query, $search) {
                                $searchTerm = '%' . $search . '%';

                                return $query->where(function ($searchQuery) use ($searchTerm) {
                                    $searchQuery
                                        ->where('transactions.transaction_reference', 'like', $searchTerm)
                                        ->orWhere('transactions.customer_name', 'like', $searchTerm)
                                        ->orWhere('transactions.cus_email', 'like', $searchTerm)
                                        ->orWhere('transactions.bank_transaction_id', 'like', $searchTerm)
                                        ->orWhere('payments.id', 'like', $searchTerm)
                                        ->orWhere('bookings.id', 'like', $searchTerm)
                                        ->orWhere('payments.payment_method', 'like', $searchTerm);
                                });
                            })
                            ->when($fromDate, function ($query, $fromDate) {
                                return $query->whereDate('transactions.created_at', '>=', $fromDate);
                            })
                            ->when($toDate, function ($query, $toDate) {
                                return $query->whereDate('transactions.created_at', '<=', $toDate);
                            })
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
