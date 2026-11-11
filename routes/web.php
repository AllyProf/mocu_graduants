<?php

use App\Http\Controllers\Graduation\BestStudentController;
use App\Http\Controllers\Graduation\GraduandController;
use App\Http\Controllers\Graduation\HelpController;
use App\Http\Controllers\Graduation\HomeController;
use App\Http\Controllers\Graduation\LiveCeremonyController;
use App\Http\Controllers\Graduation\TimetableController;
use App\Http\Controllers\Graduation\VenueController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Graduation Companion
| Data: storage/app/graduation/*.json
| Views: resources/views/SmartGradu_App
| Assets: public/SmartGradu_App/assets
|--------------------------------------------------------------------------
*/

Route::get('/', HomeController::class)->name('home');
Route::get('/graduands', GraduandController::class)->name('graduands');
Route::get('/best-students', BestStudentController::class)->name('best-students');
Route::get('/timetable', TimetableController::class)->name('timetable');
Route::get('/venues', VenueController::class)->name('venues');
Route::get('/live', LiveCeremonyController::class)->name('live');
Route::get('/help', HelpController::class)->name('help');
