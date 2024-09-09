<?php

namespace App\Repository\Services;

use App\Repository\Interfaces\RouteInterface;
use Exception;
use Illuminate\Support\Facades\Log;
use DB;

class RouteService implements RouteInterface{

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
           $routes = DB::table('routes') 
                    ->where('route_name', 'like', '%' . $search . '%')
                    ->orWhere('origin', 'like', '%' . $search . '%')
                    ->orWhere('destination', 'like', '%' . $search . '%')
                    ->paginate($perPage, ['*'], 'page', $page);
           return $routes;
        }catch(Exception $ex){
            Log::alert($ex->getMessage());
        }
    }

}

