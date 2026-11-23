<?php

use App\Http\Controllers\Graduation\BestStudentController;
use App\Http\Controllers\Graduation\GalleryController;
use App\Http\Controllers\Graduation\GraduandController;
use App\Http\Controllers\Graduation\HelpController;
use App\Http\Controllers\Graduation\HomeController;
use App\Http\Controllers\Graduation\LeaderController;
use App\Http\Controllers\Graduation\ListPdfController;
use App\Http\Controllers\Graduation\LiveCeremonyController;
use App\Http\Controllers\Graduation\MenuController;
use App\Http\Controllers\Graduation\TimetableController;
use App\Http\Controllers\Graduation\VenueController;
use App\Http\Controllers\Graduation\VoiceTtsController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| MoCU Graduation Companion — web routes
|--------------------------------------------------------------------------
| Data   : storage/app/graduation/*.json
| Views  : resources/views/SmartGradu_App
| Assets : public/SmartGradu_App/assets
|
| Public pages are open for ceremony visitors (no staff login).
*/

/* Home dashboard */
Route::get('/', HomeController::class)->name('home');
Route::get('/menu', MenuController::class)->name('menu');

/* Ceremony lists + PDF export */
Route::get('/graduands', GraduandController::class)->name('graduands');
Route::get('/graduands/pdf', [ListPdfController::class, 'graduands'])->name('graduands.pdf');
Route::get('/best-students', BestStudentController::class)->name('best-students');
Route::get('/best-students/pdf', [ListPdfController::class, 'bestStudents'])->name('best-students.pdf');

/* Supporting ceremony pages */
Route::get('/timetable', TimetableController::class)->name('timetable');
Route::get('/venues', VenueController::class)->name('venues');
Route::get('/leaders', LeaderController::class)->name('leaders');
Route::get('/gallery', [GalleryController::class, 'index'])->name('gallery');
Route::post('/gallery', [GalleryController::class, 'store'])->name('gallery.store');
Route::get('/live', LiveCeremonyController::class)->name('live');
Route::get('/help', HelpController::class)->name('help');
Route::get('/voice/tts', VoiceTtsController::class)->name('voice.tts');

/* Old auth URLs → home (login removed) */
Route::redirect('/login', '/')->name('login');
Route::match(['get', 'post'], '/logout', fn () => redirect()->route('home'))->name('logout');
