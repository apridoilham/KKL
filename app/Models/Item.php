<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Item extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'code',
        'category',
        'name',
        'quantity',
        'status',
    ];

    /**
     * Mendefinisikan relasi one-to-many ke tabel transactions.
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Menambah jumlah stok barang.
     * @param int $amount Jumlah yang akan ditambahkan
     */
    public function increaseStock(int $amount): void
    {
        $this->quantity += $amount;
        $this->status = $this->quantity > 0 ? 'available' : 'out';
        $this->save();
    }

    /**
     * Mengurangi jumlah stok barang.
     * @param int $amount Jumlah yang akan dikurangi
     * @throws Exception jika stok tidak mencukupi
     */
    public function decreaseStock(int $amount): void
    {
        if ($this->quantity < $amount) {
            throw new Exception('Stok tidak mencukupi untuk transaksi ini.');
        }
        $this->quantity -= $amount;
        $this->status = $this->quantity > 0 ? 'available' : 'out';
        $this->save();
    }
}