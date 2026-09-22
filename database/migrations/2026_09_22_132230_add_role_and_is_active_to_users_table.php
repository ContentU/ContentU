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
        Schema::table('users', function (Blueprint $table) {
            // Stringa + validazione applicativa, NON un ENUM MySQL:
            // aggiungere un ruolo in futuro non deve richiedere una migrazione distruttiva.
            // Nessun default: il ruolo va sempre specificato esplicitamente in creazione.
            $table->string('role', 32)->after('email');
            $table->boolean('is_active')->default(true)->after('role');
            $table->index('role');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['role']);
            $table->dropColumn(['role', 'is_active']);
        });
    }
};
