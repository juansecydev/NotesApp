@extends('layouts.app')

@section('title', $note->note_title)

@section('content')
    <article class="card note-card">
        <header class="card-header bg-{{ $note->note_color }} {{ in_array($note->note_color, ['warning', 'info'], true) ? 'text-dark' : 'text-white' }}">
            <h1 class="card-title h4 mb-0">{{ $note->note_title }}</h1>
        </header>
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <p>Your note</p>
                </div>
            </div>

            <p class="mb-4">{{ $note->note_content }}</p>
            <p>Created {{ $note->note_created_at->toIso8601String() }}</p>

            <div class="d-flex gap-2">
                <a class="btn btn-warning" href="{{ route('notes.edit', $note) }}">
                    <i class="bi bi-pencil-square me-1" aria-hidden="true"></i>
                    Edit
                </a>
                <form method="POST" action="{{ route('notes.destroy', $note) }}">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-danger" type="submit">
                        <i class="bi bi-trash me-1" aria-hidden="true"></i>
                        Delete
                    </button>
                </form>
            </div>
        </div>
    </article>
@endsection
