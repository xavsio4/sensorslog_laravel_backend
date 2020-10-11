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

Route::post('auth/login','App\Http\Controllers\AuthController@login');
Route::post('auth/register','App\Http\Controllers\AuthController@register');

Route::group(['middleware' => 'auth:api'], function() {
    Route::get('auth/user', function(Request $request) {
        return auth()->user();
    });
    Route::get('v1/measures','App\Http\Controllers\api\v1\MeasureController@index');
    Route::get('auth/createapikey', 'App\Http\Controllers\AuthController@createapikey');
    Route::get('auth/renewapikey', 'App\Http\Controllers\AuthController@renewapikey');
    Route::get('auth/listkey', 'App\Http\Controllers\AuthController@apikeyList');
    Route::post('auth/destroy', 'App\Http\Controllers\AuthController@destroy');
});

Route::get('v1/measure/create','App\Http\Controllers\api\v1\MeasureController@create');
Route::get('v1/measure/counting','App\Http\Controllers\api\v1\MeasureController@counting');