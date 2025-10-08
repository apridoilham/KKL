<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->enum('type', [
                'pembelian_masuk',
                'produksi_masuk',
                'produksi_keluar',
                'pengiriman_keluar',
                'rusak'
            ])->change();
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->enum('type', ['in', 'out', 'damaged'])->change();
        });
    }
};