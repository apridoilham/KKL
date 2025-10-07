<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('security_question')->nullable()->change();
            $table->string('security_answer')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Perhatian: Rollback mungkin gagal jika ada data yang sudah null.
            // Untuk amannya, kita isi data null dengan string kosong sebelum mengubahnya.
            \App\Models\User::whereNull('security_question')->update(['security_question' => '']);
            \App\Models\User::whereNull('security_answer')->update(['security_answer' => '']);

            $table->string('security_question')->nullable(false)->change();
            $table->string('security_answer')->nullable(false)->change();
        });
    }
};