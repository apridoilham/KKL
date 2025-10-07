<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Menggunakan DB::statement untuk mengubah ENUM dengan aman
        DB::statement("
            ALTER TABLE transactions MODIFY COLUMN type ENUM(
                'masuk_mentah',
                'masuk_jadi',
                'keluar_terpakai',
                'keluar_dikirim',
                'keluar_mentah',
                'rusak'
            )
        ");
    }

    public function down(): void
    {
        DB::statement("
            ALTER TABLE transactions MODIFY COLUMN type ENUM(
                'masuk_mentah',
                'masuk_jadi',
                'keluar_terpakai',
                'keluar_dikirim',
                'rusak'
            )
        ");
    }
};