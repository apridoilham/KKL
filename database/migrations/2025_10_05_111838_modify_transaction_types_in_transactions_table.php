<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            // Mengubah tipe kolom untuk mendukung jenis transaksi yang lebih spesifik
            $table->enum('type', [
                'pembelian_masuk',    // Sebelumnya 'in'
                'produksi_masuk',     // BARU: Barang jadi yang selesai diproduksi
                'produksi_keluar',    // BARU: Bahan mentah yang dipakai untuk produksi
                'pengiriman_keluar',  // Sebelumnya 'out'
                'rusak'               // Sebelumnya 'damaged'
            ])->change();
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            // Mengembalikan ke state semula jika migrasi di-rollback
             $table->enum('type', ['in', 'out', 'damaged'])->change();
        });
    }
};