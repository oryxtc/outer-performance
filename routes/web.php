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

Route::get('/', function () {
    return view('welcome');
});
Route::get('/home', 'HomeController@index')->name('home');

Route::group(['middleware' => 'admin','middleware'=>['web','errors.session']], function () {
    Auth::routes();
});


Route::group(['prefix' => 'admin','middleware'=>['web','errors.session']], function () {
    require base_path().'/routes/voyager.php';
});

Route::group(['prefix' => 'wechat','middleware'=>['web','wechat.oauth']], function () {
    Route::any('/', 'WechatController@serve');

    Route::any('/demoServe', 'WechatController@demoServe');

    Route::get('/createMenu', 'WechatController@createMenu');
});
