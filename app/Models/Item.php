<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Item extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'item_type',
        'harga_beli',
        'harga_jual',
        'quantity',
        'stok_minimum',
        'status',
    ];

    protected $casts = [
        'harga_beli' => 'decimal:2',
        'harga_jual' => 'decimal:2',
        'quantity' => 'integer',
        'stok_minimum' => 'integer',
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

    public function increaseStock(int $amount): void
    {
        if ($amount < 0) {
            throw new Exception('Jumlah untuk menambah stok harus positif.');
        }
        $this->quantity += $amount;
        $this->status = $this->quantity > 0 ? 'available' : 'out';
        $this->save();
    }

    public function decreaseStock(int $amount): void
    {
        if ($amount < 0) {
            throw new Exception('Jumlah untuk mengurangi stok harus positif.');
        }
        if ($this->quantity < $amount) {
            throw new Exception('Stok '. $this->name .' tidak mencukupi (Butuh: '.$amount.', Tersedia: '.$this->quantity.').');
        }
        $this->quantity -= $amount;
        $this->status = $this->quantity > 0 ? 'available' : 'out';
        $this->save();
    }
}