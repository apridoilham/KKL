<?php

namespace Tests\Feature; // Pastikan namespace benar

use App\Http\Livewire\TransactionComponent;
use App\Models\Item;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TransactionComponentTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin']);
        // Jalankan migrasi khusus untuk testing (opsional tapi baik)
        // $this->artisan('migrate');
    }

    #[Test]
    public function it_increases_stock_on_an_in_transaction(): void
    {
        $item = Item::factory()->create(['name' => 'Tepung', 'item_type' => 'barang_mentah', 'quantity' => 50]);

        Livewire::actingAs($this->admin)
            ->test(TransactionComponent::class)
            ->set('type', 'masuk_mentah')
            ->set('itemId', $item->id)
            ->set('quantity', 10)
            // Tambahkan field required untuk 'masuk_mentah'
            ->set('nama_supplier', 'Supplier Test')
            ->set('nomor_surat_jalan', 'SJ-001')
            ->set('tanggal_surat_jalan', now()->format('Y-m-d'))
            ->call('store')
            ->assertDispatched('toast', status: 'success'); // Pastikan berhasil

        $this->assertEquals(60, $item->fresh()->quantity); // Cek ulang stok setelah refresh

        $this->assertDatabaseHas('transactions', [
            'item_id' => $item->id,
            'type' => 'masuk_mentah',
            'quantity' => 10,
            'nama_supplier' => 'Supplier Test', // Verifikasi data tambahan
            'nomor_surat_jalan' => 'SJ-001',
        ]);
    }

    #[Test]
    public function it_decreases_stock_on_an_out_transaction(): void
    {
        $item = Item::factory()->create(['name' => 'Tepung', 'item_type' => 'barang_mentah', 'quantity' => 50]);

        Livewire::actingAs($this->admin)
            ->test(TransactionComponent::class)
            ->set('type', 'keluar_mentah')
            ->set('itemId', $item->id)
            ->set('quantity', 5)
             // Tambahkan field required untuk 'keluar_mentah'
            ->set('nama_customer', 'Customer Test')
            ->set('nomor_surat_jalan', 'SJ-002')
            ->set('tanggal_surat_jalan', now()->format('Y-m-d'))
            ->call('store')
            ->assertDispatched('toast', status: 'success'); // Pastikan berhasil

        $this->assertEquals(45, $item->fresh()->quantity); // Cek ulang stok setelah refresh

        $this->assertDatabaseHas('transactions', [
            'item_id' => $item->id,
            'type' => 'keluar_mentah',
            'quantity' => 5,
            'nama_customer' => 'Customer Test', // Verifikasi data tambahan
            'nomor_surat_jalan' => 'SJ-002',
        ]);
    }

    #[Test]
    public function it_fails_if_stock_is_insufficient_for_an_out_transaction(): void
    {
        $item = Item::factory()->create(['name' => 'Tepung', 'item_type' => 'barang_mentah', 'quantity' => 10]);

        Livewire::actingAs($this->admin)
            ->test(TransactionComponent::class)
            ->set('type', 'keluar_mentah')
            ->set('itemId', $item->id)
            ->set('quantity', 15) // Jumlah melebihi stok
             // Tambahkan field required untuk 'keluar_mentah' (meskipun akan gagal)
            ->set('nama_customer', 'Customer Test')
            ->set('nomor_surat_jalan', 'SJ-003')
            ->set('tanggal_surat_jalan', now()->format('Y-m-d'))
            ->call('store')
            ->assertDispatched('toast', status: 'failed'); // Harusnya gagal

        $this->assertEquals(10, $item->fresh()->quantity); // Pastikan stok tidak berubah

        $this->assertDatabaseMissing('transactions', [ // Pastikan transaksi tidak dibuat
            'item_id' => $item->id,
            'quantity' => 15,
        ]);
    }
}