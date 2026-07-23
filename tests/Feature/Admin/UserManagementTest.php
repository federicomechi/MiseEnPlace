<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_administrator_can_create_a_user(): void
    {
        $administrator = User::factory()->create(['email_verified_at' => now(), 'is_admin' => true]);

        $this->actingAs($administrator)
            ->post('/admin/users', [
                'name' => 'Nuovo utente',
                'email' => 'nuovo@example.test',
                'password' => 'password-sicura',
                'is_admin' => false,
            ])
            ->assertRedirect('/admin/users');

        $this->assertDatabaseHas('users', ['email' => 'nuovo@example.test', 'is_admin' => false]);
    }

    public function test_administrator_cannot_delete_own_account(): void
    {
        $administrator = User::factory()->create(['email_verified_at' => now(), 'is_admin' => true]);

        $this->actingAs($administrator)
            ->delete("/admin/users/{$administrator->id}")
            ->assertRedirect();

        $this->assertDatabaseHas('users', ['id' => $administrator->id]);
    }
}
