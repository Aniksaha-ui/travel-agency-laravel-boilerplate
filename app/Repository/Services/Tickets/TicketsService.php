<?php

namespace App\Repository\Services\Tickets;

use App\Constants\NotificationStatus;
use App\Constants\TicketStatus;
use App\Helpers\admin\FileManageHelper;
use App\Repository\Services\Notification\NotificationService;
use Exception;
use Illuminate\Support\Facades\Log;
use DB;
use Illuminate\Support\Facades\Auth;
class TicketsService
{

    private $notificationService;

  public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;  
 }  

  public function getTicketsList($page,$search){
        try {
            $perPage = 10;
            $tickets = DB::table('tickets')
                ->join('users as user', 'tickets.generate_by', '=', 'user.id')
                ->leftJoin('users as resolved_user', 'tickets.resolved_by', '=', 'resolved_user.id')
                ->where('title', 'like', '%' . $search . '%')
                ->paginate($perPage, ['tickets.*','user.name as generate_by_name','resolved_user.name as resolved_user_name'], 'page', $page);
            if ($tickets->count() > 0) {
                return ["status" => true, "data" => $tickets, "message" => "Tickets list retrived successfully"];
            } else {
                return ["status" => true, "data" => [], "message" => "No Ticket found"];
            }
        } catch (Exception $ex) {
            Log::info("TicketsService getTicketsList functions" . $ex->getMessage());
        }
    }

    public function updateTicketStatus($ticketId,$statusInfo){

        try {
            $ticket = DB::table('tickets')->where('id', $ticketId)->first();
            if (!$ticket) {
                return ["status" => false, "message" => "Ticket not found"];
            }

            $notificationData =[
                'title' => 'Ticket Status Updated',
                'content' => 'Your ticket ' . $ticket->title . ' status has been updated to ' . TicketStatus::labels()[$statusInfo],
                'user_id' => $ticket->generate_by,
                'schedule_start' => now(),
                'schedule_end' => now()->addDays(30),
                'status' => NotificationStatus::ACTIVE,
            ];

            $updateData = [
                'status' => $statusInfo,
                'resolved_by' => Auth::id(),
                'updated_at' => now(),
            ];

            DB::table('tickets')->where('id', $ticketId)->update($updateData);
            $notification = $this->notificationService->Notification($notificationData);
            Log::info("Ticket Notification status: " . json_encode($notification));

            return ["status" => true, "message" => "Ticket status updated successfully"];
        } catch (Exception $ex) {
            Log::info("TicketsService updateTicketStatus functions" . $ex->getMessage());
            return ["status" => false, "message" => "An error occurred while updating the ticket status"];
        }


    }




}
