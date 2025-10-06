<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\BeforeSheet;

class ItemsExport implements FromCollection, WithHeadings, WithMapping, WithEvents
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
            'Kode Barang',
            'Kategori',
            'Nama Barang',
            'Kuantitas',
            'Status',
            'Tanggal Dibuat',
        ];
    }

    public function map($item): array
    {
        $this->rowNumber++;
        return [
            $this->rowNumber,
            $item->code,
            $item->category,
            $item->name,
            $item->quantity,
            $item->status === 'available' ? 'Tersedia' : 'Habis',
            $item->created_at->format('Y-m-d H:i:s'),
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