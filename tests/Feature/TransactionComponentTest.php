<?php

namespace Tests\Feature;

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
        // Cari user admin yang dibuat oleh migrasi
        $this->admin = User::where('username', 'admin')->firstOrFail();
    }

    #[Test]
    public function it_increases_stock_on_an_in_transaction(): void
    {
        // Arrange: Siapkan bahan mentah dengan stok awal 50
        $item = Item::create(['name' => 'Tepung', 'item_type' => 'barang_mentah', 'quantity' => 50]);

        // Act: Buat transaksi "masuk_mentah" sebanyak 10
        Livewire::actingAs($this->admin)
            ->test(TransactionComponent::class)
            ->set('type', 'masuk_mentah')
            ->set('itemId', $item->id)
            ->set('quantity', 10)
            ->call('store');

        // Assert: Cek hasilnya
        // 1. Pastikan stok sekarang menjadi 60 (50 + 10)
        $this->assertEquals(60, $item->fresh()->quantity);

        // 2. Pastikan ada catatan transaksi di database
        $this->assertDatabaseHas('transactions', [
            'item_id' => $item->id,
            'type' => 'masuk_mentah',
            'quantity' => 10,
        ]);
    }

    #[Test]
    public function it_decreases_stock_on_an_out_transaction(): void
    {
        // Arrange: Siapkan bahan mentah dengan stok awal 50
        $item = Item::create(['name' => 'Tepung', 'item_type' => 'barang_mentah', 'quantity' => 50]);

        // Act: Buat transaksi "keluar_mentah" sebanyak 5
        Livewire::actingAs($this->admin)
            ->test(TransactionComponent::class)
            ->set('type', 'keluar_mentah')
            ->set('itemId', $item->id)
            ->set('quantity', 5)
            ->call('store');

        // Assert: Cek hasilnya
        // 1. Pastikan stok sekarang menjadi 45 (50 - 5)
        $this->assertEquals(45, $item->fresh()->quantity);

        // 2. Pastikan ada catatan transaksi di database
        $this->assertDatabaseHas('transactions', [
            'item_id' => $item->id,
            'type' => 'keluar_mentah',
            'quantity' => 5,
        ]);
    }

    #[Test]
    public function it_fails_if_stock_is_insufficient_for_an_out_transaction(): void
    {
        // Arrange: Siapkan bahan mentah dengan stok HANYA 10
        $item = Item::create(['name' => 'Tepung', 'item_type' => 'barang_mentah', 'quantity' => 10]);

        // Act: Coba buat transaksi "keluar_mentah" sebanyak 15 (lebih dari stok)
        Livewire::actingAs($this->admin)
            ->test(TransactionComponent::class)
            ->set('type', 'keluar_mentah')
            ->set('itemId', $item->id)
            ->set('quantity', 15)
            ->call('store')
            ->assertDispatched('toast', status: 'failed'); // Pastikan notifikasi kegagalan muncul

        // Assert: Cek hasilnya
        // 1. Pastikan stok TIDAK BERUBAH dan tetap 10
        $this->assertEquals(10, $item->fresh()->quantity);

        // 2. Pastikan TIDAK ADA transaksi baru yang tercatat di database
        $this->assertDatabaseMissing('transactions', [
            'item_id' => $item->id,
            'quantity' => 15,
        ]);
    }
}