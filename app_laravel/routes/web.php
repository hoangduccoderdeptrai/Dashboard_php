<?php

use App\Models\test;
use GuzzleHttp\Psr7\Query;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\testController;
use App\Models\Fight;
use Illuminate\Support\Arr;

Route::get('/',[testController::class,'home']);
// Movie 
Route::get('/tables',[testController::class,'table']);
Route::put('/update-movie/{id}',[testController::class,'update_movie']);
Route::get('/add-movie',[testController::class,'add_movie']);
Route::post('/add-movie',[testController::class,'post_movie'])    ;
Route::get('/get-movie/{id}',[testController::class,'get_movie']); 
Route::delete('/delete-movie/{id}',[testController::class,'delete_movie']);  
;

// Voucher
Route::get('/live-search-voucher',[testController::class,'live_search_voucher']);
Route::get('/voucher-management',[testController::class,'voucher_management']);
Route::post('/add-voucher',[testController::class,'add_voucher']);
Route::get('/get-voucher/{id}',[testController::class,'get_voucher']);
Route::delete('/delete-voucher/{id}',[testController::class,'delete_voucher']);
Route::put('/update-voucher/{id}',[testController::class,'update_voucher']);

// Users
Route::get('/users-management',[testController::class,'users_management']);
Route::get('/live-search-users',[testController::class,'live_search_users']);
Route::post('/add-user',[testController::class,'add_user']);


