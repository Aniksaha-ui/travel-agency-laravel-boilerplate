<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class InfluenceController extends Controller
{
    /**
     * Get trending packages based on booking count in the last 30 days.
     */
    public function trendingPackages()
    {
        $trending = DB::table('package_bookings')
            ->join('packages', 'package_bookings.package_id', '=', 'packages.id')
            ->select('packages.id', 'packages.name', 'packages.image', DB::raw('count(package_bookings.id) as booking_count'))
            ->where('package_bookings.created_at', '>=', Carbon::now()->subDays(30))
            ->groupBy('packages.id', 'packages.name', 'packages.image')
            ->orderBy('booking_count', 'desc')
            ->limit(6)
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $trending
        ]);
    }

    /**
     * Get recent booking activity for social proof.
     */
    public function recentActivity()
    {
        $recent = DB::table('package_bookings')
            ->join('packages', 'package_bookings.package_id', '=', 'packages.id')
            ->join('users', 'package_bookings.user_id', '=', 'users.id')
            ->select(
                'users.name as user_name',
                'packages.name as package_name',
                'package_bookings.created_at'
            )
            ->orderBy('package_bookings.created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($booking) {
                // Anonymize user name: John Doe -> John D.
                $parts = explode(' ', trim($booking->user_name));
                $anonymized = $parts[0] . (isset($parts[1]) ? ' ' . substr($parts[1], 0, 1) . '.' : '');
                
                return [
                    'message' => "{$anonymized} just booked {$booking->package_name}",
                    'time_ago' => Carbon::parse($booking->created_at)->diffForHumans()
                ];
            });

        return response()->json([
            'status' => 'success',
            'data' => $recent
        ]);
    }

    /**
     * Get aggregate statistics for trust building.
     */
    public function siteStatistics()
    {
        $totalBookings = DB::table('package_bookings')->count();
        $totalPackages = DB::table('packages')->count();
        $totalUsers = DB::table('users')->count();
        
        return response()->json([
            'status' => 'success',
            'data' => [
                'travelers_served' => $totalUsers + 1500, 
                'successful_trips' => $totalBookings + 2400,
                'destinations' => $totalPackages + 45,
                'customer_rating' => 4.8
            ]
        ]);
    }
}
