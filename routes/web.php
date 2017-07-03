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
    //获取员工名称列表
    \Route::any('getUsersNameList','Voyager\VoyagerUserController@getUsersNameList');

    \Route::any('getUsersList','Voyager\VoyagerUserController@getUsersList')->name('getUsersList');
    \Route::any('getProvidentsList','Voyager\VoyagerProvidentController@getProvidentsList')->name('getProvidentsList');
    \Route::any('getAttendancesList','Voyager\VoyagerAttendanceController@getAttendancesList')->name('getAttendancesList');
    \Route::any('getMemosList','Voyager\VoyagerMemoController@getMemosList')->name('getMemosList');
});

\Route::group(['as'=>'excel.'],function (){
    //导出用户模板
    \Route::get('exportUsersTemplate', 'ExcelController@exportUsersTemplate')->name('exportUsersTemplate');
    //导入用户
    \Route::post('importUsers', 'ExcelController@importUsers')->name('importUsers');
    //导出用户
    \Route::any('exportUsers', 'ExcelController@exportUsers')->name('exportUsers');

    //导出社保和公积金模板
    \Route::get('exportProvidentsTemplate', 'ExcelController@exportProvidentsTemplate')->name('exportProvidentsTemplate');
    //导入社保和公积金模板
    \Route::post('importProvidents', 'ExcelController@importProvidents')->name('importProvidents');
    //导出社保和公积金模板
    \Route::any('exportProvidents', 'ExcelController@exportProvidents')->name('exportProvidents');


    //导出考勤模板
    \Route::get('exportAttendancesTemplate', 'ExcelController@exportAttendancesTemplate')->name('exportAttendancesTemplate');
    //导入考勤
    \Route::post('importAttendances', 'ExcelController@importAttendances')->name('importAttendances');
    //导出考勤
    \Route::any('exportAttendances', 'ExcelController@exportAttendances')->name('exportAttendances');

    //导出备忘录模板
    \Route::get('exportMemosTemplate', 'ExcelController@exportAttendancesTemplate')->name('exportMemosTemplate');
    //导入备忘录
    \Route::post('importMemos', 'ExcelController@importAttendances')->name('importMemos');
    //导出备忘录
    \Route::any('exportMemos', 'ExcelController@exportAttendances')->name('exportMemos');
});


\Route::group(['as'=>'wechat.','prefix' => 'wechat'], function () {
    \Route::any('/', 'WechatController@serve');

    \Route::any('/demoServe', 'WechatController@demoServe');

    \Route::get('/createMenu', 'WechatController@createMenu');

    \Route::any('/bind', 'WechatController@bind')->name('bind');
});

