<?php

namespace App\Http\Controllers\Admin\transaction;

use App\Http\Controllers\Controller;
use App\Repository\Services\Transaction\TransactionService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class transactionController extends Controller
{
    private $transactionService;
    public function __construct(TransactionService $transactionService){
        $this->transactionService = $transactionService;
    }

    public function getTransactions(Request $request){
        try{    
            $page = $request->query('page');
            $search = $request->query('search');

            $response = $this->transactionService->transactions($page,$search);
           return response()->json([
                "data" => $response['data'],
                "status" => $response['status'],
                "message" => $response['message']
            ], 200);

        }catch(Exception $ex){
            Log::error($ex->getMessage());
        }
    }
}
