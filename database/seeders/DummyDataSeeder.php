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
use Illuminate\Support\Facades\Log; // Pastikan Log facade di-import

class DummyDataSeeder extends Seeder
{
    public function run(): void
    {
        // Nonaktifkan foreign key check untuk truncate
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        User::truncate();
        Item::truncate();
        Transaction::truncate();
        DB::table('bill_of_materials')->truncate();
        // Aktifkan kembali
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $this->command->info('Membuat data pengguna...');
        $this->createUsers();

        $this->command->info('Membuat data item bahan mentah packaging...');
        $rawMaterials = $this->createPackagingRawMaterials();

        $this->command->info('Membuat data item barang jadi packaging...');
        $finishedGoods = $this->createPackagingFinishedGoods($rawMaterials);

        $this->command->info('Membuat transaksi tambahan...');
        $this->createAdditionalTransactions($rawMaterials, $finishedGoods);

        $this->command->info('Proses seeding data dummy selesai!');
    }

    private function createUsers(): void
    {
        User::create(['name' => 'Staff Gudang', 'username' => 'admin', 'password' => Hash::make('password'), 'role' => 'admin', 'security_question' => 'Nama hewan?', 'security_answer' => Hash::make('admin'),]);
        User::create(['name' => 'Staff Produksi', 'username' => 'produksi', 'password' => Hash::make('password'), 'role' => 'produksi', 'security_question' => 'Warna favorit?', 'security_answer' => Hash::make('produksi'),]);
        User::create(['name' => 'Staff Pengiriman', 'username' => 'pengiriman', 'password' => Hash::make('password'), 'role' => 'pengiriman', 'security_question' => 'Kota kelahiran?', 'security_answer' => Hash::make('pengiriman'),]);
    }

    private function createPackagingRawMaterials(): Collection
    {
        $itemsData = [
            ['category_hint' => 'Packaging', 'name' => 'Foam Roll PE 2mm', 'code' => 'FR-PE-02', 'harga_beli' => 150000], // Harga per roll
            ['category_hint' => 'Packaging', 'name' => 'Lembaran Kardus BC Flute', 'code' => 'LK-BC-01', 'harga_beli' => 5000], // Harga per lembar
            ['category_hint' => 'Packaging', 'name' => 'Lakban Bening 2 inch', 'code' => 'LB-BN-02', 'harga_beli' => 8000], // Harga per roll
            ['category_hint' => 'Packaging', 'name' => 'Plastik Wrap 50cm', 'code' => 'PW-50', 'harga_beli' => 75000], // Harga per roll
            ['category_hint' => 'Packaging', 'name' => 'Bubble Wrap 1.25m', 'code' => 'BW-125', 'harga_beli' => 4000], // Harga per meter
        ];

        $createdItems = collect();
        $startDate = Carbon::now()->subMonths(3);

        foreach ($itemsData as $i => $itemData) {
            $item = Item::create([
                'name' => $itemData['name'],
                'code' => $itemData['code'],
                'item_type' => 'barang_mentah',
                'harga_beli' => $itemData['harga_beli'] ?? 0,
                'quantity' => 0,
                'status' => 'out',
                'created_at' => $startDate->copy()->addDays($i * 5),
                'updated_at' => $startDate->copy()->addDays($i * 5),
            ]);

            $initialStock = rand(50, 200);
            $transactionDate = $item->created_at->copy()->addDay();
            $item->increaseStock($initialStock);
            Transaction::create([
                'item_id' => $item->id,
                'type' => 'masuk_mentah',
                'quantity' => $initialStock,
                'description' => 'Stok awal pembelian',
                'nama_supplier' => 'Supplier Maju Jaya ' . ($i % 2 == 0 ? 'Plastik' : 'Karton'),
                'nomor_surat_jalan' => 'SJ/MJP/' . $transactionDate->format('Ymd') . '/' . str_pad($item->id, 3, '0', STR_PAD_LEFT),
                'tanggal_surat_jalan' => $transactionDate->format('Y-m-d'),
                'created_at' => $transactionDate,
                'updated_at' => $transactionDate,
            ]);
            $createdItems->push($item);
        }
        return $createdItems;
    }

