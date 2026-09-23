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
        Schema::create('contents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quarter_id')->constrained()->cascadeOnDelete();
            $table->foreignId('content_type_id')->constrained()->restrictOnDelete();

            $table->string('title');                          // titolo interno breve, non pubblicato
            $table->text('caption')->nullable();

            // Stringa libera con hashtag separati da spazio. NON normalizzata in v1:
            // i "set salvabili per cliente" sono S rimandata — non costruirli.
            $table->text('hashtags')->nullable();

            $table->string('resource_url')->nullable();       // link Drive/Dropbox
            $table->string('cover_resource_url')->nullable(); // asset secondario / cover

            $table->dateTime('publish_at');
            $table->string('status', 24)->default('draft');

            $table->json('channels')->nullable();             // ['instagram','facebook',…]

            $table->timestamps();

            $table->index(['quarter_id', 'publish_at']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contents');
    }
};
