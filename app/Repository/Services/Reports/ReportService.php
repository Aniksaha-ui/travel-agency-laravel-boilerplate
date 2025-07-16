<?php

namespace App\Repository\Services\Reports;

use Exception;
use Illuminate\Support\Facades\Log;
use DB;

class ReportService
{

    protected $contact;

    /**
     * Get all contacts.
     *
     * @return bool
     */
    public function vehicleWiseSeatTotalReport($page, $search)
    {

        try {
            $perPage = 10;
            $report = DB::table('vehicles')
                ->leftJoin('seats', 'seats.vehicle_id', '=', 'vehicles.id')
                ->select('vehicles.id as vehicle_id', 'vehicles.vehicle_name', 'vehicle_type', DB::raw('COUNT(seats.id) as available_seats'))
                ->when($search, function ($query, $search) {
                    return $query->where('vehicles.vehicle_name', 'like', '%' . $search . '%');
                })
                ->groupBy('vehicles.id', 'vehicles.vehicle_name')
                ->paginate($perPage, ['*'], 'page', $page);

            return $report;
        } catch (Exception $ex) {
            Log::alert($ex->getMessage());
        }
    }

    public function vehicleWiseAllSeatReport($vehicleId, $page, $search)
    {
        try {
            $perPage = 10;
            $report = DB::table('vehicles')
                ->join('seats', 'seats.vehicle_id', '=', 'vehicles.id')
                ->where('seat_number', 'like', '%' . $search . '%')
                ->where('vehicles.id', $vehicleId)
                ->get();
            return $report;
        } catch (Exception $ex) {
            Log::alert($ex->getMessage());
        }
    }

    public function accountBalance()
    {
        try {
            $accountInformation = DB::table('company_accounts')->get();
            Log::info(json_encode($accountInformation));
            return $accountInformation;
        } catch (Exception $ex) {
            Log::alert($ex->getMessage());
        }
    }

    public function accountHistory($userAccountType)
    {
        try {
            $accountHistory = DB::table('account_history')->where('user_account_type', $userAccountType)->get();
            return $accountHistory;
        } catch (Exception $ex) {
            Log::alert($ex->getMessage());
        }
    }

    public function packageWiseBookingReport()
    {
        try {
            $report =  DB::table('package_bookings as pb')
                ->join('packages as p', 'pb.package_id', '=', 'p.id')
                ->select(
                    'pb.package_id',
                    'p.name as package_name',
                    DB::raw('COUNT(pb.id) as number_of_bookings'),
                    DB::raw('SUM(pb.total_adult) as total_adult'),
                    DB::raw('SUM(pb.total_child) as total_child')
                )
                ->groupBy('pb.package_id', 'p.name')
                ->orderByDesc('number_of_bookings')
                ->get();
            if ($report->count() > 0) {
                return ["status" => true, "data" => $report, "message" => "Report retrieved successfully"];
            } else {
                return ["status" => true, "data" => [], "message" => "No Report found"];
            }
        } catch (Exception $ex) {
            Log::alert($ex->getMessage());
            return ["status" => false, "data" => [], "message" => "server error"];
        }
    }


    function useageOfVehicle($page, $search, $start_date, $end_date)
    {
        try {
            $perPage = 10;
            $query = DB::table('vehicle_trip_trackings')
                ->join('vehicles', 'vehicle_trip_trackings.vehicle_id', '=', 'vehicles.id')
                ->join('trips', 'vehicle_trip_trackings.trip_id', '=', 'trips.id');
            if ($start_date && $end_date) {
                $query = $query->whereBetween('vehicle_trip_trackings.travel_start_date', [$start_date, $end_date]);
            }
            $query = $query->when($search, function ($query, $search) {
                return $query->where('vehicles.vehicle_name', 'like', '%' . $search . '%')
                    ->orWhere('trips.trip_name', 'like', '%' . $search . '%');
            });
            $report = $query->paginate($perPage, ['vehicle_trip_trackings.*', 'trips.trip_name', 'vehicles.vehicle_name'], 'page', $page);
            if ($report->count() > 0) {
                return ["status" => true, "data" => $report, "message" => "Report retrieved successfully"];
            } else {
                return ["status" => true, "data" => [], "message" => "No report found"];
            }
        } catch (Exception $ex) {
            Log::alert($ex->getMessage());
            return ["status" => false, "data" => [], "message" => "server error"];
        }
    }


