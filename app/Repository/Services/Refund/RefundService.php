<?php

namespace App\Repository\Services\Refund;

use App\Repository\Interfaces\CommonInterface;
use App\route;
use Exception;
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
                ->paginate($perPage, ['refunds.*'], 'page', $page);
                if ($refunds->count() > 0) {
                    return ["status" => true, "data" => $refunds, "message" => "Refund list retrived successfully"];
            } else {
                return ["status" => true, "data" => [], "message" => "No refund found"];
            }
        } catch (Exception $ex) {
            Log::alert("refundService - getAllRefunds function" . $ex->getMessage());
        }
    }


    public function disburseRefund($data){
        try {
            $isExist = DB::table('refunds')->where('id', $data['refund_id'])->exists();
            if(!$isExist){
                return ["status" => false, "data" => [], "message" => "Refund not found"];
            }
           $response = DB::table('refunds')->where('id', $data['refund_id'])->update(['status' => 'disbursed']);
           if($response){
               return ["status" => true, "data" => [], "message" => "Refund disbursed successfully"];
           }else{
               return ["status" => false, "data" => [], "message" => "Refund disbursement failed"];
           } 
        } catch (Exception $ex) {
            Log::alert("refundService - disburseRefund function" . $ex->getMessage());
        }
    }
}