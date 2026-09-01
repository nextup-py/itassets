<?php

use App\Http\Controllers\AssignmentPdfController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/assignments/{assignment}/pdf', [AssignmentPdfController::class, 'download'])
    ->middleware('auth')
    ->name('assignments.pdf');