    public function tripwiseBookingUsers($tripId)
    {
        try {
            $report = DB::table('bookings')
                ->join('users', 'bookings.user_id', '=', 'users.id')
                ->where('bookings.trip_id', $tripId)
                ->where('bookings.status', '!=', 'cancelled')
                ->select('users.name', 'users.email', 'bookings.created_at as booking_date', 'bookings.status')
                ->get();

            if ($report->count() > 0) {
                return ["status" => true, "data" => $report, "message" => "Report retrieved successfully"];
            } else {
                return ["status" => true, "data" => [], "message" => "No Report found"];
            }
        } catch (Exception $ex) {
            Log::alert($ex->getMessage());
            return ["status" => false, "data" => [], "message" => "server error"];
        }
    }

    public function tripPerformance()
    {
        try {
            $report =  DB::table('trips as t')
                ->leftJoin(DB::raw('(
        SELECT b.trip_id, COUNT(DISTINCT bs.seat_id) AS total_seats_booked
        FROM bookings b
        JOIN booking_seats bs ON bs.booking_id = b.id
        GROUP BY b.trip_id
    ) as bs'), 'bs.trip_id', '=', 't.id')

                ->leftJoin(DB::raw('(
        SELECT trip_id, COUNT(*) AS total_seats_available
        FROM seat_availablities
        WHERE is_available = 1
        GROUP BY trip_id
    ) as sa'), 'sa.trip_id', '=', 't.id')

                ->leftJoin(DB::raw('(
        SELECT b.trip_id, SUM(p.amount) AS total_paid_amount
        FROM payments p
        JOIN bookings b ON p.booking_id = b.id
        GROUP BY b.trip_id
    ) as pay'), 'pay.trip_id', '=', 't.id')

                ->leftJoin(DB::raw('(
        SELECT trip_id, SUM(cost_amount) AS total_cost
        FROM trip_package_costings
        GROUP BY trip_id
    ) as tc'), 'tc.trip_id', '=', 't.id')

                ->select(
                    't.id as trip_id',
                    't.trip_name',
                    't.departure_time',
                    't.arrival_time',
                    DB::raw('IFNULL(bs.total_seats_booked, 0) as total_seats_booked'),
                    DB::raw('IFNULL(sa.total_seats_available, 0) as total_seats_available'),
                    DB::raw('IFNULL(pay.total_paid_amount, 0) as total_income'),
                    DB::raw('IFNULL(tc.total_cost, 0) as total_cost'),
                    DB::raw('(IFNULL(pay.total_paid_amount, 0) - IFNULL(tc.total_cost, 0)) as profit')
                )
                ->get();

            if ($report->count() > 0) {
                return ["status" => true, "data" => $report, "message" => "Report retrieved successfully"];
            } else {
                return ["status" => true, "data" => [], "message" => "No Report found"];
            }
        } catch (Exception $ex) {
            Log::alert($ex->getMessage());
            return ["status" => false, "data" => [], "message" => "server error"];
        }
    }


    public function packagePerformance()
    {
        try {
            $report =  DB::table('packages as p')
                ->leftJoin(DB::raw('(
        SELECT 
            package_id,
            COUNT(*) AS total_bookings,
            SUM(total_cost) AS total_income
        FROM package_bookings
        GROUP BY package_id
    ) as pb_data'), 'pb_data.package_id', '=', 'p.id')

                ->leftJoin(DB::raw('(
        SELECT 
            package_id,
            SUM(cost_amount) AS total_expense
        FROM trip_package_costings
        GROUP BY package_id
    ) as tc_data'), 'tc_data.package_id', '=', 'p.id')

                ->select(
                    'p.id as package_id',
                    'p.name as package_name',
                    DB::raw('COALESCE(pb_data.total_bookings, 0) as total_bookings'),
                    DB::raw('COALESCE(pb_data.total_income, 0) as total_income'),
                    DB::raw('COALESCE(tc_data.total_expense, 0) as total_expense'),
                    DB::raw('(COALESCE(pb_data.total_income, 0) - COALESCE(tc_data.total_expense, 0)) as net_profit')
                )
                ->get();

            if ($report->count() > 0) {
                return ["status" => true, "data" => $report, "message" => "Report retrieved successfully"];
            } else {
                return ["status" => true, "data" => [], "message" => "No Report found"];
            }
        } catch (Exception $ex) {
            return response()->json([
                "data" => [],
                "status" => false,
                "message" => "Internal Server Error"
            ], 500);
        }
    }


    public function guideEfficencyReport()
    {
        try {
            $report = DB::table('users as u')
                ->join('guide_packages as gp', 'gp.guide_id', '=', 'u.id')
                ->leftJoin('guide_performances as gperf', 'gperf.guide_id', '=', 'u.id')
                ->leftJoin('trip_package_costings as tpc', 'tpc.guide_id', '=', 'u.id')
                ->select(
                    'u.id as guide_id',
                    'u.name as guide_name',
                    DB::raw('COUNT(DISTINCT gp.package_id) as total_packages'),
                    DB::raw('ROUND(AVG(gperf.rating), 2) as avg_rating'),
                    DB::raw('COALESCE(SUM(tpc.cost_amount), 0) as total_trip_cost')
                )
                ->groupBy('u.id', 'u.name') // Include name if strict SQL mode is enabled
                ->get();
            if ($report->count() > 0) {
                return ["status" => true, "data" => $report, "message" => "Report retrieved successfully"];
            } else {
                return ["status" => true, "data" => [], "message" => "No Report found"];
            }
        } catch (Exception $ex) {
            Log::alert($ex->getMessage());
            return ["status" => false, "data" => [], "message" => "server error"];
        }
    }
    public function customerValueReport()
    {
        try {
            $report =  DB::table('users as u')
                ->leftJoin('bookings as b', 'u.id', '=', 'b.user_id')
                ->leftJoin('payments as p', 'p.booking_id', '=', 'b.id')
                ->leftJoin('refunds as r', 'r.booking_id', '=', 'b.id')
                ->leftJoin('package_bookings as pb', 'pb.user_id', '=', 'u.id')
                ->select(
                    'u.id as user_id',
                    'u.name',
                    DB::raw('COUNT(DISTINCT b.id) as total_trip_bookings'),
                    DB::raw('COUNT(DISTINCT pb.id) as total_package_bookings'),
                    DB::raw('COALESCE(SUM(p.amount), 0) as total_paid'),
                    DB::raw('COALESCE(SUM(r.amount), 0) as total_refunded'),
                    DB::raw('(COALESCE(SUM(p.amount), 0) - COALESCE(SUM(r.amount), 0)) as net_spent')
                )
                ->where('role', 'users')
                ->orderBy('net_spent', 'desc')
                ->groupBy('u.id', 'u.name') // include 'u.name' if using strict SQL mode
                ->get();
            if ($report->count() > 0) {
                return ["status" => true, "data" => $report, "message" => "Report retrieved successfully"];
            } else {
                return ["status" => true, "data" => [], "message" => "No Report found"];
            }
        } catch (Exception $ex) {
            Log::alert($ex->getMessage());
            return ["status" => false, "data" => [], "message" => "server error"];
        }
    }


    public function transactionHistoryReport()
    {
        try {
            $report = DB::table('transactions as t')
                ->join('payments as p', 't.payment_id', '=', 'p.id')
                ->select(
                    't.*',
                    'p.amount',
                    'p.payment_method'
                )
                ->whereMonth('t.created_at', '=', date('m'))
                ->whereYear('t.created_at', '=', date('Y'))
                ->orderBy('t.created_at', 'desc')
                ->get();
            if ($report->count() > 0) {
                return ["status" => true, "data" => $report, "message" => "Report retrieved successfully"];
            } else {
                return ["status" => true, "data" => [], "message" => "No Report found"];
            }
        } catch (Exception $ex) {
            Log::alert($ex->getMessage());
            return ["status" => false, "data" => [], "message" => "server error"];
        }
    }

    public function monthRunningBalanceReport()
    {
        try {
            // Fetch the monthly summary data
            $report = DB::select("
            SELECT
                DATE_FORMAT(tran_date, '%Y-%m') AS month,
                COUNT(*) AS tx_count,
                SUM(CASE WHEN transaction_type = 'c' THEN amount ELSE 0 END) AS total_credit,
                SUM(CASE WHEN transaction_type = 'd' THEN amount ELSE 0 END) AS total_debit
            FROM account_history
            GROUP BY DATE_FORMAT(tran_date, '%Y-%m')
            ORDER BY month
        ");

            if ($report) {
                // Manually calculate the running balance
                $runningBalance = 0;
                foreach ($report as $index => $data) {
                    // Calculate net change
                    $netChange = $data->total_credit - $data->total_debit;

                    // Opening balance is the previous balance (or 0 for the first month)
                    $openingBalance = $index == 0 ? 0 : $runningBalance;

                    // Closing balance (running balance)
                    $closingBalance = $runningBalance + $netChange;

                    // Store the results back to the report
                    $data->opening_balance = $openingBalance;
                    $data->closing_balance = $closingBalance;

                    // Update running balance for the next month
                    $runningBalance = $closingBalance;
                }

                return [
                    "status" => true,
                    "data" => $report,
                    "message" => "Report retrieved successfully"
                ];
            } else {
                return [
                    "status" => true,
                    "data" => [],
                    "message" => "No Report found"
                ];
            }
        } catch (Exception $ex) {
            Log::alert('Running Balance Error: ' . $ex->getMessage());
            return [
                "status" => false,
                "data" => [],
                "message" => "Server error occurred while generating the report"
            ];
        }
    }

    public function dailyBalanceReport()
    {
        try {
            // Fetch the daily summary data
            $report = DB::select("
    WITH daily_summary AS (
        SELECT
            DATE(ah.tran_date) AS date,
            COUNT(*) AS tx_count,
            SUM(CASE WHEN ah.transaction_type = 'c' THEN ah.amount ELSE 0 END) AS total_credit,
            SUM(CASE WHEN ah.transaction_type = 'd' THEN ah.amount ELSE 0 END) AS total_debit
        FROM account_history ah
        WHERE MONTH(ah.tran_date) = MONTH(CURDATE())
            AND YEAR(ah.tran_date) = YEAR(CURDATE())
        GROUP BY DATE(ah.tran_date)
    )
    SELECT
        date,
        tx_count,
        total_credit,
        total_debit,
        SUM(total_credit - total_debit) OVER (ORDER BY date ASC) AS balance
    FROM daily_summary
    ORDER BY date ASC
");

            if ($report) {
                return [
                    "status" => true,
                    "data" => $report,
                    "message" => "Report retrieved successfully"
                ];
            } else {
                return [
                    "status" => true,
                    "data" => [],
                    "message" => "No Report found"
                ];
            }
        } catch (Exception $ex) {
            Log::alert('Daily Balance Error: ' . $ex->getMessage());
            return [
                "status" => false,
                "data" => [],
                "message" => "Server error occurred while generating the report"
            ];
        }
    }
}
