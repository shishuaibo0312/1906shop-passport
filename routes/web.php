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

Route::get('/info',function(){
    phpinfo();
});


//登录
Route::get('/login','UserController@login');
Route::post('/login_do','UserController@login_do');

//注册
Route::get('/reg','UserController@register');
Route::post('/doreg','UserController@store');

//修改密码
Route::get('/changepass','UserController@changePass');
Route::post('/changepass','UserController@dochangePass');

//退出登录
Route::get('loginexit','UserController@loginexit');

Route::get('pass','FindPass@findpass');  //展示找回页面
Route::post('/doFindpass','FindPass@doFindpass'); //执行发邮件
Route::get('/newpass','FindPass@resPass');  //展示重置密码页面
Route::post('/newpass','FindPass@doResPass');  //执行重置密码

Route::get('/gitlogin','GithubLogin@gitlogin'); //github登录

//api接口
Route::prefix('api')->group(function () {
    Route::post('login','Api\ApiController@login');//登录接口
    Route::post('logout','Api\ApiController@logout');//退出的接口
});
