<?php

namespace App\Repository\Services\Configure;

use App\Constants\ApiResponseStatus;
use Exception;
use Illuminate\Support\Facades\Log;
use DB;


class ConfigureService
{

    protected $contact;

    public function onlineConfigureList($page, $search)
    {
        try {
            $perPage = 10;
                $configureList=DB::table('payment_method_config')
                ->orderBy('payment_method_config.id','desc')
                ->paginate($perPage, ['payment_method_config.id', 'payment_method_config.payment_for'], 'page', $page);
         
            if($configureList->total() > 0){
                return ["status" => ApiResponseStatus::SUCCESS, "data" => $configureList, "message" => config("message.app_data_retrieved")];
            }
            return ["status" => ApiResponseStatus::FAILED, "data" => [], "message" => config("message.no_data_found")];


        } catch (Exception $ex) {
            Log::alert("bookingService-index function" . $ex->getMessage());
            return ["status" => false, "data" => [], "message" => config("message.server_error")];

        }
    }



     public function store($request){
        try {
            $configureSetup = DB::table('payment_method_config')->insert($request);
            if ($configureSetup) {
                return ["status" => ApiResponseStatus::SUCCESS, "data" => $configureSetup, "message" => config("message.app_added")];
            }
            return ["status" => ApiResponseStatus::FAILED, "data" => null, "message" => config("message.not_stored")];
        } catch (Exception $ex) {
            Log::alert("Error: " . $ex->getMessage());
            return ["status" => ApiResponseStatus::FAILED, "data" => null, "message" => config("message.server_error")];
        }
    }



    public function findConfigureById($id)
    {
        try {
            $route = DB::table('routes')->where('id', $id)->first();
            if (!$route) {
                return ["status" => ApiResponseStatus::FAILED, "data" => null, "message" => config("message.no_data_found")];
            }

            return ["status" => ApiResponseStatus::SUCCESS, "data" => $route, "message" => config("message.app_data_retrieved")];
        } catch (Exception $ex) {
            Log::alert("ConfigureService - findConfigureById function" . $ex->getMessage());
            return ["status" => ApiResponseStatus::FAILED, "data" => null, "message" => config("message.server_error")];
        }
    }

}
