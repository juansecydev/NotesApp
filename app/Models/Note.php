<?php

namespace App\Models;

use App\Enums\NoteColor;
use Database\Factories\NoteFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Represents a note owned by a user.
 */
#[Fillable(['note_user_id', 'note_color', 'note_title', 'note_content'])]
#[Table(dateFormat: 'Y-m-d\TH:i:sP')]
class Note extends Model
{
    /** @use HasFactory<NoteFactory> */
    use HasFactory;

    /**
     * The primary key column for the notes table.
     *
     * @var string
     */
    protected $primaryKey = 'note_id';

    /**
     * The default color assigned to new notes before persistence.
     *
     * @var array<string, string>
     */
    protected $attributes = [
        'note_color' => 'primary',
    ];

    /**
     * The column used to store the creation timestamp.
     */
    public const CREATED_AT = 'note_created_at';

    /**
     * The column used to store the update timestamp.
     */
    public const UPDATED_AT = 'note_updated_at';

    /**
     * Return the user who owns this note.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'note_user_id', 'user_id');
    }

    /**
     * Return a valid lowercase color keyword, falling back for invalid stored values.
     *
     * @param  mixed  $value  The raw database value.
     * @return string A supported lowercase Bootstrap color keyword.
     */
    public function getNoteColorAttribute(mixed $value): string
    {
        return NoteColor::normalize($value);
    }

    /**
     * Normalize colors before they are persisted.
     *
     * @param  mixed  $value  The submitted color value.
     */
    public function setNoteColorAttribute(mixed $value): void
    {
        $this->attributes['note_color'] = NoteColor::normalize($value);
    }

    /**
     * Get the attributes that should be cast to native types.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'note_created_at' => 'datetime',
            'note_updated_at' => 'datetime',
        ];
    }
}
