<?php

namespace App\Repository\Services;

use App\Constants\ApiResponseStatus;
use App\Repository\Interfaces\CommonInterface;
use App\route;
use Exception;
use Illuminate\Support\Facades\Log;
use DB;
use Dotenv\Exception\ValidationException;

class RouteService
{

    protected $contact;

    public function index($page, $search)
    {
        try {
            $perPage = 10;
            $routes = DB::table('routes')
                ->where('route_name', 'like', '%' . $search . '%')
                ->orWhere('origin', 'like', '%' . $search . '%')
                ->orWhere('destination', 'like', '%' . $search . '%')
                ->paginate($perPage, ['*'], 'page', $page);

            if ($routes->total() > 0) {
                return ["status" => ApiResponseStatus::SUCCESS, "data" => $routes, "message" => config("message.route_list_retrieved")];
            }
            return ["status" => ApiResponseStatus::FAILED, "data" => [], "message" => config("message.no_data_found")];
        } catch (Exception $ex) {
            Log::alert($ex->getMessage());
            return ["status" => false, "data" => [], "message" => config("message.server_error")];
        }
    }

    public function store($request)
    {
        try {
            $routeInsert = DB::table('routes')->insert($request);
            if ($routeInsert) {
                return ["status" => ApiResponseStatus::SUCCESS, "data" => $routeInsert, "message" => config("message.route_added")];
            }
            return ["status" => ApiResponseStatus::FAILED, "data" => null, "message" => config("message.not_stored")];
        } catch (Exception $ex) {
            Log::alert("Insert error: " . $ex->getMessage());
            return ["status" => ApiResponseStatus::FAILED, "data" => null, "message" => config("message.server_error")];
        }
    }


    public function findById($id)
    {
        try {
            $route = DB::table('routes')->where('id', $id)->first();
            if (!$route) {
                return ["status" => ApiResponseStatus::FAILED, "data" => null, "message" => config("message.no_data_found")];
            }
            return $route;
        } catch (Exception $ex) {
            Log::alert("Route Service - findById function" . $ex->getMessage());
            return ["status" => ApiResponseStatus::FAILED, "data" => null, "message" => config("message.server_error")];
        }
    }

    public function delete($id)
    {
        try {

            $route = DB::table('routes')->where('id', $id)->first();
            if (!$route) {
                return ["status" => ApiResponseStatus::FAILED, "data" => null, "message" => config("message.no_data_found")];
            }

            $response = DB::table('routes')->where('id', $id)->delete();
            if (!$response) {
                return ["status" => ApiResponseStatus::FAILED, "data" => null, "message" => config("message.not_deleted")];
            }

            return ["status" => ApiResponseStatus::SUCCESS, "data" => $response, "message" => config("message.route_deleted")];
        } catch (Exception $ex) {
            Log::alert("Route Service - delete function" . $ex->getMessage());
            return ["status" => ApiResponseStatus::FAILED, "data" => null, "message" => config("message.route_deleted")];
        }
    }

    public function dropdownList()
    {
        try {
            $perPage = 10;
            $routes = DB::table('routes')->select('id', 'route_name')->get();
            if ($routes->total() > 0) {
                return ["status" => ApiResponseStatus::SUCCESS, "data" => $routes, "message" => "Route Information retrieved"];
            }
            return ["status" => ApiResponseStatus::SUCCESS, "data" => $routes, "message" => config("message.no_data_found")];
        } catch (Exception $ex) {
            Log::alert($ex->getMessage());
        }
    }
}
