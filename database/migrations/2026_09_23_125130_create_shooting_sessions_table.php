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
        Schema::create('shooting_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();

            $table->date('session_date');
            $table->string('type', 16);                 // photo | video | photo_video

            // Alternative ancora da decidere, es. "video: Giorgio o Veronica".
            $table->boolean('is_tentative')->default(false);

            // Sessioni "eventuali" a capacità elastica: producibili solo dopo un checkpoint
            // (es. verifica della library esistente). Non calendarizzate di default.
            $table->boolean('checkpoint_required')->default(false);
            $table->text('checkpoint_note')->nullable();

            // Nota interna/criticità: MAI esposta al cliente (decisione B).
            $table->text('internal_note')->nullable();

            $table->timestamps();

            $table->index(['client_id', 'session_date']);
            $table->index('session_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shooting_sessions');
    }
};
