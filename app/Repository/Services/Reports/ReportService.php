<?php

namespace App\Repository\Services\Reports;

use App\Constants\ApiResponseStatus;
use App\Constants\BookingStatus;
use App\Constants\BookingType;
use Carbon\Carbon;
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
            $accountHistory = DB::table('account_history')->where('user_account_type', $userAccountType)->OrderBy('id','desc')->paginate(20);
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
            $report = $query->paginate($perPage, ['vehicle_trip_trackings.*', 'trips.trip_name', 'vehicles.vehicle_name', 'trips.arrival_at', 'trips.departure_at'], 'page', $page);
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
                ->join('trips','bookings.trip_id','=','trips.id')
                ->where('bookings.trip_id', $tripId)
                ->where('bookings.status', '=', BookingStatus::PAID)
                ->select('users.name', 'users.email', 'bookings.created_at as booking_date', 'bookings.status', "bookings.seat_ids","trips.price")
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

    public function tripPerformance($page, $search)
    {
        try {
            $perPage = 10;
            $report = DB::table('trips as t')
                ->leftJoin(DB::raw('(
                            SELECT b.trip_id, COUNT(DISTINCT bs.seat_id) AS total_seats_booked
                            FROM bookings b
                            JOIN booking_seats bs ON bs.booking_id = b.id
                            WHERE b.booking_type = "trip"
                            AND b.status = "' . BookingStatus::PAID . '"
                            GROUP BY b.trip_id
                        ) as bs'), 'bs.trip_id', '=', 't.id')

                ->leftJoin(DB::raw('(
                            SELECT b.trip_id, COUNT(DISTINCT bs.seat_id) AS total_seats_booked_package
                            FROM bookings b
                            JOIN booking_seats bs ON bs.booking_id = b.id
                            WHERE b.booking_type = "package"
                            AND b.status = "' . BookingStatus::PAID . '"
                            GROUP BY b.trip_id
                        ) as bp'), 'bp.trip_id', '=', 't.id')

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
                            WHERE b.booking_type = "trip"
                            AND b.status = "' . BookingStatus::PAID . '"
                            GROUP BY b.trip_id
                        ) as pay'), 'pay.trip_id', '=', 't.id')

                ->leftJoin(DB::raw('(
                            SELECT b.trip_id, SUM(p.amount) AS total_paid_amount_package
                            FROM payments p
                            JOIN bookings b ON p.booking_id = b.id
                            WHERE b.booking_type = "package"
                            AND b.status = "' . BookingStatus::PAID . '"
                            GROUP BY b.trip_id
                        ) as pay_package'), 'pay_package.trip_id', '=', 't.id')

                ->leftJoin(DB::raw('(
                            SELECT trip_id, SUM(cost_amount) AS total_cost
                            FROM trip_package_costings
                            GROUP BY trip_id
                        ) as tc'), 'tc.trip_id', '=', 't.id')

                ->where(function ($query) use ($search) {
                    $query->where('t.trip_name', 'like', '%' . $search . '%')
                        ->orWhere('t.departure_time', 'like', '%' . $search . '%')
                        ->orWhere('t.arrival_time', 'like', '%' . $search . '%');
                })
                ->select(
                    't.id as trip_id',
                    't.trip_name',
                    't.departure_time',
                    't.arrival_time',
                    DB::raw('IFNULL(bs.total_seats_booked, 0) as total_seats_booked_trip'),
                    DB::raw('IFNULL(bp.total_seats_booked_package, 0) as total_seats_booked_package'),
                    DB::raw('IFNULL(sa.total_seats_available, 0) as total_seats_available'),
                    DB::raw('IFNULL(pay.total_paid_amount, 0) as total_income_trip'),
                    DB::raw('IFNULL(pay_package.total_paid_amount_package, 0) as total_income_package'),
                    DB::raw('IFNULL(tc.total_cost, 0) as total_cost'),

                    // Calculate profit for trip and package separately
                    DB::raw('(IFNULL(pay.total_paid_amount, 0) - IFNULL(tc.total_cost, 0)) as profit_trip'),
                    DB::raw('(IFNULL(pay_package.total_paid_amount_package, 0) - IFNULL(tc.total_cost, 0)) as profit_package')
                )
                ->orderBy('t.id', 'desc')
                ->paginate($perPage);



            if ($report->count() > 0) {
                return ["status" => true, "data" => $report, "message" => "Report retrieved successfully"];
            } else {
                return ["status" => true, "data" => [], "message" => "No Report found"];
            }
        } catch (Exception $ex) {
            Log::alert($ex->getMessage());
            return ["status" => false, "data" => [], "message" => "Internal server error"];
        }
    }


    public function packagePerformance($page, $search)
    {
        try {
            $perPage = 10;
            $report =  DB::table('packages as p')
                ->leftJoin(
                    DB::raw('(
                                SELECT 
                                    package_id,
                                    COUNT(*) AS total_bookings,
                                    SUM(total_cost) AS total_income
                                FROM package_bookings
                            ) as pb_data'),
                    'pb_data.package_id',
                    '=',
                    'p.id'


                )
                ->join('bookings as b', function ($join) {
                    $join->on('p.id', '=', 'b.package_id')
                        ->where('b.status', '!=', BookingStatus::CANCELLED);
                })

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
                ->where(function ($query) use ($search) {
                    $query->where('p.name', 'like', '%' . $search . '%');
                })
                ->groupBy('pb_data.package_id')
                ->paginate($perPage);

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
    public function customerValueReport($page, $search)
    {
        try {
            $perPage = 10;

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
                ->when($search, function ($query, $search) {
                    return $query->where('u.name', 'like', '%' . $search . '%');
                })
                ->where('role', 'users')
                ->where("b.status", '=', BookingStatus::PAID)
                ->orderBy('net_spent', 'desc')
                ->groupBy('u.id', 'u.name')
                ->paginate($perPage);
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


    public function transactionHistoryReport($page, $search)
    {
        try {
            $perPage = 50;
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
                ->paginate($perPage);
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

    public function monthRunningBalanceReport($page, $search)
    {
        try {
            $perPage = 10;
            // Fetch the monthly summary data
            $report = DB::table('account_history')
                ->select(
                    DB::raw('DATE_FORMAT(tran_date, "%M %Y") AS month'),
                    DB::raw('COUNT(*) AS tx_count'),
                    DB::raw('SUM(CASE WHEN transaction_type = "c" THEN amount ELSE 0 END) AS total_credit'),
                    DB::raw('SUM(CASE WHEN transaction_type = "d" THEN amount ELSE 0 END) AS total_debit')
                )
                ->groupBy(DB::raw('DATE_FORMAT(tran_date, "%Y-%m")'))
                ->orderBy('tran_date')
                ->paginate($perPage);

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

    public function dailyBalanceReport($page, $search)
    {
        try {
            // Fetch the daily summary data
            $perPage = 31;
            $month = Carbon::now()->month;
            $year = Carbon::now()->year;
            $report = DB::table(DB::raw(
                "(SELECT
                    DATE(ah.tran_date) AS date,
                    COUNT(*) AS tx_count,
                    SUM(CASE WHEN ah.transaction_type = 'c' THEN ah.amount ELSE 0 END) AS total_credit,
                    SUM(CASE WHEN ah.transaction_type = 'd' THEN ah.amount ELSE 0 END) AS total_debit
                FROM account_history ah
                WHERE MONTH(ah.tran_date) = $month
                    AND YEAR(ah.tran_date) = $year
                GROUP BY DATE(ah.tran_date)
                ) AS daily_summary"
            ))
                ->select(
                    'date',
                    'tx_count',
                    'total_credit',
                    'total_debit',
                    DB::raw('SUM(total_credit - total_debit) OVER (ORDER BY date ASC) AS balance')
                )
                ->orderBy('date')
                ->paginate($perPage, ['date', 'tx_count', 'total_credit', 'total_debit', 'balance'], 'page', $page);

            if ($report->total() > 0) {
                return [
                    "status" => ApiResponseStatus::SUCCESS,
                    "data" => $report,
                    "message" => "Report retrieved successfully"
                ];
            } 
            return [
                "status" => ApiResponseStatus::FAILED,
                "data" => [],
                "message" => "No Report found"
            ];
        } catch (Exception $ex) {
            Log::alert('Daily Balance Error: ' . $ex->getMessage());
            return [
                "status" => ApiResponseStatus::FAILED,
                "data" => [],
                "message" => "Server error occurred while generating the report"
            ];
        }
    }

    public function financialReport($page, $search)
    {
        try {
            $financialReport = DB::table('fy_report')->get();
            if ($financialReport->count() > 0) {
                return ['status' => true, 'data' => $financialReport, 'message' => "Financial report retrieved"];
            } else {
                return ['status' => true, 'data' => [], 'message' => "No financial report retrieved"];
            }
        } catch (Exception $ex) {
            Log::alert('ReportService - financialReport function error: ' . $ex->getMessage());
            return [
                "status" => false,
                "data" => [],
                "message" => "Server error occurred while generating the report"
            ];
        }
    }

    public function financialReportById($financialReportId)
    {
        try {
            $financialReport = DB::table('fy_report')->where('id', $financialReportId)->first();

            if ($financialReport) {
                return ['status' => true, 'data' => $financialReport, 'message' => "Financial report retrieved"];
            } else {
                return ['status' => true, 'data' => [], 'message' => "No financial report retrieved"];
            }
        } catch (Exception $ex) {
            Log::alert('ReportService - financialReport function error: ' . $ex->getMessage());
            return [
                "status" => false,
                "data" => [],
                "message" => "Server error occurred while generating the report"
            ];
        }
    }


    public function bookingSummary()
    {
        try {
            $result = DB::table('bookings')
                ->select(DB::raw('COUNT(1) as total_booking'), 'booking_type')
                ->groupBy('booking_type')
                ->get();
            if ($result->count() > 0) {
                return ['status' => ApiResponseStatus::SUCCESS, 'message' => "Report fetch successfully", 'data' => $result];
            }
            return ['status' => ApiResponseStatus::FAILED, 'message' => "No report found", 'data' => []];
        } catch (Exception $ex) {
            Log::alert('ReportService - bookingSummary function error: ' . $ex->getMessage());
            return [
                "status" => ApiResponseStatus::FAILED,
                "data" => [],
                "message" => "Server error occurred while generating the report"
            ];
        }
    }



    public function salesSummary()
    {
        try {
            $tripQuery = DB::table('payments as p')
                ->join('bookings as b', 'b.id', '=', 'p.booking_id')
                ->where('booking_type','=' ,BookingType::TRIP)
                ->select(
                    DB::raw("'Trip' as source"),
                    DB::raw('SUM(p.amount) as total_amount')
                );

            $packageQuery = DB::table('package_bookings')
                ->select(
                    DB::raw("'Package' as source"),
                    DB::raw('SUM(total_cost) as total_amount')
                );

            $hotelQuery = DB::table('hotel_bookings')
                ->select(
                    DB::raw("'Hotel' as source"),
                    DB::raw('SUM(total_cost) as total_amount')
                );

            $report = $tripQuery
                ->unionAll($packageQuery)
                ->unionAll($hotelQuery)
                ->get();

            if ($report->count() > 0) {
                return ['status' => ApiResponseStatus::SUCCESS, 'message' => "Report fetch successfully", 'data' => $report];
            }
            return ['status' => ApiResponseStatus::FAILED, 'message' => "No report found", 'data' => []];
        } catch (Exception $ex) {
            Log::alert('ReportService - salesSummary function error: ' . $ex->getMessage());
            return [
                "status" => ApiResponseStatus::FAILED,
                "data" => [],
                "message" => "Server error occurred while generating the report"
            ];
        }
    }



      public function routeWiseSalesSummary()
    {
        try {
           $report = DB::table('routes as r')
                        ->join('trips as t', 't.route_id', '=', 'r.id')
                        ->join('bookings as b', 'b.trip_id', '=', 't.id')
                        ->join('payments as p', 'p.booking_id', '=', 'b.id')
                        ->select(
                            'r.route_name',
                            DB::raw('COUNT(b.id) as total_bookings'),
                            DB::raw('SUM(p.amount) as total_revenue')
                        )
                        ->groupBy('r.route_name')
                        ->get();

            if ($report->count() > 0) {
                return ['status' => ApiResponseStatus::SUCCESS, 'message' => "Report fetch successfully", 'data' => $report];
            }
            return ['status' => ApiResponseStatus::FAILED, 'message' => "No report found", 'data' => []];
        } catch (Exception $ex) {
            Log::alert('ReportService - salesSummary function error: ' . $ex->getMessage());
            return [
                "status" => ApiResponseStatus::FAILED,
                "data" => [],
                "message" => "Server error occurred while generating the report"
            ];
        }
    }



     public function currentMonthTripSales()
    {
        try {
          $report = DB::table('trips as t')
                            ->join('bookings as b', 'b.trip_id', '=', 't.id')
                            ->join('payments as p', 'p.booking_id', '=', 'b.id')
                            ->whereMonth('b.created_at', Carbon::now()->month)
                            ->whereYear('b.created_at', Carbon::now()->year)
                            ->where('b.booking_type','=',BookingType::TRIP)
                            ->where('b.status','=',BookingStatus::PAID)
                            ->select(
                                't.trip_name',
                                DB::raw("DATE_FORMAT(b.created_at, '%M-%Y') as month"),
                                DB::raw('SUM(p.amount) as total_transaction')
                            )
                            ->groupBy('t.id', 'month')
                            ->orderBy('month', 'asc')
                            ->get();

            if ($report->count() > 0) {
                return ['status' => ApiResponseStatus::SUCCESS, 'message' => "Report fetch successfully", 'data' => $report];
            }
            return ['status' => ApiResponseStatus::FAILED, 'message' => "No report found", 'data' => []];
        } catch (Exception $ex) {
            Log::alert('ReportService - currentMonthTripSales function error: ' . $ex->getMessage());
            return [
                "status" => ApiResponseStatus::FAILED,
                "data" => [],
                "message" => "Server error occurred while generating the report"
            ];
        }
    }

    public function unpaidBookingReport()
    {
        try {
            // Since bookings table often lacks total_cost and payment_status, we rely on 'status' and joins.
            // This is a best-effort implementation given the schema constraints.
            
            $report = DB::table('bookings')
                ->join('users', 'bookings.user_id', '=', 'users.id')
                ->leftJoin('trips', 'bookings.trip_id', '=', 'trips.id')
                ->leftJoin('payments', 'bookings.id', '=', 'payments.booking_id')
                ->select(
                    'bookings.id as booking_id',
                    'users.name as user_name',
                    'users.email',
                    'bookings.booking_type',
                    // Calculate cost for trips if possible
                    DB::raw('COALESCE(trips.price, 0) as estimated_cost'), 
                    'bookings.created_at',
                    'trips.trip_name'
                )
                // Assuming 'pending' status implies unpaid/processing
                ->where('bookings.status', 'pending') 
                ->whereNull('payments.id') // No payment record found
                ->orderByDesc('bookings.created_at')
                ->limit(50)
                ->get();

            if ($report->count() > 0) {
                return ['status' => ApiResponseStatus::SUCCESS, 'message' => "Report fetch successfully", 'data' => $report];
            }
            return ['status' => ApiResponseStatus::FAILED, 'message' => "No report found", 'data' => []];
        } catch (Exception $ex) {
            Log::alert('ReportService - unpaidBookingReport function error: ' . $ex->getMessage());
            return [
                "status" => ApiResponseStatus::FAILED,
                "data" => [],
                "message" => "Server error occurred while generating the report"
            ];
        }
    }

    public function userGrowthReport()
    {
        try {
            $report = DB::table('users')
                ->select(
                    DB::raw("DATE_FORMAT(created_at, '%Y-%m') as month"),
                    DB::raw('COUNT(id) as new_users')
                )
                ->where('created_at', '>=', Carbon::now()->subMonths(12))
                ->groupBy('month')
                ->orderBy('month', 'asc')
                ->get();

            if ($report->count() > 0) {
                return ['status' => ApiResponseStatus::SUCCESS, 'message' => "Report fetch successfully", 'data' => $report];
            }
            return ['status' => ApiResponseStatus::FAILED, 'message' => "No report found", 'data' => []];
        } catch (Exception $ex) {
            Log::alert('ReportService - userGrowthReport function error: ' . $ex->getMessage());
            return [
                "status" => ApiResponseStatus::FAILED,
                "data" => [],
                "message" => "Server error occurred while generating the report"
            ];
        }
    }

    public function ticketStatusReport()
    {
        try {
            $report = DB::table('tickets')
                ->select(
                    'status',
                    DB::raw('COUNT(id) as total_tickets')
                )
                ->groupBy('status')
                ->get()
                ->map(function ($item) {
                    $statusMap = [
                        '0' => 'Pending',
                        '1' => 'Resolved',
                        '2' => 'Declined'
                    ];
                    $item->status_label = $statusMap[$item->status] ?? 'Unknown';
                    return $item;
                });

            if ($report->count() > 0) {
                return ['status' => ApiResponseStatus::SUCCESS, 'message' => "Report fetch successfully", 'data' => $report];
            }
            return ['status' => ApiResponseStatus::FAILED, 'message' => "No report found", 'data' => []];
        } catch (Exception $ex) {
            Log::alert('ReportService - ticketStatusReport function error: ' . $ex->getMessage());
            return [
                "status" => ApiResponseStatus::FAILED,
                "data" => [],
                "message" => "Server error occurred while generating the report"
            ];
        }
    }

    public function refundStatusReport()
    {
        try {
            $report = DB::table('refunds')
                ->select(
                    'status',
                    DB::raw('COUNT(id) as total_refunds'),
                    DB::raw('SUM(amount) as total_amount')
                )
                ->groupBy('status')
                ->get();

            if ($report->count() > 0) {
                return ['status' => ApiResponseStatus::SUCCESS, 'message' => "Report fetch successfully", 'data' => $report];
            }
            return ['status' => ApiResponseStatus::FAILED, 'message' => "No report found", 'data' => []];
        } catch (Exception $ex) {
            Log::alert('ReportService - refundStatusReport function error: ' . $ex->getMessage());
            return [
                "status" => ApiResponseStatus::FAILED,
                "data" => [],
                "message" => "Server error occurred while generating the report"
            ];
        }
    }

    public function lowOccupancyTripReport()
    {
        try {
            $report = DB::table('trips')
                ->join('vehicles', 'trips.vehicle_id', '=', 'vehicles.id')
                ->leftJoin('bookings', function ($join) {
                    $join->on('trips.id', '=', 'bookings.trip_id')
                        ->where('bookings.status', '!=', 'cancelled');
                })
                ->select(
                    'trips.trip_name',
                    'trips.departure_time',
                    'vehicles.total_seats',
                    DB::raw('COUNT(bookings.id) as booked_seats'),
                    DB::raw('ROUND((COUNT(bookings.id) / vehicles.total_seats) * 100, 2) as occupancy_rate')
                )
                ->where('trips.departure_time', '>', Carbon::now())
                ->groupBy('trips.id', 'trips.trip_name', 'trips.departure_time', 'vehicles.total_seats')
                ->having('occupancy_rate', '<', 50)
                ->orderBy('trips.departure_time', 'asc')
                ->get();

            if ($report->count() > 0) {
                return ['status' => ApiResponseStatus::SUCCESS, 'message' => "Report fetch successfully", 'data' => $report];
            }
            return ['status' => ApiResponseStatus::FAILED, 'message' => "No report found", 'data' => []];
        } catch (Exception $ex) {
            Log::alert('ReportService - lowOccupancyTripReport function error: ' . $ex->getMessage());
            return [
                "status" => ApiResponseStatus::FAILED,
                "data" => [],
                "message" => "Server error occurred while generating the report"
            ];
        }
    }

    public function avgBookingValueReport()
    {
        try {
            // Use payments table to calculate actual realized booking value
            $report = DB::table('payments')
                ->join('bookings', 'payments.booking_id', '=', 'bookings.id')
                ->select(
                    'bookings.booking_type',
                    DB::raw('AVG(payments.amount) as average_value')
                )
                  // Exclude cancelled bookings if status column exists and is used
                ->where('bookings.status', '!=', 'cancelled')
                ->groupBy('bookings.booking_type')
                ->orderByDesc('average_value')
                ->get();

            if ($report->count() > 0) {
                return ['status' => ApiResponseStatus::SUCCESS, 'message' => "Report fetch successfully", 'data' => $report];
            }
            return ['status' => ApiResponseStatus::FAILED, 'message' => "No report found", 'data' => []];
        } catch (Exception $ex) {
            Log::alert('ReportService - avgBookingValueReport function error: ' . $ex->getMessage());
            return [
                "status" => ApiResponseStatus::FAILED,
                "data" => [],
                "message" => "Server error occurred while generating the report"
            ];
        }
    }

    public function lowPerformingPackages()
    {
        try {
            // Packages with low bookings in the last 90 days. Decision: Drop or Promote.
            $startDate = Carbon::now()->subDays(90);
            
            $report = DB::table('packages as p')
                ->leftJoin('package_bookings as pb', function($join) use ($startDate) {
                    $join->on('p.id', '=', 'pb.package_id')
                         ->where('pb.created_at', '>=', $startDate);
                })
                ->select(
                    'p.id',
                    'p.name as package_name',
                    DB::raw('COUNT(pb.id) as recent_booking_count')
                )
                ->groupBy('p.id', 'p.name')
                ->having('recent_booking_count', '<', 5) // Threshold for "low performing"
                ->orderBy('recent_booking_count', 'asc')
                ->get();

            if ($report->count() > 0) {
                return ['status' => ApiResponseStatus::SUCCESS, 'message' => "Report fetch successfully", 'data' => $report];
            }
            return ['status' => ApiResponseStatus::SUCCESS, 'message' => "No low performing packages found", 'data' => []];
        } catch (Exception $ex) {
            Log::alert('ReportService - lowPerformingPackages function error: ' . $ex->getMessage());
            return [
                "status" => ApiResponseStatus::FAILED,
                "data" => [],
                "message" => "Server error occurred while generating the report"
            ];
        }
    }

    public function highCancellationPackages()
    {
        try {
            // Packages with high cancellation rates. Decision: Investigate quality/pricing.
            $report = DB::table('package_bookings as pb')
                ->join('packages as p', 'pb.package_id', '=', 'p.id')
                ->leftJoin('bookings as b', 'pb.booking_id', '=', 'b.id') // Assuming package_bookings links to bookings
                ->select(
                    'p.name as package_name',
                    DB::raw('COUNT(pb.id) as total_bookings'),
                    DB::raw('SUM(CASE WHEN b.status = "cancelled" THEN 1 ELSE 0 END) as cancelled_count'),
                    DB::raw('(SUM(CASE WHEN b.status = "cancelled" THEN 1 ELSE 0 END) / COUNT(pb.id)) * 100 as cancellation_rate')
                )
                ->groupBy('p.id', 'p.name')
                ->having('total_bookings', '>', 0) // Ignore never booked packages
                ->orderByDesc('cancellation_rate')
                ->limit(20)
                ->get();

            if ($report->count() > 0) {
                return ['status' => ApiResponseStatus::SUCCESS, 'message' => "Report fetch successfully", 'data' => $report];
            }
            return ['status' => ApiResponseStatus::FAILED, 'message' => "No report found", 'data' => []];
        } catch (Exception $ex) {
            Log::alert('ReportService - highCancellationPackages function error: ' . $ex->getMessage());
            return [
                "status" => ApiResponseStatus::FAILED,
                "data" => [],
                "message" => "Server error occurred while generating the report"
            ];
        }
    }

    public function packageProfitMargin()
    {
        try {
            // Profit margin analysis. Decision: Pricing adjustment.
            $report = DB::table('packages as p')
                ->leftJoin('package_bookings as pb', 'p.id', '=', 'pb.package_id')
                ->leftJoin('trip_package_costings as tpc', 'p.id', '=', 'tpc.package_id')
                ->select(
                    'p.name as package_name',
                    DB::raw('COALESCE(SUM(pb.total_cost), 0) as total_revenue'),
                    DB::raw('COALESCE(SUM(DISTINCT tpc.cost_amount), 0) as total_fixed_cost'), // Simplified cost assumption
                    DB::raw('COALESCE(SUM(pb.total_cost), 0) - COALESCE(SUM(DISTINCT tpc.cost_amount), 0) as gross_profit'),
                     DB::raw('CASE WHEN SUM(pb.total_cost) > 0 THEN ((SUM(pb.total_cost) - COALESCE(SUM(DISTINCT tpc.cost_amount), 0)) / SUM(pb.total_cost)) * 100 ELSE 0 END as margin_percentage')
                )
                ->groupBy('p.id', 'p.name')
                ->orderByDesc('margin_percentage')
                ->get();

            if ($report->count() > 0) {
                return ['status' => ApiResponseStatus::SUCCESS, 'message' => "Report fetch successfully", 'data' => $report];
            }
            return ['status' => ApiResponseStatus::FAILED, 'message' => "No report found", 'data' => []];
        } catch (Exception $ex) {
            Log::alert('ReportService - packageProfitMargin function error: ' . $ex->getMessage());
            return [
                "status" => ApiResponseStatus::FAILED,
                "data" => [],
                "message" => "Server error occurred while generating the report"
            ];
        }
    }
}
