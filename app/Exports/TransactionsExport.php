<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection; // Ganti FromQuery menjadi FromCollection
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class TransactionsExport implements FromCollection, WithHeadings, WithMapping
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function collection()
    {
        return $this->data;
    }

    public function headings(): array
    {
        return [
            'No', // Ganti 'ID Transaksi' menjadi 'No'
            'Nama Barang',
            'Kategori Barang',
            'Tipe',
            'Kuantitas',
            'Deskripsi',
            'Tanggal Transaksi',
        ];
    }

    public function map($transaction): array
    {
        static $rowNumber = 0; // Buat variabel statis untuk nomor urut
        $rowNumber++;

        return [
            $rowNumber, // Tambahkan nomor urut
            $transaction->item->name,
            $transaction->item->category,
            ucfirst($transaction->type),
            $transaction->quantity,
            $transaction->description,
            $transaction->created_at->format('Y-m-d H:i:s'),
        ];
    }
}