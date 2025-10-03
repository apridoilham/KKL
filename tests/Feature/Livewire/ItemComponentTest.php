<?php

namespace Tests\Feature\Livewire;

use App\Http\Livewire\ItemComponent;
use App\Models\Item;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ItemComponentTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function an_admin_can_see_the_item_management_page()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        $this->get('/item')->assertSeeLivewire('item-component');
    }

    /** @test */
    public function an_admin_can_create_a_new_item()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        Livewire::test(ItemComponent::class)
            ->set('name', 'Laptop Baru')
            ->set('category', 'Elektronik')
            ->set('code', 'LP-001')
            ->call('store');

        $this->assertTrue(Item::where('name', 'Laptop Baru')->exists());
    }

    /** @test */
    public function item_name_is_required()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        Livewire::test(ItemComponent::class)
            ->set('name', '') // Nama dikosongkan
            ->call('store')
            ->assertHasErrors(['name' => 'required']);
    }

    /** @test */
    public function an_admin_can_delete_an_item()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        $item = Item::factory()->create();

        Livewire::test(ItemComponent::class)
            ->call('delete', $item->id);

        $this->assertDatabaseMissing('items', ['id' => $item->id]);
    }

    /** @test */
    public function a_staff_cannot_delete_an_item()
    {
        $staff = User::factory()->create(['role' => 'staff']);
        $this->actingAs($staff);

        $item = Item::factory()->create();

        Livewire::test(ItemComponent::class)
            ->call('delete', $item->id);
            
        // Pastikan item masih ada di database
        $this->assertDatabaseHas('items', ['id' => $item->id]);
    }
}