<?php

namespace App\Http\Controllers;

use App\Repository\Services\Dashboard\DashboardService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AdminController extends Controller
{

    private $dashboardService;

    public function __construct(DashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    public function dashboard()
    {
        $users = $this->dashboardService->index();
        return response()->json([
            "data" => $users,
            "message" => "success"
        ], 200);
    }

    public function databaseList(){
        try{
            $databases = $this->dashboardService->databaseList();
            return response()->json([
                "data" => $databases,
                "message" => "success"
            ], 200);
        }catch(Exception $ex){
            Log::alert($ex->getMessage());
        }
    }

}
