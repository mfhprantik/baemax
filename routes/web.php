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

Route::get('/', 'AccountController@index')->name('index');
Route::post('/login', 'AccountController@login')->name('login');
Route::post('/signup', 'AccountController@signup')->name('signup');
Route::get('/upload', 'DashboardController@dashboard')->name('upload')->middleware('auth');
Route::post('/doUpload', 'DashboardController@upload')->name('doUpload')->middleware('auth');
Route::get('/dashboard', 'DashboardController@dashboard')->name('dashboard')->middleware('auth');
Route::get('/logout', 'AccountController@logout')->name('logout')->middleware('auth');
Route::post('/like', 'DashboardController@like')->name('like')->middleware('auth');
Route::post('/dislike', 'DashboardController@dislike')->name('dislike')->middleware('auth');