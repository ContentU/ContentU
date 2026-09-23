<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->string('slug')->nullable()->after('name');
        });

        $this->backfillSlugs();

        Schema::table('clients', function (Blueprint $table) {
            $table->unique('slug');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->dropColumn('slug');
        });
    }

    /** Clienti già esistenti: slug derivato dal nome, con suffisso numerico in caso di collisione. */
    private function backfillSlugs(): void
    {
        $used = [];

        // DB::table ignora comunque il global scope SoftDeletes: include anche gli archiviati.
        DB::table('clients')->orderBy('id')->get(['id', 'name'])->each(function ($client) use (&$used) {
            $base = Str::slug($client->name) ?: 'client';
            $slug = $base;
            $suffix = 2;

            while (in_array($slug, $used, true)) {
                $slug = "{$base}-{$suffix}";
                $suffix++;
            }

            $used[] = $slug;

            DB::table('clients')->where('id', $client->id)->update(['slug' => $slug]);
        });
    }
};
