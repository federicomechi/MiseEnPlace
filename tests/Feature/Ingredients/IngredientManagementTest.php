<?php

namespace Tests\Feature\Ingredients;

use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IngredientManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_kitchen_user_can_create_an_ingredient_with_a_supplier_price(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_KITCHEN, 'is_admin' => false]);
        $supplier = Supplier::create(['name' => 'Orto del Sole', 'is_active' => true]);

        $this->actingAs($user)
            ->post('/operativita/ingredients', [
                'code' => 'POM-001',
                'name' => 'Pomodoro San Marzano',
                'category' => 'Ortaggi',
                'unit' => 'kg',
                'available_for_bar' => false,
                'prices' => [[
                    'supplier_id' => $supplier->id,
                    'package_name' => 'Cassetta',
                    'package_quantity' => 5,
                    'package_unit' => 'kg',
                    'package_price' => 17.5,
                    'valid_from' => '2026-07-23',
                    'is_current' => true,
                ]],
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('ingredients', [
            'code' => 'POM-001',
            'name' => 'Pomodoro San Marzano',
            'unit_cost' => 3.5,
        ]);

        $this->assertDatabaseHas('supplier_prices', [
            'supplier_id' => $supplier->id,
            'package_price' => 17.5,
            'is_current' => true,
        ]);
    }
}
