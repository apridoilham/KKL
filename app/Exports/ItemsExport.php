<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection; // Ganti FromQuery menjadi FromCollection
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ItemsExport implements FromCollection, WithHeadings, WithMapping
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    // Mengambil data dari collection yang sudah di-fetch
    public function collection()
    {
        return $this->data;
    }

    // Menentukan judul kolom (header)
    public function headings(): array
    {
        return [
            'No', // Ganti 'ID' menjadi 'No'
            'Kode Barang',
            'Kategori',
            'Nama Barang',
            'Kuantitas',
            'Status',
            'Tanggal Dibuat',
        ];
    }

    // Memetakan data dari setiap baris
    public function map($item): array
    {
        static $rowNumber = 0; // Buat variabel statis untuk nomor urut
        $rowNumber++;

        return [
            $rowNumber, // Tambahkan nomor urut
            $item->code,
            $item->category,
            $item->name,
            $item->quantity,
            $item->status === 'available' ? 'Tersedia' : 'Habis',
            $item->created_at->format('Y-m-d H:i:s'),
        ];
    }
}