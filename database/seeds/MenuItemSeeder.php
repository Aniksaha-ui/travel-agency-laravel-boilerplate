<?php

use Illuminate\Database\Seeder;
use App\MenuItem;
use Illuminate\Support\Facades\DB;

class MenuItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Clear existing data
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        MenuItem::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $mainMenuItems = [
            [
                'title' => 'Dashboard',
                'path' => '/admin/dashboard',
                'icon' => 'DashboardIcon',
                'location' => 'main',
                'roles' => ['admin', 'guide'],
            ],
            [
                'title' => 'User Management',
                'path' => '/admin/users',
                'icon' => 'UserManagementIcon',
                'location' => 'main',
                'roles' => ['admin'],
            ],
            [
                'title' => 'Route Management',
                'path' => '/admin/routes',
                'icon' => 'RouteManagementIcon',
                'location' => 'main',
                'roles' => ['admin'],
            ],
            [
                'title' => 'Vehicle Management',
                'path' => '/admin/vehicles',
                'icon' => 'VehicleManagementIcon',
                'location' => 'main',
                'roles' => ['admin'],
            ],
            [
                'title' => 'Seat Management',
                'path' => '/admin/seat',
                'icon' => 'SeatManagementIcon',
                'location' => 'main',
                'roles' => ['admin'],
            ],
            [
                'title' => 'Trip Management',
                'path' => '/admin/trips',
                'icon' => 'TripManagementIcon',
                'location' => 'main',
                'roles' => ['admin'],
            ],
            [
                'title' => 'Package Management',
                'path' => '/admin/packages',
                'icon' => 'PackageManagementIcon',
                'location' => 'main',
                'roles' => ['admin'],
            ],
        ];

        foreach ($mainMenuItems as $index => $item) {
            MenuItem::create([
                'title' => $item['title'],
                'path' => $item['path'],
                'icon' => $item['icon'],
                'location' => 'main',
                'order' => $index,
                'roles' => $item['roles'],
            ]);
        }

        $bottomMenuItems = [
            [
                'title' => 'Transaction Management',
                'path' => '/admin/transactions',
                'icon' => 'MoneyIcon',
                'location' => 'bottom',
                'roles' => ['admin'],
            ],
            [
                'title' => 'Ticket Management',
                'path' => '/admin/tickets',
                'icon' => 'ComplaintIcon',
                'location' => 'bottom',
                'roles' => ['admin'],
            ],
            [
                'title' => 'Guide Management',
                'path' => '/admin/guide',
                'icon' => 'GuideManagementIcon',
                'location' => 'bottom',
                'roles' => ['admin'],
            ],
            [
                'title' => 'Booking Management',
                'path' => '/admin/bookings',
                'icon' => 'BookingManagementIcon',
                'location' => 'bottom',
                'roles' => ['admin'],
            ],
            [
                'title' => 'Refund Management',
                'path' => '/admin/refunds',
                'icon' => 'RefundManagementIcon',
                'location' => 'bottom',
                'roles' => ['admin'],
            ],
            [
                'title' => 'Hotel Management',
                'icon' => 'HotelManagementIcon',
                'location' => 'bottom',
                'roles' => ['admin'],
                'children' => [
                    [
                        'title' => 'Hotel Management',
                        'path' => '/admin/hotel',
                        'icon' => 'HotelManagementIcon',
                        'roles' => ['admin'],
                    ],
                    [
                        'title' => 'Hotel Checkin',
                        'path' => '/admin/hotel/checkin',
                        'icon' => 'HotelCheckInIcon',
                        'roles' => ['admin'],
                    ],
                ],
            ],
            [
                'title' => 'Settings', 
                'icon' => 'HotelManagementIcon',
                'location' => 'bottom',
                'roles' => ['admin'],
                'children' => [
                    [
                        'title' => 'Online Payment Config',
                        'path' => '/admin/online-payment-configure',
                        'icon' => 'PackageManagementIcon',
                        'roles' => ['admin'],
                    ],
                ],
            ],
            [
                'title' => 'Reports Management', 
                'icon' => 'ReportManagementIcon',
                'location' => 'bottom',
                'roles' => ['admin'],
                'children' => [
                    [
                        'title' => 'Vehicle - Total Seat Report',
                        'path' => '/admin/vehiclewiseseatreport',
                        'icon' => 'VehicleManagementIcon',
                        'roles' => ['admin'],
                    ],
                    [
                        'title' => 'Account - Balance Report',
                        'path' => '/admin/account/balance',
                        'icon' => 'MoneyIcon',
                        'roles' => ['admin'],
                    ],
                    [
                        'title' => 'Monthly Running Balance',
                        'path' => '/admin/monthRunningBalance',
                        'icon' => 'MoneyIcon',
                        'roles' => ['admin'],
                    ],
                    [
                        'title' => 'Vehicle Tracking Report',
                        'path' => '/admin/vehicletrackingreport',
                        'icon' => 'VehicleManagementIcon',
                        'roles' => ['admin'],
                    ],
                    [
                        'title' => 'Trip Performance Report',
                        'path' => '/admin/tripPerformance',
                        'icon' => 'TripManagementIcon',
                        'roles' => ['admin'],
                    ],
                    [
                        'title' => 'Package Performance Report',
                        'path' => '/admin/packagePerformance',
                        'icon' => 'PackageManagementIcon',
                        'roles' => ['admin'],
                    ],
                    [
                        'title' => 'Customer Value Report',
                        'path' => '/admin/customerValueReport',
                        'icon' => 'MoneyIcon',
                        'roles' => ['admin'],
                    ],
                    [
                        'title' => 'Financial Report',
                        'path' => '/admin/financialReport',
                        'icon' => 'MoneyIcon',
                        'roles' => ['admin'],
                    ],
                    [
                        'title' => 'Monitoring Query Report',
                        'path' => '/admin/monitoring',
                        'icon' => 'SqlMonitorIcon',
                        'roles' => ['admin'],
                    ],
                    [
                        'title' => 'Package Booking Summary',
                        'path' => '/admin/package-summary',
                        'icon' => 'PackageManagementIcon',
                        'roles' => ['admin'],
                    ],
                ],
            ],
        ];

        foreach ($bottomMenuItems as $index => $item) {
            $parent = MenuItem::create([
                'title' => $item['title'],
                'path' => $item['path'] ?? null,
                'icon' => $item['icon'],
                'location' => 'bottom',
                'order' => $index,
                'roles' => $item['roles'],
            ]);

            if (isset($item['children'])) {
                foreach ($item['children'] as $childIndex => $child) {
                    MenuItem::create([
                        'title' => $child['title'],
                        'path' => $child['path'],
                        'icon' => $child['icon'],
                        'location' => 'bottom',
                        'parent_id' => $parent->id,
                        'order' => $childIndex,
                        'roles' => $child['roles'],
                    ]);
                }
            }
        }
    }
}
