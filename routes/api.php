<?php

use App\Http\Controllers\CommunityController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


Route::post("/process", [CommunityController::class, 'process']);

Route::post("test", function (Request $request) {

    $getUser = DB::table('users')->where('phone_number', $request->phoneNumber)->first();
    $getCommunity = DB::table('communities')->where('id', $getUser->community_id)->first();
    return $getCommunity;
});
