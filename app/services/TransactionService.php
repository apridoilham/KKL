<?php

namespace App\Services;

use App\Models\Item;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Exception;

class TransactionService
{
    /**
     * Membuat transaksi baru dan memperbarui stok item secara atomik.
     *
     * @param array $data Data transaksi ['item_id', 'type', 'quantity', 'description']
     * @return Transaction
     * @throws Exception
     */
    public function createTransaction(array $data): Transaction
    {
        // Memulai database transaction
        return DB::transaction(function () use ($data) {
            $item = Item::findOrFail($data['item_id']);

            // Memanggil metode dari model Item yang sudah kita buat sebelumnya
            if ($data['type'] === 'in') {
                $item->increaseStock($data['quantity']);
            } else {
                $item->decreaseStock($data['quantity']);
            }

            // Membuat record transaksi
            $transaction = Transaction::create([
                'item_id' => $data['item_id'],
                'type' => $data['type'],
                'quantity' => $data['quantity'],
                'description' => $data['description'],
            ]);

            return $transaction;
        });
    }
}