<?php

namespace App\Http\Controllers;

use App\Enums\NoteColor;
use App\Models\Note;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * Provides CRUD actions for notes owned by the authenticated user.
 */
class NoteController extends Controller
{
    /**
     * Display the authenticated user's notes.
     *
     * @param  Request  $request  The authenticated browser request.
     * @return View The paginated personal note list.
     */
    public function index(Request $request): View
    {
        return view('notes.index', [
            'notes' => $request->user()->notes()
                ->orderByDesc('note_created_at')
                ->orderByDesc('note_id')
                ->paginate(15),
        ]);
    }

    /**
     * Show the form for creating a note.
     *
     * @return View The note form with its default color.
     */
    public function create(): View
    {
        return view('notes.form', [
            'note' => new Note,
            'isEditing' => false,
        ]);
    }

    /**
     * Validate and create a note owned by the authenticated user.
     *
     * @param  Request  $request  The note form submission.
     * @return RedirectResponse A redirect to the created note.
     *
     * @throws ValidationException When the submitted note data is invalid.
     */
    public function store(Request $request): RedirectResponse
    {
        $this->normalizeNoteColor($request);

        $validated = $request->validate([
            'note_color' => ['sometimes', 'required', 'string', 'in:primary,secondary,success,danger,warning,info'],
            'note_title' => ['required', 'string', 'max:30'],
            'note_content' => ['required', 'string', 'max:300'],
        ]);

        $note = $request->user()->notes()->create($validated);

        //return redirect()->route('notes.show', $note)->with('status', 'Note created.');
        return redirect()->route('notes.index')->with('status', 'Note created.');
    }

    /**
     * Display one of the authenticated user's notes.
     *
     * @param  Request  $request  The authenticated browser request.
     * @param  Note  $note  The note resolved from the route.
     * @return View The note detail page.
     */
    public function show(Request $request, Note $note): View
    {
        $note = $this->findOwnedNote($request->user(), $note);

        return view('notes.show', ['note' => $note]);
    }

    /**
     * Show the form for editing one of the authenticated user's notes.
     *
     * @param  Request  $request  The authenticated browser request.
     * @param  Note  $note  The note resolved from the route.
     * @return View The note edit form.
     */
    public function edit(Request $request, Note $note): View
    {
        return view('notes.form', [
            'note' => $this->findOwnedNote($request->user(), $note),
            'isEditing' => true,
        ]);
    }

    /**
     * Validate and update one of the authenticated user's notes.
     *
     * @param  Request  $request  The note form submission.
     * @param  Note  $note  The note resolved from the route.
     * @return RedirectResponse A redirect to the updated note.
     *
     * @throws ValidationException When the submitted note data is invalid.
     */
    public function update(Request $request, Note $note): RedirectResponse
    {
        $note = $this->findOwnedNote($request->user(), $note);
        $this->normalizeNoteColor($request);

        $note->update($request->validate([
            'note_color' => ['sometimes', 'required', 'string', 'in:primary,secondary,success,danger,warning,info'],
            'note_title' => ['required', 'string', 'max:30'],
            'note_content' => ['required', 'string', 'max:300'],
        ]));

        return redirect()->route('notes.show', $note)->with('status', 'Note updated.');
    }

    /**
     * Delete one of the authenticated user's notes.
     *
     * @param  Request  $request  The authenticated browser request.
     * @param  Note  $note  The note resolved from the route.
     * @return RedirectResponse A redirect to the user's note list.
     */
    public function destroy(Request $request, Note $note): RedirectResponse
    {
        $this->findOwnedNote($request->user(), $note)->delete();

        return redirect()->route('notes.index')->with('status', 'Note deleted.');
    }

    /**
     * Resolve a note only when it belongs to the supplied user.
     *
     * @param  User  $user  The authenticated owner.
     * @param  Note  $note  The note initially resolved from the route.
     * @return Note The note scoped to the supplied owner.
     */
    private function findOwnedNote(User $user, Note $note): Note
    {
        return $user->notes()->whereKey($note->getKey())->firstOrFail();
    }

    /**
     * Lowercase supported color keywords and replace unsupported strings with primary.
     *
     * @param  Request  $request  The note submission to normalize.
     */
    private function normalizeNoteColor(Request $request): void
    {
        if (is_string($request->input('note_color'))) {
            $request->merge([
                'note_color' => NoteColor::normalize($request->input('note_color')),
            ]);
        }
    }
}
