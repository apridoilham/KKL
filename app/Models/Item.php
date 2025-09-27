<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    /* Ini untuk mengatur field table mana yang boleh diisi */
    protected $fillable = [
        'code',
        'category',
        'name',
    ];

    /* Ini untuk membuat relasi one to many ke table transaction */
    public function transactions(){
        return $this->hasMany(Transaction::class);
    }
}
