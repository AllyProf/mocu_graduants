<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Graduation\BestStudentController;
use App\Http\Controllers\Graduation\GalleryController;
use App\Http\Controllers\Graduation\GraduandController;
use App\Http\Controllers\Graduation\HelpController;
use App\Http\Controllers\Graduation\HomeController;
use App\Http\Controllers\Graduation\LeaderController;
use App\Http\Controllers\Graduation\LiveCeremonyController;
use App\Http\Controllers\Graduation\MenuController;
use App\Http\Controllers\Graduation\TimetableController;
use App\Http\Controllers\Graduation\VenueController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| MoCU Graduation Companion — web routes
|--------------------------------------------------------------------------
| Data   : storage/app/graduation/*.json
| Views  : resources/views/SmartGradu_App
| Assets : public/SmartGradu_App/assets
|
| Public pages are open for ceremony visitors.
| Login is required only for authenticated admin actions (logout).
*/

/* Home dashboard */
Route::get('/', HomeController::class)->name('home');
Route::get('/menu', MenuController::class)->name('menu');

/* Ceremony lists */
Route::get('/graduands', GraduandController::class)->name('graduands');
Route::get('/best-students', BestStudentController::class)->name('best-students');

/* Supporting ceremony pages */
Route::get('/timetable', TimetableController::class)->name('timetable');
Route::get('/venues', VenueController::class)->name('venues');
Route::get('/leaders', LeaderController::class)->name('leaders');
Route::get('/gallery', [GalleryController::class, 'index'])->name('gallery');
Route::post('/gallery', [GalleryController::class, 'store'])->name('gallery.store');
Route::get('/gallery/download', [GalleryController::class, 'download'])->name('gallery.download');
Route::get('/gallery/download/{programme}', [GalleryController::class, 'downloadProgramme'])->name('gallery.download.programme');
Route::get('/live', LiveCeremonyController::class)->name('live');
Route::get('/help', HelpController::class)->name('help');

/* Authentication */
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store']);
});

Route::post('/logout', [LoginController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');
