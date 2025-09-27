<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = [
        'item_id',
        'type',
        'quantity',
        'description',
    ];

    /* Ini untuk merelasikan ke table items, dimana yang berelasi yaitu field item_id */

    public function item(){
        return $this->belongsTo(Item::class,'item_id');
    }
}
