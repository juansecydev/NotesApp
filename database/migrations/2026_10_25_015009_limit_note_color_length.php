<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Limit note colors to the longest supported plain color name.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('notes', function (Blueprint $table): void {
            $table->string('note_color', 9)->default('primary')->change();
        });
    }

    /**
     * Restore the previous ten-character note color column.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('notes', function (Blueprint $table): void {
            $table->string('note_color', 10)->default('primary')->change();
        });
    }
};
