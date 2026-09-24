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
        Schema::table('clients', function (Blueprint $table) {
            $table->string('topics_artifact_url', 2048)->nullable()->after('shooting_notes');
            $table->string('shooting_artifact_url', 2048)->nullable()->after('topics_artifact_url');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn(['topics_artifact_url', 'shooting_artifact_url']);
        });
    }
};
