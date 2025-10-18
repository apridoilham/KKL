<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained('items')->onDelete('restrict');
            $table->enum('type', [
                'masuk_mentah',
                'masuk_jadi',
                'produksi_jadi',
                'produksi_terpakai',
                'keluar_dikirim',
                'keluar_mentah',
                'rusak_mentah',
                'rusak_jadi'
            ]);
            $table->unsignedInteger('quantity');
            $table->text('description')->nullable();
            $table->string('nama_supplier')->nullable();
            $table->string('nama_customer')->nullable();
            $table->string('nomor_surat_jalan')->nullable();
            $table->date('tanggal_surat_jalan')->nullable();
            $table->timestamps();
            $table->index('type');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};