    private function createPackagingFinishedGoods(Collection $rawMaterials): Collection
    {
        $finishedData = [
            ['name' => 'Kotak Packing Standar 30x20x10', 'code' => 'KPS-302010', 'harga_jual' => 15000, 'bom' => ['LK-BC-01' => 1, 'LB-BN-02' => 0.1]],
            ['name' => 'Paket Bubble Wrap 5m', 'code' => 'PBW-05', 'harga_jual' => 25000, 'bom' => ['BW-125' => 5, 'LB-BN-02' => 0.05]],
        ];

        $createdItems = collect();
        $startDate = Carbon::now()->subMonths(2);

        foreach ($finishedData as $i => $itemData) {
            $item = Item::create([
                'name' => $itemData['name'],
                'code' => $itemData['code'],
                'item_type' => 'barang_jadi',
                'harga_jual' => $itemData['harga_jual'],
                'quantity' => 0,
                'status' => 'out',
                'created_at' => $startDate->copy()->addDays($i * 7),
                'updated_at' => $startDate->copy()->addDays($i * 7),
            ]);

            $bomData = [];
            foreach ($itemData['bom'] as $rawCode => $qty) {
                $rawMaterial = $rawMaterials->firstWhere('code', $rawCode);
                if ($rawMaterial) {
                    $bomData[$rawMaterial->id] = ['quantity_required' => $qty];
                } else {
                     Log::warning("BOM Warning: Raw material with code {$rawCode} not found for finished good {$itemData['name']}");
                }
            }
            if (!empty($bomData)) {
                $item->bomRawMaterials()->attach($bomData);
            }

            if (!empty($bomData)) {
                 $this->simulateProduction($item, $rawMaterials, rand(10, 30), $item->created_at->copy()->addDays(2));
            }

            $createdItems->push($item);
        }
        return $createdItems;
    }

