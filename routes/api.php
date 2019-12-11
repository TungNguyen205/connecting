<?php

use Illuminate\Http\Request;

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

Route::get('install', function () {
    return view('install');
});

Route::post('install', 'AppController@installHandle')->name('app.installHandle');

Route::get('auth', 'AppController@auth')->name('app.auth');
Route::group(['prefix' => 'product'], function() {
    Route::get('list', 'ProductController@list')->name('product.list');
});