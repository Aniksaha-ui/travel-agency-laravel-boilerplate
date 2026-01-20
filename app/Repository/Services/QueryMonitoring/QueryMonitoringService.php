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
            $response = [
                'request_report'    => [],
                'slow_queries'      => [],
                'route_report'      => [],
                'controller_report' => [],
                'user_report'       => [],
                'daily_report'      => [],
                'latest_logs'       => [],
            ];
            $fromDate = QueryLogFilter::fromDate();
            $showEntries = config('query_monitor.days');


            /** 1️⃣ Request-wise performance (latest 5 requests) */
            $report = $this->getReport($fromDate, $showEntries);

            if ($report->total() > 0) {
                $response['request_report'] = $report;
            }

            /** 2️⃣ Slow queries (latest 5) */
            $slowQueries = $this->getSlowQueries($fromDate, $showEntries);
            if ($slowQueries->total() > 0) {
                $response['slow_queries'] = $slowQueries;
            }

            /** 3️⃣ Route-wise performance (latest 5 routes) */
            $routeReport = $this->getRouteReport($fromDate, $showEntries);
            if ($routeReport->total() > 0) {
                $response['route_report'] = $routeReport;
            }

            /** 4️⃣ Controller-wise load (latest 5 controllers) */
            $controllerReport = $this->getControllerReport($fromDate, $showEntries);
            if ($controllerReport->total() > 0) {
                $response['controller_report'] = $controllerReport;
            }

            /** 5️⃣ User-wise usage (latest 5 users) */
            $userReport = $this->getUserReport($fromDate, $showEntries);
            if ($userReport->total() > 0) {
                $response['user_report'] = $userReport;
            }

            /** 6️⃣ Daily summary (latest 5 days) */
            $dailyReport = $this->getDailyReport($fromDate, $showEntries);
            if ($dailyReport->total() > 0) {
                $response['daily_report'] = $dailyReport;
            }

            /** 7️⃣ Latest raw logs (latest 5 rows) */
            $logs = $this->getLatestLogs($fromDate, $showEntries);
            if ($logs->total() > 0) {
                $response['latest_logs'] = $logs;
            }


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

    function getReport($fromDate, $showEntries)
    {
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
            ->limit($showEntries)
            ->get();


        return $report;
    }

    public function getSlowQueries($fromDate, $showEntries)
    {
        $slowQuery =  DB::table('query_logs')
            ->where('is_slow', 1)
            ->where('created_at', '>=', $fromDate)
            ->where('route', '!=', 'api/admin/monitoring')
            ->orderByDesc('created_at')
            ->limit($showEntries)
            ->get();

        return $slowQuery;
    }


    public function getRouteReport($fromDate, $showEntries)
    {
        $routeReport =  DB::table('query_logs')
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
            ->limit($showEntries)
            ->get();
        return $routeReport;
    }

    public function getControllerReport($fromDate, $showEntries)
    {
        $controllerReport =  DB::table('query_logs')
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
            ->limit($showEntries)
            ->get();
        return $controllerReport;
    }

    public function getUserReport($fromDate, $showEntries)
    {
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
            ->limit($showEntries)
            ->get();
        return $userReport;
    }

    public function getDailyReport($fromDate, $showEntries)
    {
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
            ->limit($showEntries)
            ->get();
        return $dailyReport;
    }


    public function getLatestLogs($fromDate, $showEntries)
    {
        $logs = DB::table('query_logs')
            ->where('route', '!=', 'api/admin/monitoring')
            ->where('created_at', '>=', $fromDate)
            ->orderByDesc('created_at')
            ->limit($showEntries)
            ->get();

        return $logs;
    }
}
