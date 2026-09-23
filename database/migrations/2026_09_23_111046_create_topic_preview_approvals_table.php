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
        Schema::create('topic_preview_approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('topic_preview_id')->constrained()->cascadeOnDelete();

            // approved | approved_with_notes | revise
            // Stesso pattern del pannello "stato di approvazione" del documento PED.
            $table->string('status', 32);

            $table->text('comment')->nullable();
            $table->foreignId('responded_by')->constrained('users');
            $table->timestamp('responded_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('topic_preview_approvals');
    }
};
