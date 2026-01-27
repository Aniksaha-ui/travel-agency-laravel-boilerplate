<?php

namespace App\Http\Controllers\Admin\account;

use App\Http\Controllers\Controller;
use App\Repository\Services\Accounts\AccountService;
use Exception;
use Illuminate\Http\Request;

class AccountHistoryController extends Controller
{


      protected $accountService;
    public function __construct(AccountService $accountService)
    {
        $this->accountService = $accountService;
    }
    public function accountHistory(Request $request){
        try{
                $page = $request->query('page');
            $search = $request->query('search');
            $dateRange = ['start_date' => $request['start_date'],'end_date'=>$request['end_date']];
          

            $accountHistory = $this->accountService->accountHistoryList($page, $search,$dateRange);
            return response()->json([
                "isExecute" => $accountHistory['status'],
                "data" => $accountHistory['data'],
                "message" => $accountHistory['message']
            ], 200);
        }catch(Exception $ex){

        }
    }
}
