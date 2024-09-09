<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Repository\Services\RouteService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class RouteController extends Controller
{
    protected $routeService;
    public function __construct(RouteService $routeService)
    {
        $this->routeService = $routeService;
    }

    public function index(Request $request){
        $page = $request->query('page');
        $search = $request->query('search');
      
        $response = $this->routeService->index($page,$search);
        return response()->json([
            "data"=> $response,
            "message"=> "success"
        ],200);
    }
}
