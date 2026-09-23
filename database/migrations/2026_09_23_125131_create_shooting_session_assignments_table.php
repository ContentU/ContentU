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
        Schema::create('shooting_session_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shooting_session_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // I ruoli F / V / C del documento originale.
            $table->string('role', 16);        // photo | video | coordination

            // "Questa persona è un'alternativa possibile, non confermata".
            $table->boolean('is_alternative')->default(false);

            $table->timestamps();

            $table->unique(['shooting_session_id', 'user_id', 'role'], 'shooting_session_assignments_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shooting_session_assignments');
    }
};
