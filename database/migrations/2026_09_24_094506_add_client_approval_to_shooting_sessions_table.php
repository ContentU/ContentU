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
        Schema::table('shooting_sessions', function (Blueprint $table) {
            $table->timestamp('client_approved_at')->nullable()->after('internal_note');
            $table->foreignId('client_approved_by')->nullable()->after('client_approved_at')
                ->constrained('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shooting_sessions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('client_approved_by');
            $table->dropColumn('client_approved_at');
        });
    }
};
