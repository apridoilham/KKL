<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo; // Import BelongsTo

class Transaction extends Model
{
    protected $fillable = [
        'item_id',
        'type',
        'quantity',
        'description',
        'nama_supplier',        // Tambahkan
        'nama_customer',        // Tambahkan
        'nomor_surat_jalan',    // Tambahkan
        'tanggal_surat_jalan',  // Tambahkan
    ];

    // Tambahkan casts untuk tipe data date
    protected $casts = [
        'tanggal_surat_jalan' => 'date',
        'quantity' => 'integer', // Pastikan quantity integer
    ];

    // Perbaiki return type hint
    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class,'item_id');
    }
}