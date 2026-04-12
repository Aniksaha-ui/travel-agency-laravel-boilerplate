<?php

namespace App\Repository\Services\Visa;

use App\Constants\ApiResponseStatus;
use App\Constants\NotificationStatus;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class VisaNotificationBellService
{
    public function create($userId, $title, $content, $referenceId = null)
    {
        try {
            $notificationId = DB::table('user_notification_bell')->insertGetId([
                'user_id' => $userId,
                'title' => $title,
                'content' => $content,
                'schedule_start' => now(),
                'schedule_end' => now()->addDays(30),
                'status' => NotificationStatus::ACTIVE,
                'type' => 'visa',
                'reference_type' => 'visa_application',
                'reference_id' => $referenceId,
                'is_read' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return [
                'status' => ApiResponseStatus::SUCCESS,
                'data' => $notificationId,
                'message' => 'Notification created successfully',
            ];
        } catch (Exception $exception) {
            Log::error('VisaNotificationBellService create error: ' . $exception->getMessage());

            return [
                'status' => ApiResponseStatus::FAILED,
                'data' => [],
                'message' => 'Failed to create notification',
            ];
        }
    }

    public function listByUser($userId, $page)
    {
        try {
            $notifications = DB::table('user_notification_bell')
                ->where('user_id', $userId)
                ->where('type', 'visa')
                ->orderBy('id', 'desc')
                ->paginate(10, ['*'], 'page', $page);

            return [
                'status' => ApiResponseStatus::SUCCESS,
                'data' => $notifications,
                'message' => $notifications->total() > 0 ? 'Visa notifications retrieved successfully' : 'No visa notifications found',
            ];
        } catch (Exception $exception) {
            Log::error('VisaNotificationBellService listByUser error: ' . $exception->getMessage());

            return [
                'status' => ApiResponseStatus::FAILED,
                'data' => [],
                'message' => 'Failed to retrieve visa notifications',
            ];
        }
    }

    public function markAsRead($userId, $notificationId)
    {
        try {
            $notification = DB::table('user_notification_bell')
                ->where('id', $notificationId)
                ->where('user_id', $userId)
                ->where('type', 'visa')
                ->first();

            if (!$notification) {
                return [
                    'status' => ApiResponseStatus::FAILED,
                    'data' => [],
                    'message' => 'Notification not found',
                ];
            }

            DB::table('user_notification_bell')
                ->where('id', $notificationId)
                ->update([
                    'is_read' => 1,
                    'read_at' => now(),
                    'updated_at' => now(),
                ]);

            return [
                'status' => ApiResponseStatus::SUCCESS,
                'data' => [],
                'message' => 'Notification marked as read',
            ];
        } catch (Exception $exception) {
            Log::error('VisaNotificationBellService markAsRead error: ' . $exception->getMessage());

            return [
                'status' => ApiResponseStatus::FAILED,
                'data' => [],
                'message' => 'Failed to update notification',
            ];
        }
    }
}
