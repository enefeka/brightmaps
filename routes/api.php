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


Route::post('register', 'UserController@register');
Route::post ('login', 'UserController@login');
Route::post('deactivate', 'UserController@deactivateAccount');
Route::post('reactivate', 'UserController@reactivateAccount');
Route::post('updateuser', 'UserController@post_update');
Route::post('insertuser', 'UserController@post_insertUser');
Route::post('deleteuser', 'UserController@post_delete');
Route::post('recover', 'UserController@post_recover');
Route::get('listusers', 'UserController@get_allusers');
Route::get('userbyid', 'UserController@get_userById');
Route::get('finduser', 'UserController@get_user');

Route::apiResource('places', 'PlaceController');
Route::post('deletePlace', 'PlaceController@deletePlace');
Route::post('updatePlace', 'PlaceController@updatePlace');
Route::get('placeData', 'PlaceController@get_place');
