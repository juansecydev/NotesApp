@extends('layouts.app')

@section('title', $isEditing ? 'Edit note' : 'Create note')

@section('content')
    <h1 class="mb-4">{{ $isEditing ? 'Edit note' : 'Create note' }}</h1>

    <form method="POST" action="{{ $isEditing ? route('notes.update', $note) : route('notes.store') }}" class="vstack gap-3">
        @csrf
        @if ($isEditing)
            @method('PUT')
        @endif

        <div>
            <label class="form-label" for="note_title">Title</label>
            <input class="form-control" id="note_title" name="note_title" value="{{ old('note_title', $note->note_title) }}" maxlength="30" required>
            @error('note_title')<div class="text-danger">{{ $message }}</div>@enderror
        </div>

        <div>
            <label class="form-label" for="note_content">Content</label>
            <textarea class="form-control" id="note_content" name="note_content" maxlength="300" rows="5" required>{{ old('note_content', $note->note_content) }}</textarea>
            @error('note_content')<div class="text-danger">{{ $message }}</div>@enderror
        </div>

        <div>
            <label class="form-label" for="note_color">Card color</label>
            <select class="form-select" id="note_color" name="note_color" required>
                @foreach (['primary' => 'Primary', 'secondary' => 'Secondary', 'success' => 'Success', 'danger' => 'Danger', 'warning' => 'Warning', 'info' => 'Info'] as $color => $label)
                    <option value="{{ $color }}" @selected(old('note_color', $note->note_color) === $color)>{{ $label }}</option>
                @endforeach
            </select>
            @error('note_color')<div class="text-danger">{{ $message }}</div>@enderror
        </div>

        <div>
            <p class="form-label mb-2">Color examples</p>
            <div class="note-color-examples" role="group" aria-label="Examples of available note colors">
                @foreach (['primary' => 'Primary', 'secondary' => 'Secondary', 'success' => 'Success', 'danger' => 'Danger', 'warning' => 'Warning', 'info' => 'Info'] as $color => $label)
                    <article class="note-color-example card">
                        <h2 class="card-header h6 mb-0 bg-{{ $color }} {{ in_array($color, ['warning', 'info'], true) ? 'text-dark' : 'text-white' }}">{{ $label }}</h2>
                        <div class="card-body">
                            <span class="small">Note preview</span>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>

        <div>
            <p class="form-label mb-2">Selected preview</p>
            <article class="card note-color-preview">
                <h2 class="card-header h5 mb-0 note-color-preview-title bg-{{ $note->note_color }} {{ in_array($note->note_color, ['warning', 'info'], true) ? 'text-dark' : 'text-white' }}" data-note-color-preview-title>
                    <span data-note-color-label>{{ ucfirst($note->note_color) }} note</span>
                </h2>
                <div class="card-body">
                    <p class="mb-0">Only the title uses the selected note color.</p>
                </div>
            </article>
        </div>

        <div class="d-flex gap-2">
            <button class="btn btn-primary" type="submit">{{ $isEditing ? 'Save changes' : 'Create note' }}</button>
            <a class="btn btn-outline-secondary" href="{{ route('notes.index') }}">Cancel</a>
        </div>
    </form>
@endsection
