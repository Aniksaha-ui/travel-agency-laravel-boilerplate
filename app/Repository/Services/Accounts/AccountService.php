<?php

namespace App\Repository\Services\Accounts;

use App\Constants\ApiResponseStatus;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Log;
use DB;


class AccountService
{

    protected $contact;

    public function accountHistoryList($page, $search,$dateRange)
    {
        try {
            $perPage = 10;
             $startDate = Carbon::parse($dateRange['start_date'])->startOfDay();
            $endDate   = Carbon::parse($dateRange['end_date'])->addDay()->startOfDay();

            $accountHistoryList = DB::table('account_history')
                ->where('tran_date', '>=', $startDate)
                ->where('tran_date', '<', $endDate)
                ->when($search, function ($query) use ($search) {
                    $query->where(function ($q) use ($search) {
                        $q->where('transaction_reference', 'like', "%{$search}%")
                        ->orWhere('getaway', 'like', "%{$search}%")
                        ->orWhere('transaction_type', 'like', "%{$search}%")
                        ->orWhere('purpose', 'like', "%{$search}%");
                    });
                })
                ->orderBy('account_history.id', 'desc')
                ->paginate($perPage, ['account_history.*'], 'page', $page);
         
            if($accountHistoryList->total() > 0){
                return ["status" => ApiResponseStatus::SUCCESS, "data" => $accountHistoryList, "message" => config("message.app_data_retrieved")];
            }
            return ["status" => ApiResponseStatus::FAILED, "data" => [], "message" => config("message.no_data_found")];


        } catch (Exception $ex) {
            Log::alert("bookingService-index function" . $ex->getMessage());
            return ["status" => false, "data" => [], "message" => config("message.server_error")];

        }
    }

}
