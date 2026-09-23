<?php

namespace Database\Seeders;

use App\Models\ContentType;
use Illuminate\Database\Seeder;

class ContentTypeSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * Questo seed è solo un punto di partenza, non un vincolo: l'admin
     * ne aggiunge altre dalla UI (05.3) senza intervento di sviluppo.
     */
    public function run(): void
    {
        $types = [
            ['post', 'Post singolo', false],
            ['carousel', 'Carosello', false],
            ['reel', 'Reel', true],   // video + cover
            ['story', 'Story', false],
            ['video', 'Video lungo', true],
            ['live', 'Diretta', false],
            ['graphic', 'Grafica', false],
        ];

        foreach ($types as $i => [$key, $label, $secondary]) {
            ContentType::updateOrCreate(['key' => $key], [
                'label' => $label,
                'requires_secondary_asset' => $secondary,
                'is_active' => true,
                'sort_order' => $i,
            ]);
        }
    }
}