    private function simulateProduction(Item $finishedGood, Collection $rawMaterials, int $quantityToProduce, Carbon $productionDate): void
    {
         try {
            DB::transaction(function () use ($finishedGood, $rawMaterials, $quantityToProduce, $productionDate) {
                 $finishedGood->refresh();
                 $finishedGood->load('bomRawMaterials');

                 if ($finishedGood->bomRawMaterials->isEmpty()) {
                     Log::warning("Production skipped for {$finishedGood->name}: BOM is empty.");
                     return;
                 }

                 foreach ($finishedGood->bomRawMaterials as $rawMaterial) {
                     $needed = $rawMaterial->pivot->quantity_required * $quantityToProduce;
                     $currentRawMaterial = $rawMaterials->firstWhere('id', $rawMaterial->id)?->fresh() ?? Item::find($rawMaterial->id);

                     // Evaluasi quantity di luar string
                     $availableQty = $currentRawMaterial?->quantity ?? 0;
                     if (!$currentRawMaterial || $availableQty < $needed) {
                          // Gunakan variabel $availableQty di log
                          Log::warning("Production skipped for {$finishedGood->name}: Insufficient stock for {$rawMaterial->name}. Needed: {$needed}, Available: {$availableQty}.");
                          return;
                     }
                 }

                 foreach ($finishedGood->bomRawMaterials as $rawMaterial) {
                     $needed = $rawMaterial->pivot->quantity_required * $quantityToProduce;
                     $currentRawMaterial = $rawMaterials->firstWhere('id', $rawMaterial->id)?->fresh() ?? Item::find($rawMaterial->id);
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

            });
         } catch (\Exception $e) {
             Log::error("Production failed for {$finishedGood->name}: " . $e->getMessage());
             $this->command->error("Production failed for {$finishedGood->name}: " . $e->getMessage());
         }
    }


    private function createAdditionalTransactions(Collection $rawMaterials, Collection $finishedGoods): void
    {
        $startDate = Carbon::now()->subMonth();

        // 1. Masuk Bahan Mentah (Pembelian Tambahan)
        $lakban = $rawMaterials->firstWhere('code', 'LB-BN-02');
        if ($lakban) {
            $qty = rand(20, 50);
            $date = $startDate->copy()->addDays(2);
            $lakban->increaseStock($qty);
            Transaction::create([
                'item_id' => $lakban->id, 'type' => 'masuk_mentah', 'quantity' => $qty,
                'nama_supplier' => 'Supplier Cepat Kirim', 'nomor_surat_jalan' => 'SJ/CK/'. $date->format('Ymd').'/LBN',
                'tanggal_surat_jalan' => $date->format('Y-m-d'), 'created_at' => $date, 'updated_at' => $date,
                'description' => 'Pembelian Lakban'
            ]);
        }

        // 2. Masuk Barang Jadi (Manual / Retur)
        $kotak = $finishedGoods->firstWhere('code', 'KPS-302010');
        if ($kotak) {
            $qty = rand(5, 15);
            $date = $startDate->copy()->addDays(5);
            $kotak->increaseStock($qty);
            Transaction::create([
                'item_id' => $kotak->id, 'type' => 'masuk_jadi', 'quantity' => $qty,
                'nama_supplier' => 'Ekspedisi Retur', 'nomor_surat_jalan' => 'RETUR/'. $date->format('Ymd').'/01', // Supplier diisi sumber retur
                'tanggal_surat_jalan' => $date->format('Y-m-d'), 'created_at' => $date, 'updated_at' => $date,
                'description' => 'Retur dari Customer X'
            ]);
        }

        // 3. Keluar Bahan Mentah (Dikirim/Jual)
        $foam = $rawMaterials->firstWhere('code', 'FR-PE-02');
        if ($foam?->quantity > 10) { // Gunakan null safe operator
            $qty = rand(5, 10);
            $date = $startDate->copy()->addDays(8);
            try { // Tambahkan try-catch untuk decreaseStock
                $foam->decreaseStock($qty);
                Transaction::create([
                    'item_id' => $foam->id, 'type' => 'keluar_mentah', 'quantity' => $qty,
                    'nama_customer' => 'Customer Langganan Foam', 'nomor_surat_jalan' => 'SJ/CUST/'. $date->format('Ymd').'/FR',
                    'tanggal_surat_jalan' => $date->format('Y-m-d'), 'created_at' => $date, 'updated_at' => $date,
                    'description' => 'Pengiriman roll foam'
                ]);
            } catch (\Exception $e) {
                 Log::error("Failed to decrease stock for {$foam->name} in seeder: " . $e->getMessage());
                 $this->command->error("Failed to decrease stock for {$foam->name} in seeder: " . $e->getMessage());
            }
        }

        // 4. Keluar Barang Jadi (Dikirim)
        $kotak = $finishedGoods->firstWhere('code', 'KPS-302010')?->fresh(); // Ambil data fresh
        if ($kotak && $kotak->quantity > 25) {
            $qty = rand(10, 25);
            $date = $startDate->copy()->addDays(12);
             try {
                $kotak->decreaseStock($qty);
                Transaction::create([
                    'item_id' => $kotak->id, 'type' => 'keluar_dikirim', 'quantity' => $qty,
                    'nama_customer' => 'Toko Online Sejahtera', 'nomor_surat_jalan' => 'SJ/TOS/'. $date->format('Ymd').'/KPS',
                    'tanggal_surat_jalan' => $date->format('Y-m-d'), 'created_at' => $date, 'updated_at' => $date,
                    'description' => 'Pengiriman pesanan #123'
                ]);
            } catch (\Exception $e) {
                 Log::error("Failed to decrease stock for {$kotak->name} in seeder: " . $e->getMessage());
                 $this->command->error("Failed to decrease stock for {$kotak->name} in seeder: " . $e->getMessage());
            }
        }

        // 5. Rusak Bahan Mentah
        $bubble = $rawMaterials->firstWhere('code', 'BW-125')?->fresh();
        if ($bubble && $bubble->quantity > 5) {
             $qty = rand(1, 5);
             $date = $startDate->copy()->addDays(15);
              try {
                $bubble->decreaseStock($qty);
                Transaction::create([
                    'item_id' => $bubble->id, 'type' => 'rusak_mentah', 'quantity' => $qty,
                    'description' => 'Terkena air saat penyimpanan', 'created_at' => $date, 'updated_at' => $date,
                ]);
             } catch (\Exception $e) {
                 Log::error("Failed to decrease stock for {$bubble->name} (damaged) in seeder: " . $e->getMessage());
                 $this->command->error("Failed to decrease stock for {$bubble->name} (damaged) in seeder: " . $e->getMessage());
             }
        }

         // 6. Rusak Barang Jadi
         $kotak = $finishedGoods->firstWhere('code', 'KPS-302010')?->fresh();
         if ($kotak && $kotak->quantity > 3) {
             $qty = rand(1, 3);
             $date = $startDate->copy()->addDays(18);
             try {
                $kotak->decreaseStock($qty);
                Transaction::create([
                    'item_id' => $kotak->id, 'type' => 'rusak_jadi', 'quantity' => $qty,
                    'description' => 'Tertimpa barang lain di gudang', 'created_at' => $date, 'updated_at' => $date,
                ]);
             } catch (\Exception $e) {
                 Log::error("Failed to decrease stock for {$kotak->name} (damaged) in seeder: " . $e->getMessage());
                 $this->command->error("Failed to decrease stock for {$kotak->name} (damaged) in seeder: " . $e->getMessage());
             }
         }

         // 7. Simulasi Produksi Lagi
         $paketBubble = $finishedGoods->firstWhere('code', 'PBW-05');
         if($paketBubble){
            $this->simulateProduction($paketBubble, $rawMaterials, rand(5, 15), $startDate->copy()->addDays(20));
         }
    }
}