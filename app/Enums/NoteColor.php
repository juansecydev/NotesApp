<?php

namespace App\Enums;

/**
 * Represents a supported Bootstrap background color for notes.
 */
enum NoteColor: string
{
    case Primary = 'primary';

    case Secondary = 'secondary';

    case Success = 'success';

    case Danger = 'danger';

    case Warning = 'warning';

    case Info = 'info';

    /**
     * Return a supported lowercase color, defaulting unsupported values to primary.
     *
     * @param  mixed  $value  The submitted or stored color value.
     * @return string A supported lowercase Bootstrap color keyword.
     */
    public static function normalize(mixed $value): string
    {
        if (! is_string($value)) {
            return self::Primary->value;
        }

        return self::tryFrom(strtolower($value))?->value ?? self::Primary->value;
    }
}
