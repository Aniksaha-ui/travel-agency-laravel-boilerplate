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


Route::middleware(['auth:sanctum', 'query.monitor'])->group(function () {
    Route::get('/admin/menu', 'Admin\MenuController@index');
});

Route::middleware(['auth:sanctum', 'admin', 'query.monitor'])->group(function () {

    /*************************************routes api start**************************************/
    Route::get('admin/dashboard', 'AdminController@dashboard');
    // Route::get('admin/dashboard/databaseList', action: 'AdminController@databaseList');
    Route::get('/admin/routes', 'Admin\RouteController@index');
    Route::get('/admin/routes/dropdown', 'Admin\RouteController@dropdown');
    Route::post('/admin/routes', 'Admin\RouteController@insert');
    Route::get('/admin/routes/{id}', 'Admin\RouteController@findRouteById');
    Route::delete('/admin/routes/{id}', 'Admin\RouteController@delete');

    /*************************************routes api end**************************************/





    /*************************************users api start**************************************/

    Route::get('/admin/users', 'Admin\Users\UserController@index');
    Route::post('/admin/users', 'Admin\Users\UserController@insert');
    Route::get('/admin/users/{id}', 'Admin\Users\UserController@findUserById');
    Route::delete('/admin/users/{id}', 'Admin\Users\UserController@delete');

    /*************************************users api end**************************************/




    /*************************************vehicles api start**************************************/

    Route::get('/admin/vehicles', 'Admin\Vehicles\VehiclesController@index');
    Route::get('/admin/vehicles/dropdown', 'Admin\Vehicles\VehiclesController@dropdown');
    Route::post('/admin/vehicles', 'Admin\Vehicles\VehiclesController@insert');
    Route::get('/admin/vehicles/{id}', 'Admin\Vehicles\VehiclesController@findVehicleById');
    Route::delete('/admin/vehicles/{id}', 'Admin\Vehicles\VehiclesController@delete');
    Route::post('/admin/trip/vehicle/booking', 'Admin\Vehicles\VehiclesController@vehicleBooking');


    /*************************************vehicles api end**************************************/



    /*************************************seats api start**************************************/

    Route::get('/admin/seat', 'Admin\Seat\SeatController@index');
    Route::post('/admin/seat', 'Admin\Seat\SeatController@insert');
    Route::get('/admin/seat/{id}', 'Admin\Seat\SeatController@findVehicleById');
    Route::delete('/admin/seat/{id}', 'Admin\Seat\SeatController@delete');

    /*************************************seats api end**************************************/

    /*************************************Trips api end**************************************/
    Route::get('/admin/trip', 'Admin\Trip\TripController@index');
    Route::get('/admin/trip/dropdown', 'Admin\Trip\TripController@dropdown');
    Route::post('/admin/trip', 'Admin\Trip\TripController@insert');
    Route::get('/admin/trip/{tripId}', 'Admin\Trip\TripController@inactiveTripByTripId');
    Route::get('/admin/single/trip/{id}', 'Admin\Trip\TripController@singleTrip');
    Route::post('/admin/trip/update/{id}', 'Admin\Trip\TripController@update');


    /*************************************seats api end**************************************/

    /*************************************Booking**************************************/
    Route::get('/admin/booking', 'Admin\booking\bookingController@index');
    Route::post('/admin/booking-invoice', 'Admin\booking\bookingController@invoice');
    Route::post('/admin/tripsummery', 'Admin\booking\bookingController@tripwiseBooking');
    Route::post('/admin/dailybookingReport', 'Admin\booking\bookingController@dailybookingReport');

    /*************************************Booking**************************************/





    /***************************************accounting api end ***********************************/
    Route::get('/admin/accountBalance', 'Admin\Reports\ReportController@accountBalance');
    Route::get('/admin/accountHistory/{accountId}', 'Admin\Reports\ReportController@accountHistory');

    /***************************************accounting api end ***********************************/


    /****************************************packages api start ********************************** */

    Route::post('/admin/packages', 'Admin\Package\PackageController@index');
    Route::post('/admin/packages/create', 'Admin\Package\PackageController@create');

    /****************************************packages api start ********************************** */

    /*************************************Guide api start **********************************/

    Route::get('admin/guide', 'Admin\guide\guideController@getGuides');
    Route::post('admin/guide', 'Admin\guide\guideController@store');
    Route::get('admin/guide/{id}', 'Admin\guide\guideController@getGuideById');
    Route::post('admin/guide/update', 'Admin\guide\guideController@update');
    Route::get('guide/performance', 'Admin\guide\guideController@getGuidePerformance');
    Route::get('admin/guide/dropdown', 'Admin\guide\guideController@getGuidesdropdown');

   #Guide api start ********************************** */

    Route::get('admin/refund', 'Admin\refund\refundController@getRefunds');
    Route::post('admin/refund/disburse', 'Admin\refund\refundController@disburseRefund');

    #Hotel management api
    Route::get('admin/hotel', 'Admin\hotel\hotelController@getHotels');
    Route::post('admin/hotel', 'Admin\hotel\hotelController@store');
    Route::post('admin/hotel/update/{hotel_id}', 'Admin\hotel\hotelController@update');

    Route::get('admin/hotel/checkin', 'Admin\hotel\hotelController@hotelCheckinList');
    Route::post('/hotel/update/checkin', 'User\hotel\hotelController@hotelCheckinStatusUpdate');
    Route::get('admin/hotel/bookings', 'Admin\hotel\hotelController@hotelBooking');

    #transactions and payment api
    Route::get('admin/transaction', 'Admin\transaction\transactionController@getTransactions');

    #tickets api
    Route::get('admin/tickets', 'Admin\tickets\ticketsController@getTickets');
    Route::post('admin/tickets/update/{id}', 'Admin\tickets\ticketsController@updateTicket');
    Route::get('admin/monitoring', 'Admin\monitoring\monitoringController@monitoring');

    #online payment configure
    Route::get('admin/online-configure', 'Admin\configure\OnlineConfigureController@onlineConfigureList');
    Route::post('admin/online-configure', 'Admin\configure\OnlineConfigureController@storeNewConfigure');
    Route::get('admin/online-configure/{id}', 'Admin\configure\OnlineConfigureController@findConfigureById');
    Route::post('admin/online-configure/update', 'Admin\configure\OnlineConfigureController@updateOnlineConfigure');

    #account history
    Route::post('admin/account-history', 'Admin\account\AccountHistoryController@accountHistory');





    #reports api

    Route::get('/admin/vehiclewisetotalseat', 'Admin\Reports\ReportController@vehicleWiseSeatTotalReport');
    Route::get('/admin/vehiclewiseseat/{id}', 'Admin\Reports\ReportController@vehicleWiseAllSeatReport');
    Route::get('/admin/packageWiseBooking', 'Admin\Reports\ReportController@packageWiseBooking');
    Route::post('/admin/useageOfVehicle', 'Admin\Reports\ReportController@useageOfVehicle');
    Route::get('/admin/tripwiseBookingUsers/{id}', 'Admin\Reports\ReportController@tripwiseBookingUsers');
    Route::get('/admin/tripPerformance', 'Admin\Reports\ReportController@tripPerformance');
    Route::get('/admin/packagePerformance', 'Admin\Reports\ReportController@packagePerformance');
    Route::get('/admin/guideEfficency', 'Admin\Reports\ReportController@guideEfficencyReport');
    Route::get('/admin/customerValue', 'Admin\Reports\ReportController@customerValueReport');
    Route::get('/admin/transactionHistory', 'Admin\Reports\ReportController@transactionHistoryReport');
    Route::get('/admin/monthRunningBalance', 'Admin\Reports\ReportController@monthRunningBalanceReport');
    Route::get('/admin/monthlyDailyBalanceReport', 'Admin\Reports\ReportController@dailyBalanceReport');
    Route::get('admin/financial_report', 'Admin\Reports\ReportController@financialReport');
    Route::get('/admin/financial_report/{financialReportId}', 'Admin\Reports\ReportController@financialReportById');


    Route::get('/admin/booking-summary', 'Admin\Reports\ReportController@bookingSummary');
    Route::get('/admin/overall-sales-summary', 'Admin\Reports\ReportController@salesSummary');
    Route::get('/admin/route-wise-sales-summary', 'Admin\Reports\ReportController@routeWiseSalesSummary');
    Route::get('/admin/route-wise-sales-summary', 'Admin\Reports\ReportController@routeWiseSalesSummary');
    Route::get('/admin/current-month-trip-sales-report', 'Admin\Reports\ReportController@currentMonthTripSales');

    #reports api



});

