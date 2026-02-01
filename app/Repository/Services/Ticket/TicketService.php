<?php

namespace App\Repository\Services\Ticket;

use App\Helpers\admin\FileManageHelper;
use Exception;
use Illuminate\Support\Facades\Log;
use DB;
use Illuminate\Support\Facades\Auth;

class TicketService
{
    public function store($ticketData)
    {
        DB::beginTransaction();

        try {
            if (request()->hasFile('attachment')) {
                $documentLink = FileManageHelper::uploadFile('tickets', $ticketData['attachment']);
                $ticketData['attachment'] = $documentLink;
            } else {
                $ticketData['attachment'] = '';
            }

            $ticketData['created_at'] = now();
            $ticketData['generate_by'] = Auth::id();
            $ticketInsert = DB::table('tickets')->insert($ticketData);

            if($ticketInsert){

                DB::commit();
                return [
                    'isExecute' => true,
                    'message' => 'Ticket created successfully.',
                    'data'=> []
                ];
            }else {
                    return [
                    'isExecute' => true,
                    'data'=> [],
                    'message' => 'Ticket can not created successfully.'
                ];
            }

        } catch (Exception $e) {
            Log::info("error in TicketService : store function" .$e->getMessage());
            DB::rollBack();
            return [
                'status' => false,
                'message' => 'Failed to create ticket: ' . $e->getMessage()
            ];
        }
    }



    public function myTickets()
    {
        try {
                $userId = Auth::id();
                $ticketInformation = DB::table('tickets')
                                        ->leftJoin('users as resolved_user', 'tickets.resolved_by', '=', 'resolved_user.id')
                                        ->where('tickets.generate_by', $userId)
                                        ->select([
                                            'tickets.id',
                                            'tickets.title',
                                            'tickets.description',
                                            'tickets.generate_by',
                                            'tickets.status',
                                            'tickets.remarks',
                                            'tickets.attachment',
                                            'tickets.created_at',
                                            'tickets.updated_at',
                                            DB::raw('resolved_user.name as resolved_by'),
                                        ])
                                        ->where('generate_by',$userId)
                                        ->get();
                    
                return $ticketInformation;

            } catch (Exception $ex) {
                Log::alert($ex->getMessage());
            }
    }







    

}
