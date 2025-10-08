<?php

namespace Tests\Feature;

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

        // Menjalankan migrasi akan otomatis membuat user admin
        // Jadi, kita hanya perlu mencarinya, bukan membuatnya lagi.
        $this->admin = User::where('username', 'admin')->firstOrFail();
    }

    #[Test]
    public function it_can_produce_a_finished_good_and_updates_stock_correctly(): void
    {
        // ARRANGE: Persiapan data awal
        $barangJadi = Item::create([
            'name' => 'Kue Bolu',
            'item_type' => 'barang_jadi',
            'quantity' => 10,
        ]);
        $tepung = Item::create(['name' => 'Tepung', 'item_type' => 'barang_mentah', 'quantity' => 100]);
        $gula = Item::create(['name' => 'Gula', 'item_type' => 'barang_mentah', 'quantity' => 100]);
        
        $barangJadi->bomRawMaterials()->attach([
            $tepung->id => ['quantity_required' => 2],
            $gula->id => ['quantity_required' => 1],
        ]);

        // ACT: Lakukan aksi yang ingin di-test
        Livewire::actingAs($this->admin)
            ->test(ProductionComponent::class)
            ->set('selectedFinishedGoodId', $barangJadi->id)
            ->set('quantityToProduce', 5)
            ->call('produce');

        // ASSERT: Lakukan pengecekan hasil
        $this->assertEquals(15, $barangJadi->fresh()->quantity);
        $this->assertEquals(90, $tepung->fresh()->quantity);
        $this->assertEquals(95, $gula->fresh()->quantity);

        $this->assertDatabaseHas('transactions', [
            'item_id' => $barangJadi->id,
            'type' => 'masuk_jadi',
            'quantity' => 5,
        ]);
        $this->assertDatabaseHas('transactions', [
            'item_id' => $tepung->id,
            'type' => 'keluar_terpakai',
            'quantity' => 10,
        ]);
        $this->assertDatabaseHas('transactions', [
            'item_id' => $gula->id,
            'type' => 'keluar_terpakai',
            'quantity' => 5,
        ]);
    }
}