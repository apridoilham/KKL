<?php

use App\Http\Controllers\ReportDownloadController;
use App\Http\Livewire\HomeComponent;
use App\Http\Livewire\ItemComponent;
use App\Http\Livewire\LoginComponent;
use App\Http\Livewire\ProductionComponent;
use App\Http\Livewire\ProfileComponent;
use App\Http\Livewire\ReportComponent;
use App\Http\Livewire\ReportPrintComponent;
use App\Http\Livewire\TransactionComponent;
use App\Http\Livewire\UserComponent;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', LoginComponent::class)->name('login');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/', HomeComponent::class)->name('home')->middleware('non-admin.redirect');
    Route::get('/profile', ProfileComponent::class)->name('profile');
    Route::get('/item', ItemComponent::class);
    Route::get('/transaction', TransactionComponent::class);
    Route::get('/production', ProductionComponent::class);
    Route::get('/report', ReportComponent::class);
    Route::get('/user', UserComponent::class);
    Route::get('/report/print', ReportPrintComponent::class)->name('print.report');

    Route::get('/report/download/{type}', [ReportDownloadController::class, 'download'])->name('report.download');
});

Route::get('/logout', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect('/login');
})->name('logout');