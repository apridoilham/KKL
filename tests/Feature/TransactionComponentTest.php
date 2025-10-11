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
        $this->admin = User::factory()->create(['role' => 'admin']);
    }

    #[Test]
    public function it_increases_stock_on_an_in_transaction(): void
    {
        $item = Item::create(['name' => 'Tepung', 'item_type' => 'barang_mentah', 'quantity' => 50]);

        Livewire::actingAs($this->admin)
            ->test(TransactionComponent::class)
            ->set('type', 'masuk_mentah')
            ->set('itemId', $item->id)
            ->set('quantity', 10)
            ->call('store');

        $this->assertEquals(60, $item->fresh()->quantity);

        $this->assertDatabaseHas('transactions', [
            'item_id' => $item->id,
            'type' => 'masuk_mentah',
            'quantity' => 10,
        ]);
    }

    #[Test]
    public function it_decreases_stock_on_an_out_transaction(): void
    {
        $item = Item::create(['name' => 'Tepung', 'item_type' => 'barang_mentah', 'quantity' => 50]);

        Livewire::actingAs($this->admin)
            ->test(TransactionComponent::class)
            ->set('type', 'keluar_mentah')
            ->set('itemId', $item->id)
            ->set('quantity', 5)
            ->call('store');

        $this->assertEquals(45, $item->fresh()->quantity);

        $this->assertDatabaseHas('transactions', [
            'item_id' => $item->id,
            'type' => 'keluar_mentah',
            'quantity' => 5,
        ]);
    }

    #[Test]
    public function it_fails_if_stock_is_insufficient_for_an_out_transaction(): void
    {
        $item = Item::create(['name' => 'Tepung', 'item_type' => 'barang_mentah', 'quantity' => 10]);

        Livewire::actingAs($this->admin)
            ->test(TransactionComponent::class)
            ->set('type', 'keluar_mentah')
            ->set('itemId', $item->id)
            ->set('quantity', 15)
            ->call('store')
            ->assertDispatched('toast', status: 'failed');

        $this->assertEquals(10, $item->fresh()->quantity);

        $this->assertDatabaseMissing('transactions', [
            'item_id' => $item->id,
            'quantity' => 15,
        ]);
    }
}