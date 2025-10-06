<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Livewire\Livewire; // <-- Tambahkan ini

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Daftarkan semua komponen Livewire Anda secara manual di sini
        Livewire::component('login-component', \App\Http\Livewire\LoginComponent::class);
        Livewire::component('home-component', \App\Http\Livewire\HomeComponent::class);
        Livewire::component('item-component', \App\Http\Livewire\ItemComponent::class);
        Livewire::component('transaction-component', \App\Http\Livewire\TransactionComponent::class);
        Livewire::component('user-component', \App\Http\Livewire\UserComponent::class);
        Livewire::component('report-component', \App\Http\Livewire\ReportComponent::class);
        Livewire::component('report-print-component', \App\Http\Livewire\ReportPrintComponent::class);
        Livewire::component('production-component', \App\Http\Livewire\ProductionComponent::class);
    }
}