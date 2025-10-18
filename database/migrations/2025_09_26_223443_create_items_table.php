<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->string('code')->nullable()->unique(); // Tambahkan unique constraint jika diperlukan
            // $table->string('category')->nullable(); // Hapus kolom category
            $table->string('name');
            $table->enum('item_type', ['barang_mentah', 'barang_jadi'])->default('barang_mentah');
            $table->decimal('harga_beli', 15, 2)->nullable()->default(0); // Tambahkan harga beli
            $table->decimal('harga_jual', 15, 2)->nullable()->default(0); // Tambahkan harga jual
            $table->unsignedInteger('quantity')->default(0);
            $table->string('status')->default('available');
            $table->timestamps();

            // Index tambahan jika sering dicari
            $table->index('name');
            $table->index('item_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};