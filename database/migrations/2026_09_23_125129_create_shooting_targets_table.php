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
        Schema::create('shooting_targets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();

            // Per TRIMESTRE, non per semestre: decisione D.
            $table->foreignId('quarter_id')->constrained()->cascadeOnDelete();
            $table->string('period_label', 16);     // copia di quarters.label, es. "Q4 2026"

            // L'esempio reale: "Centro Mega: 4 → 3 + 1 potenziale"
            $table->unsignedTinyInteger('ideal_sessions');
            $table->unsignedTinyInteger('planned_sessions')->default(0);
            $table->unsignedTinyInteger('potential_sessions')->default(0);

            $table->string('weight', 4)->default('M');   // S | M | L — peso/priorità del cliente
            $table->text('status_note')->nullable();

            $table->timestamps();

            $table->unique(['client_id', 'quarter_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shooting_targets');
    }
};
