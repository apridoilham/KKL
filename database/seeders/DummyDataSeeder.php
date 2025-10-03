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
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        // Menonaktifkan pengecekan foreign key untuk proses truncate
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Mengosongkan tabel untuk menghindari data duplikat setiap kali seeder dijalankan
        User::truncate();
        Item::truncate();
        Transaction::truncate();

        // Mengaktifkan kembali foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $this->command->info('Membuat data pengguna...');
        $this->createUsers();

        $this->command->info('Membuat data barang dan transaksi...');
        $this->createItemsAndTransactions();

        $this->command->info('Proses seeding data dummy selesai!');
    }

    /**
     * Membuat data pengguna awal.
     */
    private function createUsers(): void
    {
        // Pengguna Admin Utama
        User::create([
            'name' => 'Admin Utama',
            'username' => 'admin',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'security_question' => 'Nama hewan peliharaan pertama?',
            'security_answer' => Hash::make('admin'),
        ]);

        // Pengguna Staff Utama
        User::create([
            'name' => 'Staff Gudang',
            'username' => 'staff',
            'password' => Hash::make('password'),
            'role' => 'staff',
            'security_question' => 'Warna favorit?',
            'security_answer' => Hash::make('staff'),
        ]);

        // Pengguna Staff Tambahan
        User::create([
            'name' => 'Budi Santoso',
            'username' => 'budi',
            'password' => Hash::make('password'),
            'role' => 'staff',
            'security_question' => 'Kota kelahiran?',
            'security_answer' => Hash::make('jakarta'),
        ]);
    }

    /**
     * Membuat data master barang beserta histori transaksinya.
     */
    private function createItemsAndTransactions(): void
    {
        // Daftar kategori dan item untuk data dummy
        $itemCatalog = [
            'Alat Tulis Kantor' => ['Kertas HVS A4 70gr', 'Pulpen Tinta Hitam', 'Spidol Whiteboard', 'Buku Tulis Hard Cover', 'Penghapus Papan Tulis'],
            'Elektronik' => ['Mouse Wireless Logitech', 'Keyboard Mechanical', 'Monitor LED 24 inch', 'Kabel HDMI 3m', 'Webcam HD 1080p'],
            'Perabotan' => ['Kursi Kantor Ergonomis', 'Meja Kerja Kayu', 'Lemari Arsip Besi', 'Lampu Meja Belajar', 'Papan Tulis Kaca'],
            'Pantry & Konsumsi' => ['Kopi Sachet Instan', 'Gula Pasir 1kg', 'Teh Celup Kotak', 'Air Mineral Galon', 'Biskuit Kaleng'],
            'Peralatan Kebersihan' => ['Cairan Pembersih Lantai', 'Kain Pel Microfiber', 'Sapu Ijuk', 'Tempat Sampah 20L', 'Pengharum Ruangan Otomatis'],
        ];

        foreach ($itemCatalog as $category => $items) {
            foreach ($items as $itemName) {
                // 1. Buat item dengan stok awal 0
                $item = Item::create([
                    'name' => $itemName,
                    'category' => $category,
                    'code' => strtoupper(substr($category, 0, 3)) . '-' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT),
                    'quantity' => 0,
                    'status' => 'out',
                    'created_at' => now()->subYear()->addDays(rand(1, 30)), // Dibuat acak dalam sebulan pertama tahun lalu
                ]);

                $currentStock = 0;
                $transactionDate = Carbon::instance($item->created_at)->addDay();

                // 2. Buat transaksi pertama (barang masuk) untuk mengisi stok awal
                $initialStock = rand(100, 250);
                Transaction::create([
                    'item_id' => $item->id,
                    'type' => 'in',
                    'quantity' => $initialStock,
                    'description' => 'Stok awal',
                    'created_at' => $transactionDate,
                    'updated_at' => $transactionDate,
                ]);
                $currentStock = $initialStock;
                $transactionDate->addDays(rand(1, 7)); // Maju beberapa hari untuk transaksi berikutnya

                // 3. Buat 15-40 transaksi acak untuk setiap barang
                for ($i = 0; $i < rand(15, 40); $i++) {
                    // Tentukan tipe transaksi secara acak (lebih banyak keluar daripada masuk)
                    $type = ['in', 'out', 'out', 'damaged', 'out'][rand(0, 4)];

                    if ($transactionDate->isAfter(now())) {
                        break; // Jangan buat transaksi di masa depan
                    }

                    if ($type === 'in') {
                        $quantity = rand(20, 50);
                        $currentStock += $quantity;
                        $description = 'Penambahan stok';
                    } else { // 'out' atau 'damaged'
                        if ($currentStock < 5) continue; // Lewati jika stok hampir habis
                        $quantity = rand(1, min(15, floor($currentStock * 0.5))); // Ambil maksimal setengah stok
                        $currentStock -= $quantity;
                        $description = $type === 'out' ? 'Penggunaan operasional' : 'Barang ditemukan rusak';
                    }

                    Transaction::create([
                        'item_id' => $item->id,
                        'type' => $type,
                        'quantity' => $quantity,
                        'description' => $description,
                        'created_at' => $transactionDate,
                        'updated_at' => $transactionDate,
                    ]);

                    // Majukan tanggal untuk transaksi berikutnya
                    $transactionDate->addDays(rand(3, 15));
                }

                // 4. Update total kuantitas dan status akhir di tabel items
                $item->quantity = $currentStock;
                $item->status = $currentStock > 0 ? 'available' : 'out';
                $item->save();
            }
        }
    }
}