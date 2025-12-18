<?php

namespace App\Http\Controllers\User\ticket;

use App\Http\Controllers\Controller;
use App\Repository\Services\Ticket\TicketService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ticketController extends Controller
{

    protected $ticketService;
    
    public function __construct(TicketService $ticketService)
    {
        $this->ticketService = $ticketService;
    }


    public function createTicket(Request $request){
    try {
                $response = $this->ticketService->store($request->all());
                if ($response['isExecute'] == true) {
                    return response()->json([
                        'isExecute' => $response['isExecute'],
                        'data' => $response['data'],
                        'message' => $response['message'],
                    ], 200);
                } else {
                    return response()->json([
                        'isExecute' => $response['isExecute'],
                        'data' => [],
                        'message' => $response['message'],
                    ], 200);
                }
            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json(['error' => 'Failed to create hotel', 'message' => $e->getMessage()], 500);
            }

        }

    public function ticketList(Request $request){
    try {
            $bookings = $this->ticketService->myTickets();
            return response()->json([
                "isExecute" => true,
                "data" => $bookings,
                "message" => "success"
            ], 200);
        } catch (Exception $ex) {
            Log::alert($ex->getMessage());
        }
    }
}