/************************************* User api start *******************************/

Route::post('/trips', 'User\trip\TripController@index');
Route::get('/trip/{id}', 'User\trip\TripController@singleTrip');
Route::get('/packages', 'User\trip\PackageController@getPackages');
Route::get('/packages/{id}', 'User\trip\PackageController@singlePackageInformation');   //can be also used for admin
Route::get('/tripwisepackages/{tripId}', 'User\trip\PackageController@tripwisePackages');
Route::post('/hotels', 'User\hotel\hotelController@index')->name('hotel.list');
Route::get('/hotel/{hotel_id}', 'Admin\hotel\hotelController@getHotelById');



// Route::post('guide/performance', 'Admin\guide\guideController@guidePerformance');
// Route::get('admin/guide/{id}', 'Admin\guide\guideController@getGuideById');

Route::middleware(['auth:sanctum', 'users','query.monitor'])->group(function () {
    Route::post('/user/tripsummery', 'Admin\booking\bookingController@tripwiseBooking');
    Route::post('/booking', 'User\booking\bookingController@tripBooking');
    Route::post('/mybookings', 'User\booking\bookingController@mybookings');
    Route::post('/invoice', 'User\booking\bookingController@invoice');
    Route::post('/refund', 'User\refund\refundController@refund');
    Route::post('/booking/cancle', 'User\booking\bookingController@cancleBooking');
    Route::post('/packages/booking', 'User\trip\PackageController@packageBooking');
    Route::post('/hotel/booking', 'User\hotel\hotelController@hotelBookings');
    Route::post('/user/createTicket', 'User\ticket\ticketController@createTicket');
    Route::get('/user/ticketList', 'User\ticket\ticketController@ticketList');
});


/************************************* User api start *******************************/
Route::middleware(['auth:sanctum', 'guide'])->group(function () {
    Route::post('guide/costingByPackageList', 'Admin\guide\guideController@costingByPackageList');
    Route::post('guide/myAssignPackage', 'Admin\guide\guideController@myAssignPackages');
    Route::post('guide/myFeedBackByPackage', 'Admin\guide\guideController@myFeedBackByPackage');
    Route::post('admin/guide/costingbypackage', 'Admin\guide\guideController@costingByPackage');
    Route::post('admin/guide/costingbypackage/update', 'Admin\guide\guideController@updatePackageCosting');
    Route::post('admin/guide/costingbypackage/{id}', 'Admin\guide\guideController@findCostingById');
});



// Route::get('admin/guidepackagesassign', 'Admin\guide\guideController@getGuidePackageAssign');



// costing of packages and others



//payment redirect
 Route::post('/payment/success', 'User\booking\bookingController@successPayment')->name('payment.success');
Route::post('/users/order/fail', [OrderController::class, 'fail'])->name('payment.fail');
Route::post('/users/order/cancel', [OrderController::class, 'cancel'])->name('payment.cancel');