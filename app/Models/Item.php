<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    /* PERBAIKAN DI SINI: Tambahkan 'quantity' dan 'status' */
    protected $fillable = [
        'code',
        'category',
        'name',
        'quantity',
        'status',
    ];

    /* Ini untuk membuat relasi one to many ke table transaction */
    public function transactions(){
        return $this->hasMany(Transaction::class);
    }
}