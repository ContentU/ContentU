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
        Schema::create('topic_preview_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('topic_preview_id')->constrained()->cascadeOnDelete();

            // Riusa content_types invece di duplicare l'elenco dei formati.
            // Nullable perché il formato in fase di proposta può essere ancora
            // sfumato ("Foto / Reel*"): in quel caso vale format_label.
            $table->foreignId('content_type_id')->nullable()->constrained()->nullOnDelete();
            $table->string('format_label', 64);      // "Carosello fotografico", "Foto / Reel*"

            $table->string('period_label', 48);      // "Inizio ottobre", "Prima metà", "Fine ottobre"
            $table->string('title');                 // "Sta arrivando la guava siciliana"
            $table->string('theme');                 // "Mango — nettari, confetture e trasformati"
            $table->string('objective');             // "creare attesa sulla disponibilità di ottobre"
            $table->text('footnote')->nullable();

            $table->unsignedSmallInteger('sort_order')->default(0);

            // Collegamento A POSTERIORI al contenuto reale, quando il team
            // trasforma il tema approvato in un contenuto (Fase 05).
            // NON è un vincolo: un item NON è un Content.
            $table->foreignId('content_id')->nullable()->constrained()->nullOnDelete();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('topic_preview_items');
    }
};
