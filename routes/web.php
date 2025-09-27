<?php

use App\Livewire\HomeComponent;
use App\Livewire\ItemComponent;
use App\Livewire\LoginComponent;
use App\Livewire\ReportComponent;
use App\Livewire\ReportPrintComponent;
use App\Livewire\TransactionComponent;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/login',LoginComponent::class)->name('login');
Route::get('/logout', function () {
    Auth::logout();
    return redirect('login');
})->name('logout');

Route::middleware(['auth'])->group(function () {
    Route::get('/',HomeComponent::class)->name('home');
    Route::get('/item',ItemComponent::class);
    Route::get('/transaction',TransactionComponent::class);
    Route::get('/report',ReportComponent::class);
    Route::get('/report/print',ReportPrintComponent::class)->name('print.report');
});
