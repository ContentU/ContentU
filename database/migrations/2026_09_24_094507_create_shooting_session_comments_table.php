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
        Schema::create('shooting_session_comments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('shooting_session_id')->constrained()->cascadeOnDelete();
            $table->foreignId('author_id')->constrained('users')->cascadeOnDelete();

            // Denormalizzato, come comments.author_role: "cliente" vs "team".
            $table->string('author_role', 16);

            $table->text('body');
            $table->timestamps();

            $table->index(['shooting_session_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shooting_session_comments');
    }
};
