<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Ubah kolomnya agar bisa menampung semua nilai yang dibutuhkan sementara
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['admin', 'staff', 'produksi', 'pengiriman'])->default('staff')->change();
        });

        // 2. Setelah kolomnya siap, baru update data yang ada
        DB::table('users')->where('role', 'staff')->update(['role' => 'produksi']);

        // 3. Terakhir, ubah kolomnya ke definisi final (tanpa 'staff')
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['admin', 'produksi', 'pengiriman'])->default('produksi')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 1. Ubah kolom menjadi string sementara agar bisa menerima nilai lama
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->change();
        });

        // 2. Kembalikan semua peran non-admin menjadi 'staff'
        DB::table('users')->whereIn('role', ['produksi', 'pengiriman'])->update(['role' => 'staff']);
        
        // 3. Kembalikan struktur kolom enum ke definisi lama
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['admin', 'staff'])->default('staff')->change();
        });
    }
};