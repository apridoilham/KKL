<?php

namespace Database\Seeders;

use App\Models\Item;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DummyDataSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        User::truncate();
        Item::truncate();
        Transaction::truncate();
        DB::table('bill_of_materials')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $this->command->info('Membuat data pengguna...');
        $this->createUsers();

        $this->command->info('Membuat data barang mentah dan transaksi...');
        $rawMaterials = $this->createRawMaterialsAndTransactions();

        $this->command->info('Membuat data barang jadi dan resep (BOM)...');
        $this->createFinishedGoodsAndBom($rawMaterials);

        $this->command->info('Proses seeding data dummy selesai!');
    }

    private function createUsers(): void
    {
        User::create(['name' => 'Staff Gudang', 'username' => 'admin', 'password' => Hash::make('password'), 'role' => 'admin', 'security_question' => 'Nama hewan?', 'security_answer' => Hash::make('admin'),]);
        User::create(['name' => 'Staff Produksi', 'username' => 'produksi', 'password' => Hash::make('password'), 'role' => 'produksi', 'security_question' => 'Warna favorit?', 'security_answer' => Hash::make('produksi'),]);
        User::create(['name' => 'Staff Pengiriman', 'username' => 'pengiriman', 'password' => Hash::make('password'), 'role' => 'pengiriman', 'security_question' => 'Kota kelahiran?', 'security_answer' => Hash::make('pengiriman'),]);
    }

    // ---- Perubahan Mulai Disini ----
    private function createRawMaterialsAndTransactions(): Collection
    {
        // Ubah struktur data, category tidak lagi jadi key utama
        $rawItemsData = [
            ['category_hint' => 'Bahan Kue', 'name' => 'Tepung Terigu', 'harga_beli' => 10000],
            ['category_hint' => 'Bahan Kue', 'name' => 'Gula Pasir', 'harga_beli' => 12000],
            ['category_hint' => 'Bahan Kue', 'name' => 'Telur Ayam', 'harga_beli' => 2000],
            ['category_hint' => 'Bahan Kue', 'name' => 'Mentega', 'harga_beli' => 15000],
            ['category_hint' => 'Elektronik', 'name' => 'CPU Intel i7', 'harga_beli' => 4500000],
            ['category_hint' => 'Elektronik', 'name' => 'RAM 16GB DDR4', 'harga_beli' => 800000],
            ['category_hint' => 'Elektronik', 'name' => 'SSD 1TB NVMe', 'harga_beli' => 1200000],
            ['category_hint' => 'Elektronik', 'name' => 'Casing PC ATX', 'harga_beli' => 500000],
        ];

        $createdItems = collect();

        foreach ($rawItemsData as $itemData) {
            $item = Item::create([
                'name' => $itemData['name'],
                // 'category' => $category, // <-- Hapus baris ini
                'item_type' => 'barang_mentah',
                'code' => strtoupper(substr($itemData['category_hint'], 0, 3)) . '-' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT),
                'harga_beli' => $itemData['harga_beli'] ?? 0, // Ambil harga beli
                'quantity' => 0,
                'status' => 'out', // Awalnya out
                'created_at' => now()->subMonths(rand(1, 6)),
            ]);

            $initialStock = rand(500, 1000);
            $item->increaseStock($initialStock); // Status jadi available
            Transaction::create([
                'item_id' => $item->id,
                'type' => 'masuk_mentah',
                'quantity' => $initialStock,
                'description' => 'Stok awal dari pemasok',
                'nama_supplier' => 'Supplier Dummy ' . $itemData['category_hint'], // Contoh data supplier
                'nomor_surat_jalan' => 'SJ-DUMMY-' . rand(100, 999), // Contoh data SJ
                'tanggal_surat_jalan' => $item->created_at->addDay()->format('Y-m-d'), // Contoh data tgl SJ
                'created_at' => $item->created_at->addDay(),
            ]);
            $createdItems->push($item);
        }
        return $createdItems;
    }
    // ---- Perubahan Selesai Disini ----

    private function createFinishedGoodsAndBom(Collection $rawMaterials): void
    {
        $kue = Item::create([
            'name' => 'Kue Bolu',
            'item_type' => 'barang_jadi',
            'code' => 'PROD-KUE01',
            'harga_jual' => 50000, // Contoh harga jual
            'quantity' => 0,
            'status' => 'out'
        ]);
        // Pastikan item ditemukan sebelum attach
        $tepung = $rawMaterials->firstWhere('name', 'Tepung Terigu');
        $gula = $rawMaterials->firstWhere('name', 'Gula Pasir');
        $telur = $rawMaterials->firstWhere('name', 'Telur Ayam');
        $mentega = $rawMaterials->firstWhere('name', 'Mentega');

        if($tepung && $gula && $telur && $mentega) {
             $kue->bomRawMaterials()->attach([
                $tepung->id => ['quantity_required' => 2],
                $gula->id => ['quantity_required' => 1],
                $telur->id => ['quantity_required' => 4],
                $mentega->id => ['quantity_required' => 1],
            ]);
        }


        $komputer = Item::create([
            'name' => 'PC Gaming Rakitan',
            'item_type' => 'barang_jadi',
            'code' => 'PROD-PC01',
            'harga_jual' => 15000000, // Contoh harga jual
            'quantity' => 0,
            'status' => 'out'
        ]);
        // Pastikan item ditemukan sebelum attach
        $cpu = $rawMaterials->firstWhere('name', 'CPU Intel i7');
        $ram = $rawMaterials->firstWhere('name', 'RAM 16GB DDR4');
        $ssd = $rawMaterials->firstWhere('name', 'SSD 1TB NVMe');
        $casing = $rawMaterials->firstWhere('name', 'Casing PC ATX');

        if($cpu && $ram && $ssd && $casing){
            $komputer->bomRawMaterials()->attach([
                $cpu->id => ['quantity_required' => 1],
                $ram->id => ['quantity_required' => 2],
                $ssd->id => ['quantity_required' => 1],
                $casing->id => ['quantity_required' => 1],
            ]);
        }
    }
}