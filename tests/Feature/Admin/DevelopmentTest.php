<?php

namespace Tests\Feature\Admin;

use App\Models\DevelopmentEntry;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DevelopmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_administrator_can_manage_development_entries(): void
    {
        $administrator = User::factory()->create(['is_admin' => true, 'role' => User::ROLE_FULL_ACCESS]);

        $this->actingAs($administrator)
            ->post('/admin/development', [
                'title' => 'Importazione FileMaker',
                'description' => 'Mappatura iniziale dei dati.',
                'link' => 'https://example.test/filemaker',
            ])
            ->assertRedirect();

        $entry = DevelopmentEntry::firstOrFail();

        $this->actingAs($administrator)
            ->put("/admin/development/{$entry->id}", [
                'title' => 'Importazione FileMaker aggiornata',
                'description' => 'Mappatura verificata.',
                'link' => 'https://example.test/mappatura',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('development_entries', ['title' => 'Importazione FileMaker aggiornata']);

        $this->actingAs($administrator)
            ->delete("/admin/development/{$entry->id}")
            ->assertRedirect('/admin/development');

        $this->assertDatabaseMissing('development_entries', ['id' => $entry->id]);
    }
}
