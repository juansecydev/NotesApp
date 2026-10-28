<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Normalize existing note colors and set the database default to primary.
     */
    public function up(): void
    {
        DB::table('notes')->orderBy('note_id')->get(['note_id', 'note_color'])->each(
            static function (object $note): void {
                $color = strtolower((string) $note->note_color);

                if (str_starts_with($color, 'bg-')) {
                    $color = substr($color, 3);
                }

                if (! in_array($color, ['primary', 'secondary', 'success', 'danger', 'warning', 'info'], true)) {
                    $color = 'primary';
                }

                DB::table('notes')->where('note_id', $note->note_id)->update(['note_color' => $color]);
            },
        );

        Schema::table('notes', function (Blueprint $table): void {
            $table->string('note_color', 10)->default('primary')->change();
        });
    }

    /**
     * Restore the previous default and background-prefixed values.
     */
    public function down(): void
    {
        DB::table('notes')->orderBy('note_id')->get(['note_id', 'note_color'])->each(
            static function (object $note): void {
                DB::table('notes')->where('note_id', $note->note_id)->update([
                    'note_color' => 'bg-'.strtolower((string) $note->note_color),
                ]);
            },
        );

        Schema::table('notes', function (Blueprint $table): void {
            $table->string('note_color', 10)->default('bg-primary')->change();
        });
    }
};
