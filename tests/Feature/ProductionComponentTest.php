<?php

namespace Tests\Feature; // Pastikan namespace benar

use App\Http\Livewire\ProductionComponent;
use App\Models\Item;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ProductionComponentTest extends TestCase
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
    public function it_can_produce_a_finished_good_and_updates_stock_correctly(): void
    {
        $barangJadi = Item::factory()->create([ // Gunakan factory
            'name' => 'Kue Bolu',
            'item_type' => 'barang_jadi',
            'quantity' => 10,
        ]);
        $tepung = Item::factory()->create(['name' => 'Tepung', 'item_type' => 'barang_mentah', 'quantity' => 100]);
        $gula = Item::factory()->create(['name' => 'Gula', 'item_type' => 'barang_mentah', 'quantity' => 100]);

        $barangJadi->bomRawMaterials()->attach([
            $tepung->id => ['quantity_required' => 2],
            $gula->id => ['quantity_required' => 1],
        ]);

        Livewire::actingAs($this->admin)
            ->test(ProductionComponent::class)
            ->set('selectedFinishedGoodId', $barangJadi->id)
            ->set('quantityToProduce', 5)
            ->call('produce') // Pastikan 'produce' berhasil dipanggil
            ->assertDispatched('toast', status: 'success'); // Cek apakah toast success muncul


        // Refresh data setelah aksi Livewire
        $barangJadi->refresh();
        $tepung->refresh();
        $gula->refresh();

        $this->assertEquals(15, $barangJadi->quantity);
        $this->assertEquals(90, $tepung->quantity);
        $this->assertEquals(95, $gula->quantity);

        $this->assertDatabaseHas('transactions', [
            'item_id' => $barangJadi->id,
            'type' => 'produksi_jadi', // <-- Ganti tipe transaksi
            'quantity' => 5,
        ]);
        $this->assertDatabaseHas('transactions', [
            'item_id' => $tepung->id,
            'type' => 'produksi_terpakai', // <-- Ganti tipe transaksi
            'quantity' => 10, // 2 * 5
        ]);
        $this->assertDatabaseHas('transactions', [
            'item_id' => $gula->id,
            'type' => 'produksi_terpakai', // <-- Ganti tipe transaksi
            'quantity' => 5, // 1 * 5
        ]);
    }
}