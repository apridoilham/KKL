<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\BeforeSheet;

class TransactionsExport implements FromCollection, WithHeadings, WithMapping, WithEvents
{
    protected $data;
    private $rowNumber = 0;

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
            'No',
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
        $this->rowNumber++;
        return [
            $this->rowNumber,
            $transaction->item->name,
            $transaction->item->category,
            ucfirst($transaction->type),
            $transaction->quantity,
            $transaction->description,
            $transaction->created_at->format('Y-m-d H:i:s'),
        ];
    }

    public function registerEvents(): array
    {
        return [
            BeforeSheet::class => function(BeforeSheet $event) {
                $this->rowNumber = 0; // Reset nomor baris sebelum sheet dibuat
            },
        ];
    }
}