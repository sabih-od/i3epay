<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthenticationsController;
use App\Http\Controllers\SetupsController;
use App\Http\Controllers\StoresController;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/setup', [SetupsController::class, 'index']);
Route::post('/login', [AuthenticationsController::class, 'attemptLogin']);
Route::post('/register/customer', [AuthenticationsController::class, 'registerCustomer']);
Route::post('/register/vendor', [AuthenticationsController::class, 'registerVendor']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/change-password', [AuthenticationsController::class, 'changePassword']);
    Route::post('/all-stores', [StoresController::class, 'allStores']);
    Route::post('/store-subscription', [StoresController::class, 'storeSubscription']);
    Route::put('/update-store-password', [StoresController::class, 'updateStorePassword']);
    Route::post('/view-store-password', [StoresController::class, 'viewStorePassword']);
    Route::get('/store-subscription-requests', [StoresController::class, 'storeSubscriptionRequests']);
    Route::post('/accept-customer-request', [StoresController::class, 'acceptCustomerRequest']);
});