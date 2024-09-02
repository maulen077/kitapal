<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\TelegramBotController;
use App\Http\Controllers\UserRegisterController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

//Route::get('/', function () {
//    return view('welcome');
//});

Route::post('/webhook', [TelegramBotController::class, 'handle']);
Route::post('/webhook/callback', [UserRegisterController::class, 'handleCallbackQuery']);
Route::post('/webhook/message', [UserRegisterController::class, 'handleMessage']);
