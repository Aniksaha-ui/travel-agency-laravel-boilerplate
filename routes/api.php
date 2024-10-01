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

    /*************************************routes api **************************************/
    
    Route::get('/admin/routes','Admin\RouteController@index');
    Route::post('/admin/routes','Admin\RouteController@insert');
    Route::get('/admin/routes/{id}','Admin\RouteController@findRouteById');
    Route::delete('/admin/routes/{id}','Admin\RouteController@delete');

    /*************************************routes api **************************************/


    /*************************************vehicles api **************************************/

    Route::get('/admin/vehicles','Admin\Vehicles\VehiclesController@index');
    Route::post('/admin/users','Admin\Users\UserController@insert');
    Route::get('/admin/users/{id}','Admin\Users\UserController@findUserById');
    Route::delete('/admin/users/{id}','Admin\Users\UserController@delete');
    
    /*************************************users api ***************************************/


    /*************************************users api **************************************/

    Route::get('/admin/users','Admin\Users\UserController@index');
    Route::post('/admin/users','Admin\Users\UserController@insert');
    Route::get('/admin/users/{id}','Admin\Users\UserController@findUserById');
    Route::delete('/admin/users/{id}','Admin\Users\UserController@delete');
    
    /*************************************users api ***************************************/


    

});