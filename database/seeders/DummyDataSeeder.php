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
use Illuminate\Support\Facades\Log;

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

        $this->command->info('Membuat data item, stok, dan transaksi...');
        $this->createItemsAndTransactions();

        $this->command->info('Proses seeding data dummy selesai!');
    }

    private function createUsers(): void
    {
        User::create([
            'name' => 'Staff Gudang (Admin)', 
            'username' => 'admin', 
            'password' => Hash::make('password'), 
            'role' => 'admin', 
            'security_question' => 'Nama hewan?', 
            'security_answer' => Hash::make('admin')
        ]);
        User::create([
            'name' => 'Staff Produksi', 
            'username' => 'produksi', 
            'password' => Hash::make('password'), 
            'role' => 'produksi', 
            'security_question' => 'Warna favorit?', 
            'security_answer' => Hash::make('produksi')
        ]);
        User::create([
            'name' => 'Staff Pengiriman', 
            'username' => 'pengiriman', 
            'password' => Hash::make('password'), 
            'role' => 'pengiriman', 
            'security_question' => 'Kota kelahiran?', 
            'security_answer' => Hash::make('pengiriman')
        ]);
    }

    private function createItemsAndTransactions(): void
    {
        $now = Carbon::now();

        // 1. BUAT BAHAN MENTAH
        $this->command->info('Membuat bahan mentah...');
        $kardus = Item::create([
            'name' => 'Lembaran Kardus BC Flute', 
            'code' => 'LK-BC-01', 
            'item_type' => 'barang_mentah',
            'harga_beli' => 5000,
            'stok_minimum' => 20,
            'quantity' => 0, 
            'status' => 'out',
            'created_at' => $now->copy()->subDays(30)
        ]);

        $bubble = Item::create([
            'name' => 'Bubble Wrap 1.25m (meter)', 
            'code' => 'BW-125', 
            'item_type' => 'barang_mentah',
            'harga_beli' => 4000,
            'stok_minimum' => 50,
            'quantity' => 0, 
            'status' => 'out',
            'created_at' => $now->copy()->subDays(28)
        ]);

        $lakban = Item::create([
            'name' => 'Lakban Bening 2 inch', 
            'code' => 'LB-BN-02', 
            'item_type' => 'barang_mentah',
            'harga_beli' => 8000,
            'stok_minimum' => 10,
            'quantity' => 0, 
            'status' => 'out',
            'created_at' => $now->copy()->subDays(26)
        ]);

        $foam = Item::create([
            'name' => 'Foam Roll PE 2mm (roll)', 
            'code' => 'FR-PE-02', 
            'item_type' => 'barang_mentah',
            'harga_beli' => 150000,
            'stok_minimum' => 0,
            'quantity' => 0, 
            'status' => 'out',
            'created_at' => $now->copy()->subDays(24)
        ]);

        // 2. BUAT STOK AWAL BAHAN MENTAH
        $this->command->info('Membuat stok awal bahan mentah...');
        $this->createTransaction($kardus, 'masuk_mentah', 100, $now->copy()->subDays(29), [
            'nama_supplier' => 'PT Karton Jaya', 
            'nomor_surat_jalan' => 'SJ/KJ/2025/10/001', 
            'tanggal_surat_jalan' => $now->copy()->subDays(29)->format('Y-m-d')
        ]); // Stok Kardus: 100

        $this->createTransaction($bubble, 'masuk_mentah', 100, $now->copy()->subDays(27), [
            'nama_supplier' => 'Distributor Plastik Indo', 
            'nomor_surat_jalan' => 'SJ/DPI/2025/10/002', 
            'tanggal_surat_jalan' => $now->copy()->subDays(27)->format('Y-m-d')
        ]); // Stok Bubble: 100

        $this->createTransaction($lakban, 'masuk_mentah', 50, $now->copy()->subDays(25), [
            'nama_supplier' => 'Toko ATK Rapi', 
            'nomor_surat_jalan' => 'SJ/ATR/2025/10/003', 
            'tanggal_surat_jalan' => $now->copy()->subDays(25)->format('Y-m-d')
        ]); // Stok Lakban: 50

        $this->createTransaction($foam, 'masuk_mentah', 200, $now->copy()->subDays(23), [
            'nama_supplier' => 'PT Foam Sejahtera', 
            'nomor_surat_jalan' => 'SJ/FS/2025/10/004', 
            'tanggal_surat_jalan' => $now->copy()->subDays(23)->format('Y-m-d')
        ]); // Stok Foam: 200

        // 3. BUAT BARANG JADI DAN BOM
        $this->command->info('Membuat barang jadi dan BOM...');
        $kotak = Item::create([
            'name' => 'Kotak Packing Standar 30x20x10', 
            'code' => 'KPS-302010', 
            'item_type' => 'barang_jadi',
            'harga_jual' => 15000,
            'stok_minimum' => 10,
            'quantity' => 0, 
            'status' => 'out',
            'created_at' => $now->copy()->subDays(20)
        ]);
        $kotak->bomRawMaterials()->attach([
            $kardus->id => ['quantity_required' => 1],
            $lakban->id => ['quantity_required' => 0.1]
        ]);

        $paketBubble = Item::create([
            'name' => 'Paket Bubble Wrap 5m', 
            'code' => 'PBW-05', 
            'item_type' => 'barang_jadi',
            'harga_jual' => 25000,
            'stok_minimum' => 15,
            'quantity' => 0, 
            'status' => 'out',
            'created_at' => $now->copy()->subDays(19)
        ]);
        $paketBubble->bomRawMaterials()->attach([
            $bubble->id => ['quantity_required' => 5],
            $lakban->id => ['quantity_required' => 0.05]
        ]);
        
        // 4. BUAT TRANSAKSI UNTUK MENTRIGGER STOK MINIMUM
        $this->command->info('Membuat transaksi pemicu stok minimum...');

        // Trigger 1: Produksi Kotak (85 unit)
        // Ini akan menggunakan 85 Kardus (Stok: 100 -> 15) -> DI BAWAH MINIMUM (20)
        // Ini akan menggunakan 8.5 Lakban (Stok: 50 -> 41.5)
        // Stok Kotak akan menjadi 85
        $this->simulateProduction($kotak, 85, $now->copy()->subDays(18));

        // Trigger 2: Jual Kotak (80 unit)
        // Stok Kotak akan menjadi (Stok: 85 -> 5) -> DI BAWAH MINIMUM (10)
        $this->createTransaction($kotak, 'keluar_dikirim', 80, $now->copy()->subDays(15), [
            'nama_customer' => 'Toko Online Sejahtera', 
            'nomor_surat_jalan' => 'SJ/TOS/2025/10/005', 
            'tanggal_surat_jalan' => $now->copy()->subDays(15)->format('Y-m-d')
        ]);

        // Trigger 3: Bubble Wrap Rusak (60 unit)
        // Stok Bubble akan menjadi (Stok: 100 -> 40) -> DI BAWAH MINIMUM (50)
        $this->createTransaction($bubble, 'rusak_mentah', 60, $now->copy()->subDays(12), [
            'description' => 'Terkena air saat penyimpanan'
        ]);

        // Trigger 4: Produksi Paket Bubble (5 unit)
        // Ini akan menggunakan 25 Bubble (Stok: 40 -> 15) -> TETAP DI BAWAH MINIMUM
        // Ini akan menggunakan 0.25 Lakban (Stok: 41.5 -> 41.25)
        // Stok Paket Bubble akan menjadi 5
        $this->simulateProduction($paketBubble, 5, $now->copy()->subDays(10));
        
        // Transaksi Normal (Tidak trigger minimum)
        $this->createTransaction($lakban, 'keluar_mentah', 10, $now->copy()->subDays(5), [
            'nama_customer' => 'Customer Lakban Eceran', 
            'nomor_surat_jalan' => 'SJ/CLE/2025/10/006', 
            'tanggal_surat_jalan' => $now->copy()->subDays(5)->format('Y-m-d')
        ]); // Stok Lakban: 41.25 -> 31.25 (Masih di atas minimum 10)
    }

    private function createTransaction(Item $item, string $type, int $quantity, Carbon $date, array $details = []): void
    {
        try {
            $data = array_merge([
                'item_id' => $item->id,
                'type' => $type,
                'quantity' => $quantity,
                'description' => $details['description'] ?? null,
                'nama_supplier' => $details['nama_supplier'] ?? null,
                'nama_customer' => $details['nama_customer'] ?? null,
                'nomor_surat_jalan' => $details['nomor_surat_jalan'] ?? null,
                'tanggal_surat_jalan' => $details['tanggal_surat_jalan'] ?? null,
                'created_at' => $date,
                'updated_at' => $date,
            ], $details);

            DB::transaction(function () use ($item, $type, $quantity, $data) {
                if (in_array($type, ['masuk_mentah', 'masuk_jadi', 'produksi_jadi'])) {
                    $item->increaseStock($quantity);
                } else {
                    $item->decreaseStock($quantity);
                }
                Transaction::create($data);
            });
        } catch (\Exception $e) {
            Log::error("Seeder transaction failed for item {$item->name}: " . $e->getMessage());
            $this->command->error("Seeder transaction failed for item {$item->name}: " . $e->getMessage());
        }
    }

    private function simulateProduction(Item $finishedGood, int $quantityToProduce, Carbon $productionDate): void
    {
         try {
            DB::transaction(function () use ($finishedGood, $quantityToProduce, $productionDate) {
                 $finishedGood->load('bomRawMaterials');

                 if ($finishedGood->bomRawMaterials->isEmpty()) {
                     Log::warning("Production skipped for {$finishedGood->name}: BOM is empty.");
                     return;
                 }

                 $stockSufficient = true;
                 foreach ($finishedGood->bomRawMaterials as $rawMaterial) {
                     $needed = $rawMaterial->pivot->quantity_required * $quantityToProduce;
                     $currentRawMaterial = Item::find($rawMaterial->id);
                     if (!$currentRawMaterial || $currentRawMaterial->quantity < $needed) {
                         $stockSufficient = false;

                         // ---- PERUBAHAN DI SINI ----
                         // Evaluasi nilai ke variabel terpisah sebelum dimasukkan ke Log
                         $availableQty = $currentRawMaterial?->quantity ?? 0;
                         Log::warning("Production skipped for {$finishedGood->name}: Insufficient stock for {$rawMaterial->name}. Needed: {$needed}, Available: {$availableQty}.");
                         // -------------------------

                         $this->command->error("Production skipped for {$finishedGood->name}: Insufficient stock for {$rawMaterial->name}.");
                         break;
                     }
                 }

                 if ($stockSufficient) {
                     foreach ($finishedGood->bomRawMaterials as $rawMaterial) {
                         $needed = $rawMaterial->pivot->quantity_required * $quantityToProduce;
                         // Ambil data terbaru lagi untuk keamanan
                         $currentRawMaterial = Item::find($rawMaterial->id); 
                         if ($currentRawMaterial) {
                             $currentRawMaterial->decreaseStock($needed);

                             Transaction::create([
                                'item_id' => $rawMaterial->id,
                                'type' => 'produksi_terpakai',
                                'quantity' => $needed,
                                'description' => 'Digunakan u/ produksi ' . $finishedGood->name . ' (' . $quantityToProduce . ' unit)',
                                'created_at' => $productionDate,
                                'updated_at' => $productionDate,
                            ]);
                         } else {
                             Log::error("Cannot decrease stock. Raw material ID {$rawMaterial->id} not found during production of {$finishedGood->name}.");
                         }
                     }

                     // Refresh $finishedGood sebelum increaseStock
                     $finishedGood->refresh();
                     $finishedGood->increaseStock($quantityToProduce);
                     Transaction::create([
                        'item_id' => $finishedGood->id,
                        'type' => 'produksi_jadi',
                        'quantity' => $quantityToProduce,
                        'description' => 'Hasil produksi',
                        'created_at' => $productionDate,
                        'updated_at' => $productionDate,
                    ]);

                    $this->command->info("Simulated production: {$quantityToProduce} unit(s) of {$finishedGood->name}");
                 }

            });
         } catch (\Exception $e) {
             Log::error("Production failed for {$finishedGood->name}: " . $e->getMessage());
             $this->command->error("Production failed for {$finishedGood->name}: " . $e->getMessage());
         }
    }
}