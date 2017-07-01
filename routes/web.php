<?php

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

use Illuminate\Routing\Route;

\Route::get('/', function () {
    return view('welcome');
});

\Route::get('/home', 'HomeController@index')->name('home');

\Route::group(['middleware' => 'admin','middleware'=>['web','errors.session']], function () {
    \Auth::routes();
});


\Route::group(['prefix' => 'admin','middleware'=>['web','errors.session']], function () {
    require base_path().'/routes/voyager.php';
    \Route::any('getUsersList','Voyager\VoyagerUserController@getUsersList')->name('getUsersList');
});

\Route::group(['as'=>'wechat.','prefix' => 'wechat'], function () {
    \Route::any('/', 'WechatController@serve');

    \Route::any('/demoServe', 'WechatController@demoServe');

    \Route::get('/createMenu', 'WechatController@createMenu');

    \Route::any('/bind', 'WechatController@bind')->name('bind');
});

\Route::group(['as'=>'excel.'],function (){
    //导出用户模板
    \Route::get('exportUsersTemplate', 'ExcelController@exportUsersTemplate')->name('exportUsersTemplate');
    //导入用户
    \Route::post('importUsers', 'ExcelController@importUsers')->name('importUsers');
    //导出用户
    \Route::any('exportUsers', 'ExcelController@exportUsers')->name('exportUsers');
});