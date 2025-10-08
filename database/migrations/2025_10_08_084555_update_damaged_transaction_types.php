<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('transactions')
            ->join('items', 'transactions.item_id', '=', 'items.id')
            ->where('transactions.type', 'rusak')
            ->where('items.item_type', 'barang_mentah')
            ->update(['transactions.type' => 'rusak_mentah']);

        DB::table('transactions')
            ->join('items', 'transactions.item_id', '=', 'items.id')
            ->where('transactions.type', 'rusak')
            ->where('items.item_type', 'barang_jadi')
            ->update(['transactions.type' => 'rusak_jadi']);

        DB::statement("
            ALTER TABLE transactions MODIFY COLUMN type ENUM(
                'masuk_mentah',
                'masuk_jadi',
                'keluar_terpakai',
                'keluar_dikirim',
                'keluar_mentah',
                'rusak_mentah',
                'rusak_jadi'
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
                'keluar_mentah',
                'rusak'
            )
        ");

        DB::table('transactions')->whereIn('type', ['rusak_mentah', 'rusak_jadi'])->update(['type' => 'rusak']);
    }
};