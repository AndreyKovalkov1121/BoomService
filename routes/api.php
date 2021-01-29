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

Route::post('login', 'ApiController@login');
Route::post('address_verify', 'ApiController@address_verify');
Route::post('register', 'ApiController@register');
Route::post('invite', 'ApiController@invite_users');

Route::group(['middleware' => ['jwt-auth']], function () {
    /***** add category ****/
    Route::post('create_categories', 'HomeController@create_categories');
    Route::get('categories', 'HomeController@categories');
    /***** add partners ****/
    Route::post('create_partners', 'HomeController@create_partners');
    Route::get('partners', 'HomeController@partners');
    
});

