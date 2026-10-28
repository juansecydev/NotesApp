<?php

namespace Tests\Feature;

use App\Models\Note;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Exercises authenticated note management and ownership isolation.
 */
class NoteControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Guests cannot use the note routes.
     */
    public function test_guest_is_redirected_from_note_routes(): void
    {
        $user = User::factory()->create();
        $note = Note::factory()->for($user)->create();

        $this->get(route('notes.index'))->assertRedirect(route('login'));
        $this->get(route('notes.create'))->assertRedirect(route('login'));
        $this->post(route('notes.store'), [])->assertRedirect(route('login'));
        $this->get(route('notes.show', $note))->assertRedirect(route('login'));
        $this->get(route('notes.edit', $note))->assertRedirect(route('login'));
        $this->put(route('notes.update', $note), [])->assertRedirect(route('login'));
        $this->delete(route('notes.destroy', $note))->assertRedirect(route('login'));
    }

    /**
     * The create form receives its state and submits to the note store route.
     */
    public function test_create_renders_the_note_form(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('notes.create'));

        $response->assertOk()
            ->assertSee('Create note')
            ->assertSee('action="'.route('notes.store').'"', false)
            ->assertSee('value="primary"', false)
            ->assertSee('value="secondary"', false)
            ->assertSee('value="success"', false)
            ->assertSee('value="danger"', false)
            ->assertSee('value="warning"', false)
            ->assertSee('value="info"', false)
            ->assertSee('bg-primary', false)
            ->assertSee('bg-secondary', false)
            ->assertSee('bg-success', false)
            ->assertSee('bg-danger', false)
            ->assertSee('bg-warning', false)
            ->assertSee('bg-info', false)
            ->assertSee('class="card-header h5 mb-0 note-color-preview-title bg-primary text-white"', false);
    }

    /**
     * The edit form receives its state and submits to the note update route.
     */
    public function test_edit_renders_the_note_form(): void
    {
        $user = User::factory()->create();
        $note = Note::factory()->for($user)->create();

        $response = $this->actingAs($user)->get(route('notes.edit', $note));

        $response->assertOk()
            ->assertSee('Edit note')
            ->assertSee('action="'.route('notes.update', $note).'"', false)
            ->assertSee('name="_method" value="PUT"', false);
    }

    /**
     * Note creation assigns ownership from the session and ignores forged owner IDs.
     */
    public function test_store_creates_a_note_for_the_authenticated_user(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('notes.store'), [
            'note_title' => 'Shopping list',
            'note_content' => 'Milk and bread',
            'note_user_id' => 999,
        ]);

        $note = Note::query()->firstOrFail();

        $response->assertRedirect(route('notes.show', $note));
        $this->assertDatabaseHas('notes', [
            'note_id' => $note->note_id,
            'note_user_id' => $user->user_id,
            'note_color' => 'primary',
            'note_title' => 'Shopping list',
            'note_content' => 'Milk and bread',
        ]);
    }

    /**
     * Submitted color names are stored as lowercase Bootstrap keywords.
     */
    public function test_store_normalizes_a_valid_color_to_lowercase(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('notes.store'), [
            'note_title' => 'Colored note',
            'note_content' => 'Keep the color lowercase.',
            'note_color' => 'WARNING',
        ]);

        $note = Note::query()->firstOrFail();

        $response->assertRedirect(route('notes.show', $note));
        $this->assertDatabaseHas('notes', [
            'note_id' => $note->note_id,
            'note_color' => 'warning',
        ]);

        $this->actingAs($user)
            ->get(route('notes.show', $note))
            ->assertOk()
            ->assertSee('class="card-header bg-warning text-dark"', false);
    }

    /**
     * An unsupported color falls back to primary instead of being persisted.
     */
    public function test_store_replaces_an_invalid_color_with_primary(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('notes.store'), [
            'note_title' => 'Default color note',
            'note_content' => 'Unsupported colors use primary.',
            'note_color' => 'violet',
        ]);

        $note = Note::query()->firstOrFail();

        $response->assertRedirect(route('notes.show', $note));
        $this->assertDatabaseHas('notes', [
            'note_id' => $note->note_id,
            'note_color' => 'primary',
        ]);
    }

    /**
     * Notes that exceed their configured field lengths are rejected.
     */
    public function test_store_rejects_note_fields_over_their_character_limits(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('notes.store'), [
            'note_title' => str_repeat('t', 31),
            'note_content' => str_repeat('n', 301),
        ]);

        $response->assertSessionHasErrors(['note_title', 'note_content']);
        $this->assertDatabaseCount('notes', 0);
    }

    /**
     * Unsupported legacy values render with the safe primary background.
     */
    public function test_show_uses_primary_background_for_an_invalid_stored_color(): void
    {
        $user = User::factory()->create();
        $note = Note::factory()->for($user)->create();
        DB::table('notes')->where('note_id', $note->note_id)->update(['note_color' => 'text-danger']);

        $response = $this->actingAs($user)->get(route('notes.show', $note));

        $response->assertOk()
            ->assertSee('class="card-header bg-primary text-white"', false)
            ->assertDontSee('bg-text-danger');
    }

    /**
     * The notes index contains only notes owned by the authenticated user.
     */
    public function test_index_does_not_display_other_users_notes(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $ownNote = Note::factory()->for($user)->create([
            'note_title' => 'My private note',
            'note_content' => 'My private note content',
            'note_color' => 'warning',
        ]);
        Note::factory()->for($otherUser)->create(['note_title' => 'Another private note']);

        $response = $this->actingAs($user)->get(route('notes.index'));

        $response->assertOk()
            ->assertSee($ownNote->note_title)
            ->assertSee($ownNote->note_content)
            ->assertSee('bg-'.$ownNote->note_color, false)
            ->assertSee('class="card h-100 note-card"', false)
            ->assertSee('class="card-header bg-warning text-dark"', false)
            ->assertSee('class="card-body d-flex flex-column"', false)
            ->assertSee('class="btn btn-warning"', false)
            ->assertSee('class="btn btn-danger"', false)
            ->assertSee('bi-pencil-square', false)
            ->assertSee('bi-trash', false)
            ->assertSee('href="'.route('notes.edit', $ownNote).'"', false)
            ->assertSee('action="'.route('notes.destroy', $ownNote).'"', false)
            ->assertSee('Are you sure you want to delete', false)
            ->assertSee('Confirm delete')
            ->assertDontSee('Another private note');
    }

    /**
     * The notes index displays ten notes per page.
     */
    public function test_index_displays_ten_notes_per_page(): void
    {
        $user = User::factory()->create();
        Note::factory()->for($user)->count(11)->create();

        $response = $this->actingAs($user)->get(route('notes.index'));

        $response->assertOk()
            ->assertViewHas('notes', function ($notes): bool {
                return $notes->count() === 10 && $notes->perPage() === 10;
            });
    }

    /**
     * Viewing another user's note returns not found.
     */
    public function test_show_returns_not_found_for_another_users_note(): void
    {
        $user = User::factory()->create();
        $otherNote = Note::factory()->create(['note_title' => 'Secret note']);

        $this->actingAs($user)
            ->get(route('notes.show', $otherNote))
            ->assertNotFound();
    }

    /**
     * Another user's note cannot be modified or deleted.
     */
    public function test_update_and_delete_return_not_found_for_another_users_note(): void
    {
        $user = User::factory()->create();
        $otherNote = Note::factory()->create();

        $response = $this->actingAs($user)->put(route('notes.update', $otherNote), [
            'note_title' => 'Hijacked',
            'note_content' => 'Not yours',
        ]);

        $response->assertNotFound();
        $this->assertDatabaseHas('notes', [
            'note_id' => $otherNote->note_id,
            'note_title' => $otherNote->note_title,
        ]);

        $this->delete(route('notes.destroy', $otherNote))->assertNotFound();
        $this->assertModelExists($otherNote);
    }

    /**
     * A valid update changes the authenticated user's note.
     */
    public function test_update_changes_an_owned_note(): void
    {
        $user = User::factory()->create();
        $note = Note::factory()->for($user)->create();

        $response = $this->actingAs($user)->put(route('notes.update', $note), [
            'note_title' => 'Updated title',
            'note_content' => 'Updated content',
            'note_color' => 'warning',
        ]);

        $response->assertRedirect(route('notes.show', $note));
        $this->assertDatabaseHas('notes', [
            'note_id' => $note->note_id,
            'note_user_id' => $user->user_id,
            'note_title' => 'Updated title',
            'note_content' => 'Updated content',
            'note_color' => 'warning',
        ]);
    }

    /**
     * Deleting an owned note removes it from storage.
     */
    public function test_destroy_deletes_an_owned_note(): void
    {
        $user = User::factory()->create();
        $note = Note::factory()->for($user)->create();

        $response = $this->actingAs($user)->delete(route('notes.destroy', $note));

        $response->assertRedirect(route('notes.index'));
        $this->assertDatabaseMissing('notes', ['note_id' => $note->note_id]);
    }
}
