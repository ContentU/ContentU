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
        Schema::create('topic_previews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quarter_id')->constrained()->cascadeOnDelete();

            $table->string('month_label', 32);           // "Ottobre"
            $table->unsignedTinyInteger('month_order');  // ordinamento dentro il trimestre

            // ready = "Proposta pronta" · draft = "Roadmap da confermare"
            $table->string('status', 16)->default('draft');

            $table->text('note')->nullable();            // nota di contesto del mese
            $table->timestamps();

            $table->unique(['quarter_id', 'month_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('topic_previews');
    }
};
