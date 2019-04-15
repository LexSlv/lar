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

Auth::routes();

Route::get('/home', 'HomeController@index');
Route::get('/lj-parser', 'BlogsParserController@index');
Route::get('/lj-profile', 'BlogsParserController@dead_profiles');
Route::get('/proxy', 'BlogsParserController@proxy');
Route::get('/proxy-rand', 'BlogsParserController@proxy_rand');