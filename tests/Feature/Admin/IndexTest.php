<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_administrator_can_open_the_administration_hub(): void
    {
        $administrator = User::factory()->create([
            'email_verified_at' => now(),
            'is_admin' => true,
        ]);

        $this->actingAs($administrator)
            ->get('/admin')
            ->assertOk();
    }

    public function test_non_administrator_cannot_open_the_administration_hub(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
            'is_admin' => false,
        ]);

        $this->actingAs($user)
            ->get('/admin')
            ->assertForbidden();
    }
}
