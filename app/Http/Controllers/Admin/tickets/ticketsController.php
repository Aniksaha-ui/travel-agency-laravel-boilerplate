<?php

namespace App\Http\Controllers\Admin\tickets;

use App\Http\Controllers\Controller;
use App\Repository\Services\Tickets\TicketService;
use App\Repository\Services\Tickets\TicketService as TicketsTicketService;
use App\Repository\Services\Tickets\TicketsService;
use Illuminate\Http\Request;

class ticketsController extends Controller
{
       protected $ticketService;
    public function __construct(TicketsService $ticketService)
    {
        $this->ticketService = $ticketService;
    }

    
 public function getTickets(Request $request)
    {
        $page = $request->query('page');
        $search = $request->query('search');

        $response = $this->ticketService->getTicketsList($page, $search);
        return response()->json([
            "data" => $response,
            "message" => "success"
        ], 200);
    }


     public function updateTicket($id, Request $request)
    {
      
        $ticketId = $id;
        $status = $request->input('status');
        $resolvedStatus = $request->input('resolved_status');
        $response = $this->ticketService->updateTicketStatus($ticketId, $status,$resolvedStatus);
        return response()->json([
            "data" => $response,
            "message" => "success"
        ], 200);
    }
}
