<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('notes', function (Blueprint $table): void {
            $table->increments('note_id');
            $table->unsignedInteger('note_user_id')->index();
            $table->string('note_color', 10)->default('primary');
            $table->string('note_title', 30);
            $table->string('note_content', 300);
            $table->timestamp('note_created_at');
            $table->timestamp('note_updated_at')->nullable();

            $table->foreign('note_user_id')
                ->references('user_id')
                ->on('users')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notes');
    }
};
