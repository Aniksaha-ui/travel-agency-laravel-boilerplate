<?php

namespace App\Repository\Services\Dashboard;

use Exception;
use Illuminate\Support\Facades\Log;
use DB;
use GuzzleHttp\Psr7\Request;
use Carbon\Carbon;

class DashboardService
{

    protected $contact;

    public function index()
    {
        try {
            $totalGuide = DB::table('guides')->count();
            $totalTours = DB::table('trips')->count();
            $totalPackage = DB::table('packages')->count();
            $totalVehicles = DB::table('vehicles')->count();
            $totalPayments = DB::table('payments')->count();
            $totalRoute = DB::table('routes')->count();
            $totalTransaction = DB::table('transactions')->count();
            $totalTable = DB::table('migrations')->count();
            $totalPackageBookings = DB::table('package_bookings')->count();
            $totalHotelBookings = DB::table('hotel_bookings')->count();
            $thisMonthTotalHotelBookings = DB::table('hotel_bookings')
                ->whereMonth('created_at', '=', date('m'))
                ->whereYear('created_at', '=', date('Y'))
                ->count();
            $totalBookings = DB::table('bookings')->count();
            $thisMonthTotalBookings = DB::table('bookings')
                ->whereMonth('created_at', '=', date('m'))
                ->whereYear('created_at', '=', date('Y'))
                ->count();

            $monthlyPayments = DB::table('payments')
                ->whereMonth('created_at', '=', date('m'))
                ->whereYear('created_at', '=', date('Y'))
                ->sum('amount');

            // origin wise trips count
            $tripData = DB::table('trips')
                ->join('routes', 'trips.route_id', '=', 'routes.id')
                ->select(DB::raw('COUNT(*) as trip_exist'), 'origin')
                ->groupBy('origin')
                ->get();

            //channel wise payment data
            $paymentData = DB::table('payments')
                ->select(DB::raw('SUM(amount) as total_amount'), DB::raw('COUNT(*) as payment_held'), 'payment_method')
                ->groupBy('payment_method')
                ->get();






            return [
                "totalGuide" => $totalGuide,
                "totalPackage" => $totalPackage,
                "totalRoute" => $totalRoute,
                "totalBookings" => $totalBookings,
                "thisMonthTotalBookings" => $thisMonthTotalBookings,
                "thisMonthTotalHotelBookings" => $thisMonthTotalHotelBookings,
                "totalHotelBookings" => $totalHotelBookings,
                "totalPackageBookings" => $totalPackageBookings,
                "totalTransaction" => $totalTransaction,
                "totalTable" => $totalTable,
                "totalTours" => $totalTours,
                "totalVehicles" => $totalVehicles,
                "totalPayments" => $totalPayments,
                "monthlyPayments" => $monthlyPayments,
                "tripData" => $tripData,
                "paymentData" => $paymentData
            ];
        } catch (Exception $ex) {
            Log::alert($ex->getMessage());
        }
    }


    public function databaseList()
    {
        try {
            $tables = DB::select('SHOW TABLES');

            $structure = [];

            foreach ($tables as $table) {
                $tableName = $table->{'Tables_in_' . env('DB_DATABASE')};
                $columns = DB::select('DESCRIBE ' . $tableName);
                $tableStructure = [];

                foreach ($columns as $column) {
                    $fieldName = $column->Field;
                    $fieldType = $this->getColumnType($column->Type);
                    $tableStructure[] = [
                        $fieldName => $fieldType
                    ];
                }
                $structure[] = [
                    $tableName => $tableStructure
                ];
            }

            // Return the structure in JSON format
            return ["status" => true, "data" => $structure, "message" => "Database list retrived successfully"];
        } catch (Exception $ex) {
            Log::alert($ex->getMessage());
        }
    }


    private function getColumnType($dbType)
    {
        // Check if the column type includes a size (e.g., varchar(255))
        if (strpos($dbType, '(') !== false) {
            $dbType = explode('(', $dbType)[0];
        }

        $typeMap = [
            'int' => 'integer',
            'varchar' => 'string',
            'text' => 'text',
            'datetime' => 'datetime',
            'timestamp' => 'timestamp',
            'float' => 'float',
            'decimal' => 'decimal',
            'boolean' => 'boolean',
            'tinyint' => 'boolean',
            'longtext' => 'longtext'
        ];

        return $typeMap[$dbType] ?? 'unknown';
    }
}
