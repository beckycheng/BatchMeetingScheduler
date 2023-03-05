<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MeetingController;

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
    return view('guest');
})->middleware('guest');

Route::resource('meeting', MeetingController::class);
Route::get('meeting/{meeting}/choose', [MeetingController::class, 'choose'])->name('meeting.choose');

Auth::routes(['verify' => true]);

Route::get('/home', function () {
    return redirect()->route('meeting.index');
});
