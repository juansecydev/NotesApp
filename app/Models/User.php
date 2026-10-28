<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * Notes for me about models and attributes
 * Fillable: You must specify which attributes can be filled via requests to prevent massive assigament attack.
 * Hidden: You must specify which attributes should be hidden when the model is serialized to an array or JSON.
 * Table: You can specify the date format for the model's timestamps. The default is 'Y-m-d H:i:s'.
*/

/**
 * Represents a user account and its owned notes.
 */
#[Fillable(['user_name', 'user_email', 'user_password', 'user_pfp'])]
#[Hidden(['user_password', 'remember_token'])]
#[Table(dateFormat: 'Y-m-d\TH:i:sP')]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The database column used to store the user's password hash.
     *
     * @var string
     */
    protected $authPasswordName = 'user_password';

    /**
     * The primary key column for the users table.
     *
     * @var string
     */
    protected $primaryKey = 'user_id';

    /**
     * The column used to store the creation timestamp.
     */
    public const CREATED_AT = 'user_created_at';

    /**
     * The column used to store the update timestamp.
     */
    public const UPDATED_AT = 'user_updated_at';

    /**
     * Return the notes that belong to this user.
     *
     * @return HasMany<Note, $this>
     */
    public function notes(): HasMany
    {
        return $this->hasMany(Note::class, 'note_user_id', 'user_id');
    }

    /**
     * Get the attributes that should be cast to native types.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'user_password' => 'hashed',
            'user_created_at' => 'datetime',
            'user_updated_at' => 'datetime',
        ];
    }
}
