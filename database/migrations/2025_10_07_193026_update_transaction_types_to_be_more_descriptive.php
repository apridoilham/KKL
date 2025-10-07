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
        // 1. Perluas definisi ENUM untuk mencakup nilai LAMA dan BARU
        DB::statement("
            ALTER TABLE transactions MODIFY COLUMN type ENUM(
                'pembelian_masuk', 'produksi_masuk', 'produksi_keluar', 'pengiriman_keluar', 'rusak',
                'masuk_mentah', 'masuk_jadi', 'keluar_terpakai', 'keluar_dikirim'
            )
        ");

        // Peta untuk konversi tipe lama ke tipe baru
        $typeMap = [
            'pembelian_masuk' => 'masuk_mentah',
            'produksi_masuk' => 'masuk_jadi',
            'produksi_keluar' => 'keluar_terpakai',
            'pengiriman_keluar' => 'keluar_dikirim',
            'rusak' => 'rusak',
        ];

        // 2. Update data yang ada
        foreach ($typeMap as $old => $new) {
            DB::table('transactions')->where('type', $old)->update(['type' => $new]);
        }

        // 3. Persempit definisi ENUM hanya ke nilai BARU
        DB::statement("
            ALTER TABLE transactions MODIFY COLUMN type ENUM(
                'masuk_mentah', 'masuk_jadi', 'keluar_terpakai', 'keluar_dikirim', 'rusak'
            )
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 1. Perluas lagi definisi ENUM untuk mencakup nilai BARU dan LAMA
        DB::statement("
            ALTER TABLE transactions MODIFY COLUMN type ENUM(
                'pembelian_masuk', 'produksi_masuk', 'produksi_keluar', 'pengiriman_keluar', 'rusak',
                'masuk_mentah', 'masuk_jadi', 'keluar_terpakai', 'keluar_dikirim'
            )
        ");

        // Peta untuk mengembalikan dari tipe baru ke tipe lama
        $reverseTypeMap = [
            'masuk_mentah' => 'pembelian_masuk',
            'masuk_jadi' => 'produksi_masuk',
            'keluar_terpakai' => 'produksi_keluar',
            'keluar_dikirim' => 'pengiriman_keluar',
            'rusak' => 'rusak',
        ];

        // 2. Kembalikan data ke nilai lama
        foreach ($reverseTypeMap as $new => $old) {
            DB::table('transactions')->where('type', $new)->update(['type' => $old]);
        }
        
        // 3. Persempit kembali definisi ENUM hanya ke nilai LAMA
        DB::statement("
            ALTER TABLE transactions MODIFY COLUMN type ENUM(
                'pembelian_masuk', 'produksi_masuk', 'produksi_keluar', 'pengiriman_keluar', 'rusak'
            )
        ");
    }
};