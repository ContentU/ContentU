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
        Schema::create('quarters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('year');
            $table->unsignedTinyInteger('quarter_number');     // 1-4

            // Memorizzata, non ricalcolata nel front-end a ogni render.
            $table->string('label', 16);                       // "Q4 2026"

            $table->string('status', 16)->default('draft');
            $table->date('starts_on');
            $table->date('ends_on');
            $table->timestamps();

            $table->unique(['client_id', 'year', 'quarter_number']);
            $table->index(['client_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quarters');
    }
};
