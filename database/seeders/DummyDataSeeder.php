<?php

namespace Database\Seeders;

use App\Models\Item;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
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
        User::create(['name' => 'Admin Utama', 'username' => 'admin', 'password' => Hash::make('password'), 'role' => 'admin', 'security_question' => 'Nama hewan?', 'security_answer' => Hash::make('admin'),]);
        User::create(['name' => 'Staff Produksi', 'username' => 'produksi', 'password' => Hash::make('password'), 'role' => 'produksi', 'security_question' => 'Warna favorit?', 'security_answer' => Hash::make('produksi'),]);
        User::create(['name' => 'Staff Pengiriman', 'username' => 'pengiriman', 'password' => Hash::make('password'), 'role' => 'pengiriman', 'security_question' => 'Kota kelahiran?', 'security_answer' => Hash::make('pengiriman'),]);
    }

    private function createRawMaterialsAndTransactions(): \Illuminate\Support\Collection
    {
        $rawItemsData = [
            'Bahan Kue' => ['Tepung Terigu', 'Gula Pasir', 'Telur Ayam', 'Mentega'],
            'Elektronik' => ['CPU Intel i7', 'RAM 16GB DDR4', 'SSD 1TB NVMe', 'Casing PC ATX'],
        ];

        $createdItems = collect();

        foreach ($rawItemsData as $category => $items) {
            foreach ($items as $itemName) {
                $item = Item::create([
                    'name' => $itemName,
                    'category' => $category,
                    'item_type' => 'barang_mentah',
                    'code' => strtoupper(substr($category, 0, 3)) . '-' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT),
                    'quantity' => 0,
                    'status' => 'out',
                    'created_at' => now()->subMonths(rand(1, 6)),
                ]);

                $initialStock = rand(500, 1000);
                $item->increaseStock($initialStock);
                Transaction::create([
                    'item_id' => $item->id,
                    'type' => 'masuk_mentah',
                    'quantity' => $initialStock,
                    'description' => 'Stok awal dari pemasok',
                    'created_at' => $item->created_at->addDay(),
                ]);
                $createdItems->push($item);
            }
        }
        return $createdItems;
    }

    private function createFinishedGoodsAndBom(\Illuminate\Support\Collection $rawMaterials): void
    {
        $kue = Item::create(['name' => 'Kue Bolu', 'category' => 'Makanan Jadi', 'item_type' => 'barang_jadi', 'code' => 'PROD-KUE01']);
        $kue->bomRawMaterials()->attach([
            $rawMaterials->firstWhere('name', 'Tepung Terigu')->id => ['quantity_required' => 2],
            $rawMaterials->firstWhere('name', 'Gula Pasir')->id => ['quantity_required' => 1],
            $rawMaterials->firstWhere('name', 'Telur Ayam')->id => ['quantity_required' => 4],
            $rawMaterials->firstWhere('name', 'Mentega')->id => ['quantity_required' => 1],
        ]);

        $komputer = Item::create(['name' => 'PC Gaming Rakitan', 'category' => 'Elektronik Jadi', 'item_type' => 'barang_jadi', 'code' => 'PROD-PC01']);
        $komputer->bomRawMaterials()->attach([
            $rawMaterials->firstWhere('name', 'CPU Intel i7')->id => ['quantity_required' => 1],
            $rawMaterials->firstWhere('name', 'RAM 16GB DDR4')->id => ['quantity_required' => 2],
            $rawMaterials->firstWhere('name', 'SSD 1TB NVMe')->id => ['quantity_required' => 1],
            $rawMaterials->firstWhere('name', 'Casing PC ATX')->id => ['quantity_required' => 1],
        ]);
    }
}