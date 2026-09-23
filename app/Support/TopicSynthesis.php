<?php

namespace App\Support;

use App\Models\Quarter;
use Illuminate\Support\Collection;

class TopicSynthesis
{
    /**
     * I temi ricorrenti del trimestre, dal più frequente.
     * Il "tema" di un item è già il filone: si normalizza e si raggruppa.
     */
    public static function recurringThemes(Quarter $quarter, int $limit = 6): Collection
    {
        return $quarter->topicPreviews()
            ->with('items:id,topic_preview_id,theme')
            ->get()
            ->flatMap->items
            ->map(fn ($item) => self::normalize($item->theme))
            ->filter()
            ->countBy()
            ->sortDesc()
            ->take($limit)
            ->map(fn ($count, $theme) => ['theme' => $theme, 'count' => $count])
            ->values();
    }

    /** "Mango — nettari, confetture e trasformati" → "Mango" */
    private static function normalize(string $theme): string
    {
        $head = preg_split('/\s+[—–-]\s+/u', trim($theme))[0] ?? $theme;

        return ucfirst(mb_strtolower(trim($head)));
    }

    /** Contenuti che il cliente deve produrre: gli item il cui formato è un video/reel. */
    public static function materialToProduce(Quarter $quarter): Collection
    {
        return $quarter->topicPreviews()
            ->with('items')
            ->orderBy('month_order')
            ->get()
            ->flatMap(fn ($preview) => $preview->items
                ->filter(fn ($item) => str_contains(mb_strtolower($item->format_label), 'reel')
                    || str_contains(mb_strtolower($item->format_label), 'video'))
                ->map(fn ($item) => [
                    'month' => $preview->month_label,
                    'title' => $item->title,
                ]));
    }
}
