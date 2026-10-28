<?php

namespace Tests\Feature\Database\Migrations;

use App\Models\Note;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Verifies normalization of existing note colors and the database default.
 */
class NormalizeNoteColorsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * The migration normalizes legacy colors, defaults unknown values, and is reversible.
     */
    public function test_migration_normalizes_legacy_colors_and_restores_them_on_rollback(): void
    {
        $user = User::factory()->create();
        $legacyNote = Note::factory()->for($user)->create();
        $unknownNote = Note::factory()->for($user)->create();
        DB::table('notes')->where('note_id', $legacyNote->note_id)->update(['note_color' => 'BG-WARNING']);
        DB::table('notes')->where('note_id', $unknownNote->note_id)->update(['note_color' => 'text-danger']);

        $migration = require base_path('database/migrations/2026_10_25_013927_normalize_note_colors.php');
        $migration->up();

        $this->assertDatabaseHas('notes', [
            'note_id' => $legacyNote->note_id,
            'note_color' => 'warning',
        ]);
        $this->assertDatabaseHas('notes', [
            'note_id' => $unknownNote->note_id,
            'note_color' => 'primary',
        ]);

        $migration->down();

        $this->assertDatabaseHas('notes', [
            'note_id' => $legacyNote->note_id,
            'note_color' => 'bg-warning',
        ]);
        $this->assertDatabaseHas('notes', [
            'note_id' => $unknownNote->note_id,
            'note_color' => 'bg-primary',
        ]);
    }
}
