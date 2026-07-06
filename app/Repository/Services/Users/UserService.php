<?php

namespace App\Repository\Services\Users;

use App\Repository\Interfaces\CommonInterface;
use Exception;
use Illuminate\Support\Facades\Log;
use DB;

class UserService implements CommonInterface{

    protected $contact;

    /**
     * Get all contacts.
     *
     * @return bool
     */
    public function index($page,$search)
    {

        try{
            $perPage = 10;
           $routes = DB::table('users') 
                    ->where('name', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%')
                    ->orWhere('role', 'like', '%' . $search . '%')
                    ->paginate($perPage, ['id','name','email','role'], 'page', $page);
           return $routes;
        }catch(Exception $ex){
            Log::alert($ex->getMessage());
        }
    }

    public function store($request){
        try{
            $routeInsert = DB::table('users')->insert($request);
            if($routeInsert){
                return true;
            }
            return false;
        }catch(Exception $ex){
            Log::alert("Insert error: ".$ex->getMessage());
        }
    }


    public function findById($id){
        try{
            $route = DB::table('users')->where('id',$id)->first();
            return $route;
        }catch(Exception $ex){
            Log::alert("Find By Id Error".$ex->getMessage());
        }
    }

    public function delete($id){
        try{
            $response =DB::table('users')->where('id',$id)->delete();
            return $response;
        }catch(Exception $ex){
            Log::alert("Delete Error".$ex->getMessage());
        }
    }

    public function profile($id)
    {
        try {
            $user = DB::table('users')
                ->select('id', 'name', 'email', 'role', 'created_at', 'updated_at')
                ->where('id', $id)
                ->first();

            if (!$user) {
                return [
                    'status' => false,
                    'message' => 'Customer not found',
                    'data' => [],
                ];
            }

            $summary = [
                'total_bookings' => (int) DB::table('bookings')->where('user_id', $id)->count(),
                'paid_bookings' => (int) DB::table('bookings')->where('user_id', $id)->where('status', 'paid')->count(),
                'pending_bookings' => (int) DB::table('bookings')->where('user_id', $id)->where('status', 'pending')->count(),
                'cancelled_bookings' => (int) DB::table('bookings')->where('user_id', $id)->whereIn('status', ['cancelled', 'cancle booking'])->count(),
                'total_tickets' => (int) DB::table('tickets')->where('generate_by', $id)->count(),
                'open_tickets' => (int) DB::table('tickets')->where('generate_by', $id)->whereNotIn('status', ['closed', 'resolved', '0'])->count(),
                'total_refunds' => (int) DB::table('refunds')
                    ->join('bookings', 'refunds.booking_id', '=', 'bookings.id')
                    ->where('bookings.user_id', $id)
                    ->count(),
                'refund_pending' => (int) DB::table('refunds')
                    ->join('bookings', 'refunds.booking_id', '=', 'bookings.id')
                    ->where('bookings.user_id', $id)
                    ->where('refunds.status', 'pending')
                    ->count(),
                'visa_applications' => (int) DB::table('visa_applications')->where('user_id', $id)->count(),
                'total_spent' => (float) DB::table('payments')
                    ->join('bookings', 'payments.booking_id', '=', 'bookings.id')
                    ->where('bookings.user_id', $id)
                    ->sum('payments.amount'),
                'last_booking_at' => DB::table('bookings')->where('user_id', $id)->max('created_at'),
                'last_ticket_at' => DB::table('tickets')->where('generate_by', $id)->max('created_at'),
            ];

            $bookings = DB::table('bookings')
                ->leftJoin('trips', 'bookings.trip_id', '=', 'trips.id')
                ->leftJoin('packages', 'bookings.package_id', '=', 'packages.id')
                ->leftJoin('hotel_bookings', 'bookings.hotel_booking_id', '=', 'hotel_bookings.id')
                ->leftJoin('hotels', 'hotel_bookings.hotel_id', '=', 'hotels.id')
                ->leftJoin('payments', 'payments.booking_id', '=', 'bookings.id')
                ->leftJoin('refunds', 'refunds.booking_id', '=', 'bookings.id')
                ->leftJoin('package_bookings', 'package_bookings.booking_id', '=', 'bookings.id')
                ->leftJoin('visa_applications', 'visa_applications.booking_id', '=', 'bookings.id')
                ->leftJoin('booking_seats', 'booking_seats.booking_id', '=', 'bookings.id')
                ->leftJoin('seats', 'seats.id', '=', 'booking_seats.seat_id')
                ->where('bookings.user_id', $id)
                ->groupBy(
                    'bookings.id',
                    'bookings.user_id',
                    'bookings.trip_id',
                    'bookings.seat_ids',
                    'bookings.status',
                    'bookings.booking_type',
                    'bookings.package_id',
                    'bookings.hotel_booking_id',
                    'bookings.created_at',
                    'bookings.updated_at',
                    'trips.trip_name',
                    'trips.departure_time',
                    'trips.arrival_time',
                    'trips.price',
                    'packages.name',
                    'hotels.name',
                    'hotel_bookings.check_in_date',
                    'hotel_bookings.check_out_date',
                    'hotel_bookings.booking_status',
                    'hotel_bookings.payment_status',
                    'hotel_bookings.total_cost',
                    'hotel_bookings.total_persons',
                    'payments.amount',
                    'payments.payment_method',
                    'payments.created_at',
                    'refunds.id',
                    'refunds.amount',
                    'refunds.status',
                    'refunds.reason',
                    'package_bookings.total_adult',
                    'package_bookings.total_child',
                    'package_bookings.total_cost',
                    'package_bookings.payment_status',
                    'visa_applications.application_no',
                    'visa_applications.status',
                    'visa_applications.payment_status',
                    'visa_applications.country_name_snapshot',
                    'visa_applications.visa_type_snapshot',
                    'visa_applications.fee_snapshot'
                )
                ->orderBy('bookings.created_at', 'desc')
                ->select(
                    'bookings.id',
                    'bookings.user_id',
                    'bookings.trip_id',
                    'bookings.package_id',
                    'bookings.hotel_booking_id',
                    'bookings.status',
                    'bookings.booking_type',
                    'bookings.created_at',
                    'bookings.updated_at',
                    'trips.trip_name',
                    'trips.departure_time',
                    'trips.arrival_time',
                    'trips.price as trip_price',
                    'packages.name as package_name',
                    'hotels.name as hotel_name',
                    'hotel_bookings.check_in_date',
                    'hotel_bookings.check_out_date',
                    'hotel_bookings.booking_status as hotel_booking_status',
                    'hotel_bookings.payment_status as hotel_payment_status',
                    'hotel_bookings.total_cost as hotel_total_cost',
                    'hotel_bookings.total_persons as hotel_total_persons',
                    'payments.amount as payment_amount',
                    'payments.payment_method',
                    'payments.created_at as payment_created_at',
                    'refunds.id as refund_id',
                    'refunds.amount as refund_amount',
                    'refunds.status as refund_status',
                    'refunds.reason as refund_reason',
                    'package_bookings.total_adult',
                    'package_bookings.total_child',
                    'package_bookings.total_cost as package_total_cost',
                    'package_bookings.payment_status as package_payment_status',
                    'visa_applications.application_no',
                    'visa_applications.status as visa_status',
                    'visa_applications.payment_status as visa_payment_status',
                    'visa_applications.country_name_snapshot',
                    'visa_applications.visa_type_snapshot',
                    'visa_applications.fee_snapshot',
                    DB::raw('GROUP_CONCAT(DISTINCT seats.seat_number ORDER BY seats.seat_number SEPARATOR ", ") as seat_numbers')
                )
                ->get()
                ->map(function ($booking) {
                    $booking->display_title = $this->buildBookingTitle($booking);
                    return $booking;
                })
                ->values();

            $tickets = DB::table('tickets')
                ->leftJoin('users as resolved_user', 'tickets.resolved_by', '=', 'resolved_user.id')
                ->where('tickets.generate_by', $id)
                ->orderBy('tickets.created_at', 'desc')
                ->select(
                    'tickets.id',
                    'tickets.title',
                    'tickets.description',
                    'tickets.status',
                    'tickets.remarks',
                    'tickets.attachment',
                    'tickets.created_at',
                    'tickets.updated_at',
                    'resolved_user.name as resolved_by'
                )
                ->get();

            $refunds = DB::table('refunds')
                ->join('bookings', 'refunds.booking_id', '=', 'bookings.id')
                ->leftJoin('trips', 'bookings.trip_id', '=', 'trips.id')
                ->leftJoin('packages', 'bookings.package_id', '=', 'packages.id')
                ->leftJoin('hotel_bookings', 'bookings.hotel_booking_id', '=', 'hotel_bookings.id')
                ->leftJoin('hotels', 'hotel_bookings.hotel_id', '=', 'hotels.id')
                ->where('bookings.user_id', $id)
                ->orderBy('refunds.created_at', 'desc')
                ->select(
                    'refunds.id',
                    'refunds.booking_id',
                    'refunds.amount',
                    'refunds.reason',
                    'refunds.status',
                    'refunds.created_at',
                    'trips.trip_name',
                    'packages.name as package_name',
                    'hotels.name as hotel_name'
                )
                ->get();

            $visaApplications = DB::table('visa_applications')
                ->leftJoin('visa_packages', 'visa_applications.visa_package_id', '=', 'visa_packages.id')
                ->where('visa_applications.user_id', $id)
                ->orderBy('visa_applications.created_at', 'desc')
                ->select(
                    'visa_applications.id',
                    'visa_applications.application_no',
                    'visa_applications.full_name',
                    'visa_applications.country_name_snapshot',
                    'visa_applications.visa_type_snapshot',
                    'visa_applications.status',
                    'visa_applications.payment_status',
                    'visa_applications.fee_snapshot',
                    'visa_applications.travel_date',
                    'visa_applications.created_at',
                    'visa_packages.title as visa_package_name'
                )
                ->get();

            return [
                'status' => true,
                'message' => 'Customer profile loaded successfully',
                'data' => [
                    'user' => $user,
                    'summary' => $summary,
                    'bookings' => $bookings,
                    'tickets' => $tickets,
                    'refunds' => $refunds,
                    'visa_applications' => $visaApplications,
                ],
            ];
        } catch (Exception $ex) {
            Log::alert("Customer Profile Error: " . $ex->getMessage());

            return [
                'status' => false,
                'message' => 'Failed to load customer profile',
                'data' => [],
            ];
        }
    }

    private function buildBookingTitle($booking)
    {
        if (!empty($booking->trip_name)) {
            return $booking->trip_name;
        }

        if (!empty($booking->package_name)) {
            return $booking->package_name;
        }

        if (!empty($booking->hotel_name)) {
            return $booking->hotel_name;
        }

        if (!empty($booking->application_no)) {
            return 'Visa Application ' . $booking->application_no;
        }

        return 'Booking #' . $booking->id;
    }


}
