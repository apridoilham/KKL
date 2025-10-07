<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bill_of_materials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('finished_good_id')->constrained('items')->onDelete('cascade');
            $table->foreignId('raw_material_id')->constrained('items')->onDelete('cascade');
            $table->unsignedInteger('quantity_required');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bill_of_materials');
    }
};