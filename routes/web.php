<?php

use App\Http\Controllers\AboutController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\SubmitController;
use App\Http\Controllers\VisitController;
use App\Http\Controllers\Admin\IndexController;
use App\Http\Controllers\Admin\StatsController;
use App\Http\Controllers\Admin\SubmissionController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');
Route::get('/about', AboutController::class)->name('about');
Route::get('/visit/{slug}', VisitController::class)->name('visit');
Route::get('/submit', [SubmitController::class, 'create'])->name('submit');
Route::post('/submit', [SubmitController::class, 'store'])->name('submit.store');

Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/stats', StatsController::class)->name('stats');
    Route::get('/submissions', [SubmissionController::class, 'index'])->name('submissions.index');
    Route::patch('/submissions/{submission}', [SubmissionController::class, 'update'])->name('submissions.update');
    Route::get('/indexes', [IndexController::class, 'index'])->name('indexes.index');
    Route::get('/indexes/{index}/edit', [IndexController::class, 'edit'])->name('indexes.edit');
    Route::patch('/indexes/{index}', [IndexController::class, 'update'])->name('indexes.update');
});

require __DIR__.'/settings.php';
