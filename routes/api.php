<?php

use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});



Route::post('/login', [AuthController::class, 'login']);

Route::middleware(['auth:sanctum', 'admin'])->group(function () {

    /*************************************routes api start**************************************/
    
    Route::get('/admin/routes','Admin\RouteController@index');
    Route::post('/admin/routes','Admin\RouteController@insert');
    Route::get('/admin/routes/{id}','Admin\RouteController@findRouteById');
    Route::delete('/admin/routes/{id}','Admin\RouteController@delete');

    /*************************************routes api end**************************************/


    /*************************************vehicles api start**************************************/

    Route::get('/admin/vehicles','Admin\Vehicles\VehiclesController@index');
    Route::post('/admin/vehicles','Admin\Vehicles\VehiclesController@insert');
    Route::get('/admin/vehicles/{id}','Admin\Vehicles\VehiclesController@findVehicleById');
    Route::delete('/admin/vehicles/{id}','Admin\Vehicles\VehiclesController@delete');
    
    /*************************************vehicles api end**************************************/



    /*************************************vehicles api start**************************************/

    Route::get('/admin/seat','Admin\Seat\SeatController@index');
    Route::post('/admin/seat','Admin\Seat\SeatController@insert');
    Route::get('/admin/seat/{id}','Admin\Seat\SeatController@findVehicleById');
    Route::delete('/admin/seat/{id}','Admin\Seat\SeatController@delete');
    
    /*************************************vehicles api end**************************************/


    /*************************************users api start**************************************/

    Route::get('/admin/users','Admin\Users\UserController@index');
    Route::post('/admin/users','Admin\Users\UserController@insert');
    Route::get('/admin/users/{id}','Admin\Users\UserController@findUserById');
    Route::delete('/admin/users/{id}','Admin\Users\UserController@delete');
    
    /*************************************users api end**************************************/


    

});