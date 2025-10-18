<?php

namespace Tests\Feature\Livewire;

use App\Http\Livewire\ItemComponent;
use App\Models\Item;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ItemComponentTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $staff;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->staff = User::factory()->create(['role' => 'produksi']);
        // Jalankan migrasi khusus untuk testing (opsional tapi baik)
        // $this->artisan('migrate');
    }

    #[Test]
    public function an_admin_can_create_a_new_item(): void
    {
        Livewire::actingAs($this->admin)
            ->test(ItemComponent::class)
            ->call('create')
            ->set('name', 'Laptop Baru')
            // ->set('category', 'Elektronik') // <-- Hapus baris ini
            ->set('code', 'LP-001')
            ->set('harga_beli', 5000000) // Tambahkan jika perlu
            ->set('harga_jual', 7500000) // Tambahkan jika perlu
            ->set('item_type', 'barang_jadi')
            ->call('store');

        $this->assertDatabaseHas('items', ['name' => 'Laptop Baru', 'code' => 'LP-001']);
    }

    #[Test]
    public function an_admin_can_update_an_item(): void
    {
        $item = Item::factory()->create(['name' => 'Nama Lama']);

        Livewire::actingAs($this->admin)
            ->test(ItemComponent::class)
            ->call('edit', $item->id)
            ->set('name', 'Nama Baru Diedit')
            // Hapus set category jika ada sebelumnya
            ->call('store');

        $this->assertDatabaseHas('items', ['id' => $item->id, 'name' => 'Nama Baru Diedit']);
    }

    #[Test]
    public function item_name_is_required(): void
    {
        Livewire::actingAs($this->admin)
            ->test(ItemComponent::class)
            ->set('name', '')
            ->call('store')
            ->assertHasErrors(['name' => 'required']);
    }

    #[Test]
    public function an_admin_can_delete_an_item(): void
    {
        $item = Item::factory()->create();

        Livewire::actingAs($this->admin)
            ->test(ItemComponent::class)
            ->call('delete', $item->id);

        $this->assertDatabaseMissing('items', ['id' => $item->id]);
    }

    #[Test]
    public function a_non_admin_cannot_access_item_management_features(): void
    {
        $item = Item::factory()->create();

        Livewire::actingAs($this->staff)
            ->test(ItemComponent::class)
            ->call('delete', $item->id)
            ->assertForbidden();

        $this->assertDatabaseHas('items', ['id' => $item->id]);
    }
}