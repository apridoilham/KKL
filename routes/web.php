<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

use App\Livewire\HomeComponent;
use App\Livewire\ItemComponent;
use App\Livewire\LoginComponent;
use App\Livewire\ReportComponent;
use App\Livewire\ReportPrintComponent;
use App\Livewire\TransactionComponent;
use App\Livewire\UserComponent;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Rute untuk pengguna yang belum login (guest)
Route::middleware('guest')->group(function () {
    Route::get('/login', LoginComponent::class)->name('login');
});

// Rute untuk pengguna yang sudah login (authenticated)
Route::middleware(['auth'])->group(function () {
    Route::get('/', HomeComponent::class)->name('home');
    Route::get('/item', ItemComponent::class);
    Route::get('/transaction', TransactionComponent::class);
    Route::get('/report', ReportComponent::class);
    Route::get('/user', UserComponent::class);
    Route::get('/report/print', ReportPrintComponent::class)->name('print.report');
});

// Rute untuk proses logout
Route::get('/logout', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect('/login');
})->name('logout');