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
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('brand_name')->nullable();

            // Stringa + validazione applicativa, non un ENUM MySQL.
            $table->string('status', 16)->default('active');   // active | paused | archived
            $table->timestamp('paused_at')->nullable();

            // Referenti multipli: [{name, email, phone, role}, …]
            $table->json('contacts')->nullable();

            $table->string('logo_path')->nullable();
            $table->json('brand_colors')->nullable();
            $table->text('tone_of_voice')->nullable();
            $table->text('internal_notes')->nullable();

            // Eccezioni di pianificazione shooting per questo cliente (Fase 12, nota libera).
            $table->text('shooting_notes')->nullable();

            $table->timestamps();

            // Soft delete: archiviazione definitiva, distinta dallo status "archived".
            // status=archived → archiviato ma visibile in UI con filtro.
            // deleted_at      → fuori da tutte le liste, ripristinabile solo esplicitamente.
            $table->softDeletes();

            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
