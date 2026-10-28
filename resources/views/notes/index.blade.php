@extends('layouts.app')

@section('title', 'Notes')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Notes</h1>
        <a class="btn btn-primary" href="{{ route('notes.create') }}">
            <i class="bi bi-plus-lg me-1" aria-hidden="true"></i>
            Add note
        </a>
    </div>

    @if ($notes->isEmpty())
        <p>No notes have been added.</p>
    @else
        <div class="row row-cols-1 row-cols-md-2 g-3 mb-4">
            @foreach ($notes as $note)
                <div class="col">
                    <article class="card h-100 note-card">
                        <header class="card-header bg-{{ $note->note_color }} {{ in_array($note->note_color, ['warning', 'info'], true) ? 'text-dark' : 'text-white' }}">
                            <h2 class="card-title h5 mb-0">{{ $note->note_title }}</h2>
                        </header>
                        <div class="card-body d-flex flex-column">
                            <p class="card-text">{{ $note->note_content }}</p>

                            <div class="d-flex flex-wrap gap-2 mt-auto pt-2">
                                <a class="btn btn-warning" href="{{ route('notes.edit', $note) }}">
                                    <i class="bi bi-pencil-square me-1" aria-hidden="true"></i>
                                    Edit
                                </a>
                                <button
                                    class="btn btn-danger"
                                    type="button"
                                    data-bs-toggle="collapse"
                                    data-bs-target="#delete-note-{{ $note->note_id }}"
                                    aria-expanded="false"
                                    aria-controls="delete-note-{{ $note->note_id }}"
                                >
                                    <i class="bi bi-trash me-1" aria-hidden="true"></i>
                                    Delete
                                </button>
                            </div>

                            <div class="collapse mt-3" id="delete-note-{{ $note->note_id }}">
                                <div class="alert bg-danger text-white mb-0" role="alert">
                                    <p>Are you sure you want to delete “{{ $note->note_title }}”?</p>
                                    <div class="d-flex flex-wrap gap-2">
                                        <button
                                            class="btn btn-outline-secondary"
                                            type="button"
                                            data-bs-toggle="collapse"
                                            data-bs-target="#delete-note-{{ $note->note_id }}"
                                            aria-controls="delete-note-{{ $note->note_id }}"
                                        >
                                            <i class="bi bi-x-lg me-1" aria-hidden="true"></i>
                                            Cancel
                                        </button>
                                        <form method="POST" action="{{ route('notes.destroy', $note) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-danger" type="submit">
                                                <i class="bi bi-trash me-1" aria-hidden="true"></i>
                                                Confirm delete
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </article>
                </div>
            @endforeach
        </div>
    @endif

    <div class="notes-pagination mt-4 d-flex justify-content-center">
        {{ $notes->onEachSide(1)->links() }}
    </div>
@endsection
