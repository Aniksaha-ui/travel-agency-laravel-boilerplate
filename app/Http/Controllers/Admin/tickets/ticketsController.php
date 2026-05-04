<?php

namespace App\Http\Controllers\Admin\tickets;

use App\Http\Controllers\Controller;
use App\Repository\Services\Tickets\TicketsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ticketsController extends Controller
{
    protected $ticketService;

    public function __construct(TicketsService $ticketService)
    {
        $this->ticketService = $ticketService;
    }

    public function getTickets(Request $request)
    {
        try {
            $page = $request->query('page');
            $search = $request->query('search');

            $response = $this->ticketService->getTicketsList($page, $search);
            return $this->serviceResponse($response);
        } catch (\Exception $ex) {
            Log::error("ticketsController getTickets: " . $ex->getMessage());
            return $this->failedResponse();
        }
    }

    public function updateTicket($id, Request $request)
    {
        try {
            $ticketId = $id;
            $status = $request->input('status');
            $resolvedStatus = $request->input('resolved_status');
            $response = $this->ticketService->updateTicketStatus($ticketId, $status, $resolvedStatus);
            return $this->serviceResponse($response);
        } catch (\Exception $ex) {
            Log::error("ticketsController updateTicket: " . $ex->getMessage());
            return $this->failedResponse();
        }
    }
}
