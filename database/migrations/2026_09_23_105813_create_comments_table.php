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
        Schema::create('comments', function (Blueprint $table) {
            $table->id();

            // v1 commenta SOLO i contenuti. La pre-verifica ha la sua approvazione
            // complessiva (Fase 08) e non ha bisogno di commenti per riga.
            // NON costruire una relazione polimorfica preventivamente.
            $table->foreignId('content_id')->constrained()->cascadeOnDelete();

            $table->foreignId('author_id')->constrained('users')->cascadeOnDelete();

            // Denormalizzato per la visualizzazione: "cliente" vs "team".
            // Serve a etichettare il commento anche se in futuro il ruolo dell'utente cambia.
            $table->string('author_role', 16);

            $table->text('body');
            $table->timestamps();

            $table->index(['content_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comments');
    }
};
