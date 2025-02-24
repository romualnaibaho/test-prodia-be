<?php

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

// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::post('/register', 'AuthController@register');
Route::post('/verify-otp', 'AuthController@verifyOtp');
Route::post('/resend-otp', 'AuthController@resendOtp');
Route::post('/login', 'AuthController@login');

$router->group(['prefix' => 'weather/'], function ($app) {
    $app->get('/all', 'WeatherController@getAll');
    $app->get('/locations', 'WeatherController@getLocations');
    $app->get('/location/{id}', 'WeatherController@getLocationDetail');
    $app->post('/insert', 'WeatherController@insert');
});