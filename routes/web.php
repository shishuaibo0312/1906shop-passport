<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

//登录注册
Route::prefix('admin')->group(function () {
	Route::any('login','Admin\UserController@login');
	Route::post('login_do','Admin\UserController@login_do');
	Route::get('mycenter','Admin\UserController@mycenter');
});
