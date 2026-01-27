<?php

namespace App\Repository\Services\Refund;

use App\Repository\Interfaces\CommonInterface;
use App\route;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use DB;

class RefundService
{

    protected $contact;

    /**
     * Get all contacts.
     *
     * @return array
     */
    public function getAllRefunds($page, $search)
    {
        try {
            $perPage = 10;
            $refunds = DB::table('refunds')
                ->join('bookings', 'refunds.booking_id', '=', 'bookings.id')
                ->join('trips', 'bookings.trip_id', '=', 'trips.id')
                ->where('trips.trip_name', 'like', '%' . $search . '%')
                ->select('refunds.*', 'trips.trip_name', 'bookings.seat_ids', 'bookings.created_at as booking_date')
                ->paginate($perPage, ['refunds.*'], 'page', $page);
            if ($refunds->total() > 0) {
                return array( "status" => true,"data" => $refunds,"message" => "Refund list retrieved successfully");
            } else {
                return array("status" => true, "data" => [], "message" => "No refund found");
            }
        } catch (Exception $ex) {
            Log::alert("refundService - getAllRefunds function" . $ex->getMessage());
            return array("status" => false, "data" => [], "message" => "No refund found");
        }
    }


   public function disburseRefund($data)
{
    DB::beginTransaction();

    try {
        $refundInfo = DB::table('refunds')
            ->join('bookings', 'refunds.booking_id', '=', 'bookings.id')
            ->join('payments', 'payments.booking_id', '=', 'bookings.id')
            ->select('refunds.*', 'payments.payment_method', 'payments.card', 'payments.bkash', 'payments.nagad')
            ->where('refunds.id', $data['refund_id'])
            ->first();

        if (!$refundInfo) {
            return ["status" => false, "data" => [], "message" => "Refund not found"];
        }

        $accountHistoryUpdateData = [
            'user_id' => Auth::id(),
            'user_account_type' => $refundInfo->payment_method,
            'user_account_no' => match ($refundInfo->payment_method) {
                'card'  => $refundInfo->card,
                'bkash' => $refundInfo->bkash,
                'nagad' => $refundInfo->nagad,
                default => $refundInfo->card,
            },
            'getaway' => $refundInfo->payment_method,
            'amount' => $refundInfo->amount,
            'com_account_no' => '01628781323',
            'transaction_reference' => random_int(9998889, 15000000),
            'transaction_type' => 'd',
            'purpose' => 'Refund Disbursement',
            'tran_date' => now(),
        ];

        $accountHistory = DB::table('account_history')->insert($accountHistoryUpdateData);

        if (!$accountHistory) {
            DB::rollBack();
            return ["status" => false, "data" => [], "message" => "Refund disbursement failed"];
        }

        $response = DB::table('refunds')
            ->where('id', $data['refund_id'])
            ->update(['status' => 'disbursed']);

        if (!$response) {
            DB::rollBack();
            return ["status" => false, "data" => [], "message" => "Refund status update failed"];
        }

        DB::commit();
        return ["status" => true, "data" => [], "message" => "Refund disbursed successfully"];

    } catch (\Exception $ex) {
        DB::rollBack();
        Log::alert("refundService - disburseRefund: " . $ex->getMessage());

        return [
            "status" => false,
            "data" => [],
            "message" => "Something went wrong while refund disbursement"
        ];
    }
}
}
