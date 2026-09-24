<?php

namespace App\Support;

use App\Models\Client;
use App\Models\Quarter;
use App\Models\TopicPreviewItem;
use Illuminate\Support\Facades\Storage;

/**
 * Compone il prompt in italiano da incollare in Claude per creare l'artifact
 * condiviso ("Claude + link", vedi piano Fase 03): niente API a pagamento,
 * l'admin copia il testo e incolla il link dell'artifact pubblicato.
 *
 * Non include MAI internal_notes: il testo finisce, tramite l'artifact,
 * sotto gli occhi del cliente.
 */
class ArtifactPromptBuilder
{
    public static function forTopics(Client $client, ?Quarter $quarter): string
    {
        $lines = [];

        $lines[] = ArtifactPromptRules::current();
        $lines[] = '';
        $lines[] = self::brandBlock($client);

        if ($quarter === null) {
            $lines[] = '';
            $lines[] = 'Nessun trimestre pianificato per questo cliente al momento.';
        } else {
            $lines[] = '';
            $lines[] = "Trimestre: {$quarter->label}";

            $months = $quarter->topicPreviews()->with('items')->orderBy('month_order')->get();

            foreach ($months as $month) {
                $lines[] = '';
                $lines[] = "## {$month->month_label}";

                if (filled($month->note)) {
                    $lines[] = "Nota del mese: {$month->note}";
                }

                foreach ($month->items as $item) {
                    $lines[] = self::topicItemLine($item);
                }
            }
        }

        $lines[] = '';
        $lines[] = 'Crea un artifact HTML condivisibile seguendo esattamente queste regole.';

        return implode("\n", $lines);
    }

    public static function forShooting(Client $client): string
    {
        $lines = [];

        $lines[] = ArtifactPromptRules::current();
        $lines[] = '';
        $lines[] = self::brandBlock($client);

        if (filled($client->shooting_notes)) {
            $lines[] = '';
            $lines[] = "Note shooting: {$client->shooting_notes}";
        }

        $quarter = $client->currentOrLatestQuarter();

        if ($quarter !== null) {
            $targets = $client->shootingTargets()->where('quarter_id', $quarter->id)->get();

            if ($targets->isNotEmpty()) {
                $lines[] = '';
                $lines[] = '## Target dello shooting per il trimestre';

                foreach ($targets as $target) {
                    $lines[] = "- {$target->period_label}: ideali {$target->ideal_sessions}, pianificate {$target->planned_sessions}, potenziali {$target->potential_sessions}";
                }
            }
        }

        $sessions = $client->shootingSessions()
            ->whereDate('session_date', '>=', today())
            ->orderBy('session_date')
            ->get();

        if ($sessions->isNotEmpty()) {
            $lines[] = '';
            $lines[] = '## Sessioni future';

            foreach ($sessions as $session) {
                // Mai internal_note: è a uso interno del team, non del cliente.
                $lines[] = "- {$session->session_date->translatedFormat('D j M Y')}: {$session->type->label()}";
            }
        }

        $lines[] = '';
        $lines[] = 'Crea un artifact HTML condivisibile seguendo esattamente queste regole.';

        return implode("\n", $lines);
    }

    private static function brandBlock(Client $client): string
    {
        $colors = collect($client->brand_colors ?? [])->implode(', ') ?: 'nessuno specificato';
        $tone = $client->tone_of_voice ?: 'nessuno specificato';
        $logoUrl = $client->logo_path ? url(Storage::disk('public')->url($client->logo_path)) : 'nessun logo caricato';

        return implode("\n", [
            "Brand: {$client->name}".($client->brand_name ? " ({$client->brand_name})" : ''),
            "Colori brand: {$colors}",
            "Tone of voice: {$tone}",
            "Logo (URL assoluto): {$logoUrl}",
        ]);
    }

    private static function topicItemLine(TopicPreviewItem $item): string
    {
        $parts = array_filter([
            $item->format_label,
            $item->period_label,
            $item->title,
            $item->theme,
        ]);

        $line = '- '.implode(' · ', $parts);

        if (filled($item->objective)) {
            $line .= " — Obiettivo — {$item->objective}";
        }

        if (filled($item->footnote)) {
            $line .= " ({$item->footnote})";
        }

        return $line;
    }
}
