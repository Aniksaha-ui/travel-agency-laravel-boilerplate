<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\MenuItem;
use Illuminate\Support\Facades\Log;
use Exception;

class MenuController extends Controller
{
    /**
     * Get menu items organized by location.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        try {

            $userRole = auth()->user()->role; // 'admin' or 'guide'

            // Fetch MAIN_MENU_ITEMS
            $mainMenu = MenuItem::where('location', 'main')
                ->whereNull('parent_id')
                ->whereJsonContains('roles', $userRole)
                ->orderBy('order')
                ->with(['children' => function ($query) use ($userRole) {
                    $query->whereJsonContains('roles', $userRole)->orderBy('order');
                }])
                ->get();

            // Fetch BOTTOM_MENU_ITEMS
            $bottomMenu = MenuItem::where('location', 'bottom')
                ->whereNull('parent_id')
                ->whereJsonContains('roles', $userRole)
                ->orderBy('order')
                ->with(['children' => function ($query) use ($userRole) {
                    $query->whereJsonContains('roles', $userRole)->orderBy('order');
                }])
                ->get();

            return $this->successResponse([
                'MAIN_MENU_ITEMS' => $mainMenu,
                'BOTTOM_MENU_ITEMS' => $bottomMenu,
            ], 'Menu items fetched successfully');
        } catch (Exception $ex) {
            Log::error("MenuController Index Error: " . $ex->getMessage());
            return $this->failedResponse('Something went wrong.');
        }
    }
}
