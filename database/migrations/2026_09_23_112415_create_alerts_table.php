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
        Schema::create('alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();

            $table->string('type', 48);          // vedi AlertType
            $table->morphs('subject');           // Content | Quarter | User
            $table->string('message');

            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            // Evita di ricreare lo stesso alert ogni giorno per lo stesso soggetto.
            $table->index(['client_id', 'type', 'resolved_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alerts');
    }
};
