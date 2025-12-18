<?php

namespace App\Repository\Services\Notification;

use Exception;
use Illuminate\Support\Facades\Log;
use DB;

class NotificationService
{
    public function Notification($data)
    {
        DB::beginTransaction();

        try {
            // Insert hotel
   
            DB::commit();
            $notificationData = [
                'user_id' => $data['user_id'],
                'title' => $data['title'],
                'content' => $data['content'],
                'schedule_start' => $data['schedule_start'],
                'schedule_end' => $data['schedule_end'],
                'status' => $data['status'],
            ];


            $insertedValue = DB::table('user_notification_bell')->insertGetId($notificationData);

            return [
                'status' => true,
                'data' => $insertedValue,
                'message' => 'Notification sent successfully'
            ];
        } catch (Exception $e) {
            DB::rollBack();
            return [
                'status' => false,
                'message' => 'Failed to create hotel: ' . $e->getMessage()
            ];
        }
    }



    

}
