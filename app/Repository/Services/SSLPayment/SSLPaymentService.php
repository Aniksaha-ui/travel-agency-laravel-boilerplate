<?php

namespace App\Repository\Services\SSLPayment;

use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Twilio\Rest\Client;

class SSLPaymentService
{
    function initSSLTransaction($data){
       try{
            $url = env('IS_SANDBOX')
                ? 'https://dev-securepay.sslcommerz.com/gwprocess/v4/api.php'
                : 'https://dev-securepay.sslcommerz.com/gwprocess/v4/api.php';

            $response = Http::asForm()->post($url, $data);
            $sslResponse = $response->json();
            if (!empty($sslResponse['GatewayPageURL'])) {
                return [
                    'status' => 'success',
                    'url' => $sslResponse['GatewayPageURL'],
                    'tran_id' => $data['tran_id'],
                    'message' => 'Redirect to SSLCommerz gateway'
                ];
            } else {
                return ['status' => 'failed', 'message' => 'SSLCommerz gateway initialization failed'];
            }


       }catch(Exception $ex){
            return ['status' => 'failed', 'message' => $ex->getMessage()];
            
       }    
     
   }
}
