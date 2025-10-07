<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Item extends Model
{
    protected $fillable = [
        'code',
        'category',
        'name',
        'item_type',
        'quantity',
        'status',
    ];

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function bomRawMaterials(): BelongsToMany
    {
        return $this->belongsToMany(Item::class, 'bill_of_materials', 'finished_good_id', 'raw_material_id')
                        ->withPivot('quantity_required')
                        ->withTimestamps();
    }

    public function increaseStock(float $amount): void
    {
        $this->quantity += $amount;
        $this->status = $this->quantity > 0 ? 'available' : 'out';
        $this->save();
    }

    public function decreaseStock(float $amount): void
    {
        if ($this->quantity < $amount) {
            throw new Exception('Stok tidak mencukupi untuk transaksi ini.');
        }
        $this->quantity -= $amount;
        $this->status = $this->quantity > 0 ? 'available' : 'out';
        $this->save();
    }
}