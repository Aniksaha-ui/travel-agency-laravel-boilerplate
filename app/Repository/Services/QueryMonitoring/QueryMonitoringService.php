<?php

namespace App\Repository\Services\QueryMonitoring;

use App\Constants\ApiResponseStatus;
use App\Helpers\admin\QueryLogFilter;
use Exception;
use Illuminate\Support\Facades\Log;
use DB;

class QueryMonitoringService
{

    public function getQueryPerformance()
    {
        try {

            $fromDate = QueryLogFilter::fromDate();

            /** 1️⃣ Request-wise performance (latest 5 requests) */
            $report = DB::table('query_logs')
                ->select(
                    'request_id',
                    'controller',
                    'route',
                    'method',
                    'url',
                    DB::raw('COUNT(*) as total_queries'),
                    DB::raw('SUM(time_ms) as total_time_ms'),
                    DB::raw('MAX(created_at) as last_seen')
                )
                ->where('created_at', '>=', $fromDate)
                ->where('route', '!=', 'api/admin/monitoring')
                ->groupBy(
                    'request_id',
                    'controller',
                    'route',
                    'method',
                    'url'
                )
                ->orderByDesc('last_seen')
                ->limit(5)
                ->get();

            /** 2️⃣ Slow queries (latest 5) */
            $slowQueries = DB::table('query_logs')
                ->where('is_slow', 1)
                ->where('created_at', '>=', $fromDate)
                ->where('route', '!=', 'api/admin/monitoring')
                ->orderByDesc('created_at')
                ->limit(5)
                ->get();

            /** 3️⃣ Route-wise performance (latest 5 routes) */
            $routeReport = DB::table('query_logs')
                ->select(
                    'route',
                    DB::raw('COUNT(*) as total_queries'),
                    DB::raw('AVG(time_ms) as avg_time'),
                    DB::raw('MAX(time_ms) as max_time'),
                    DB::raw('MAX(created_at) as last_seen')
                )
                ->whereNotNull('route')
                ->where('created_at', '>=', $fromDate)
                ->where('route', '!=', 'api/admin/monitoring')
                ->groupBy('route')
                ->orderByDesc('last_seen')
                ->limit(5)
                ->get();

            /** 4️⃣ Controller-wise load (latest 5 controllers) */
            $controllerReport = DB::table('query_logs')
                ->select(
                    'controller',
                    DB::raw('COUNT(*) as total_queries'),
                    DB::raw('SUM(time_ms) as total_time'),
                    DB::raw('MAX(created_at) as last_seen')
                )
                ->whereNotNull('controller')
                ->where('route', '!=', 'api/admin/monitoring')
                ->where('created_at', '>=', $fromDate)
                ->groupBy('controller')
                ->orderByDesc('last_seen')
                ->limit(5)
                ->get();

            /** 5️⃣ User-wise usage (latest 5 users) */
            $userReport = DB::table('query_logs')
                ->select(
                    'user_id',
                    DB::raw('COUNT(*) as total_queries'),
                    DB::raw('SUM(time_ms) as total_time'),
                    DB::raw('MAX(created_at) as last_seen')
                )
                ->whereNotNull('user_id')
                ->where('route', '!=', 'api/admin/monitoring')
                ->where('created_at', '>=', $fromDate)
                ->groupBy('user_id')
                ->orderByDesc('last_seen')
                ->limit(5)
                ->get();

            /** 6️⃣ Daily summary (latest 5 days) */
            $dailyReport = DB::table('query_logs')
                ->select(
                    DB::raw('DATE(created_at) as date'),
                    DB::raw('COUNT(*) as total_queries'),
                    DB::raw('SUM(time_ms) as total_time')
                )
                ->where('route', '!=', 'api/admin/monitoring')
                ->where('created_at', '>=', $fromDate)
                ->groupBy(DB::raw('DATE(created_at)'))
                ->orderByDesc('date')
                ->limit(5)
                ->get();

            /** 7️⃣ Latest raw logs (latest 5 rows) */
            $logs = DB::table('query_logs')
                ->where('route', '!=', 'api/admin/monitoring')
                ->where('created_at', '>=', $fromDate)
                ->orderByDesc('created_at')
                ->limit(5)
                ->get();

            $response = [
                'request_report'    => $report,
                'slow_queries'      => $slowQueries,
                'route_report'      => $routeReport,
                'controller_report' => $controllerReport,
                'user_report'       => $userReport,
                'daily_report'      => $dailyReport,
                'latest_logs'       => $logs,
            ];

            return [
                "status"  => ApiResponseStatus::SUCCESS,
                "data"    => $response,
                "message" => "Latest query monitoring data retrieved successfully"
            ];
        } catch (\Exception $ex) {
            Log::error("QueryPerformance error: " . $ex->getMessage());

            return [
                "status"  => ApiResponseStatus::FAILED,
                "data"    => [],
                "message" => "Internal server error"
            ];
        }
    }
}
