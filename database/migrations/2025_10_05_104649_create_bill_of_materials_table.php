<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tabel ini akan menyimpan "resep" atau Bill of Materials (BOM)
        Schema::create('bill_of_materials', function (Blueprint $table) {
            $table->id();
            
            // Barang Jadi yang akan dibuat
            $table->foreignId('finished_good_id')->constrained('items')->onDelete('cascade');

            // Barang Mentah yang dibutuhkan
            $table->foreignId('raw_material_id')->constrained('items')->onDelete('cascade');
            
            // Jumlah Barang Mentah yang dibutuhkan untuk membuat 1 unit Barang Jadi
            $table->decimal('quantity_required', 8, 2);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bill_of_materials');
    }
};