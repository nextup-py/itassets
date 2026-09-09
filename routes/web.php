<?php

use App\Http\Controllers\AssetPublicController;
use App\Http\Controllers\AssetQrSheetController;
use App\Http\Controllers\AssignmentPdfController;
use Illuminate\Support\Facades\Route;

// The panel used to live at /admin; keep old bookmarks/links working by
// redirecting to the equivalent path now that it's served from /.
Route::redirect('/admin', '/');
Route::get('/admin/{path}', fn (string $path) => redirect('/' . $path))
    ->where('path', '.*');

Route::get('/assignments/{assignment}/pdf', [AssignmentPdfController::class, 'download'])
    ->middleware('auth')
    ->name('assignments.pdf');

Route::get('/assignments/pdf-preview/{token}', [AssignmentPdfController::class, 'preview'])
    ->middleware('auth')
    ->name('assignments.pdf-preview');

Route::get('/assets/{asset}/public', [AssetPublicController::class, 'show'])
    ->name('assets.public');

Route::get('/assets/{asset}/qr-download', [AssetPublicController::class, 'qrImage'])
    ->name('assets.qr-image');

Route::get('/assets/qr-sheet/{token}', [AssetQrSheetController::class, 'show'])
    ->middleware('auth')
    ->name('assets.qr-sheet');
