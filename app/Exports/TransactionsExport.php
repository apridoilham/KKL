<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class TransactionsExport implements FromCollection, WithHeadings, WithMapping
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
            'Kode Barang', // Tambah Kode
            'Nama Barang',
            'Tipe Transaksi',
            'Kuantitas',
            'Supplier', // Tambah Supplier
            'Customer', // Tambah Customer
            'No Surat Jalan', // Tambah No SJ
            'Tgl Surat Jalan', // Tambah Tgl SJ
            'Deskripsi',
            'Tanggal Transaksi',
        ];
    }

    public function map($transaction): array
    {
        $this->rowNumber++;

        // Logika untuk teks tipe transaksi
        $typeText = match ($transaction->type) {
            'masuk_mentah' => 'Masuk (Bahan Mentah)',
            'masuk_jadi' => 'Masuk (Barang Jadi)',
            'produksi_jadi' => 'Produksi (Barang Jadi)',
            'produksi_terpakai' => 'Produksi (Terpakai)',
            'keluar_dikirim' => 'Keluar (Kirim - Jadi)',
            'keluar_mentah' => 'Keluar (Kirim - Mentah)',
            'rusak_mentah' => 'Rusak (Bahan Mentah)',
            'rusak_jadi' => 'Rusak (Barang Jadi)',
            default => ucfirst(str_replace('_', ' ', $transaction->type)),
        };

        return [
            $this->rowNumber,
            $transaction->item->code ?? '-', // Tambah Kode
            $transaction->item->name,
            $typeText, // Gunakan teks yang sudah diproses
            $transaction->quantity,
            $transaction->nama_supplier ?? '-', // Tambah Supplier
            $transaction->nama_customer ?? '-', // Tambah Customer
            $transaction->nomor_surat_jalan ?? '-', // Tambah No SJ
            // Pastikan tanggal tidak null sebelum format
            $transaction->tanggal_surat_jalan ? optional($transaction->tanggal_surat_jalan)->format('Y-m-d') : '-', // Tambah Tgl SJ
            $transaction->description ?? '-',
            $transaction->created_at->format('Y-m-d H:i:s'),
        ];
    }
}