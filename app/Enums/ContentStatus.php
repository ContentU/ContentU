<?php

namespace App\Enums;

enum ContentStatus: string
{
    case Draft = 'draft';
    case InReview = 'in_review';
    case Approved = 'approved';
    case Scheduled = 'scheduled';
    case Published = 'published';
    case NeedsChanges = 'needs_changes';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Bozza',
            self::InReview => 'In revisione',
            self::Approved => 'Approvato',
            self::Scheduled => 'Programmato',
            self::Published => 'Pubblicato',
            self::NeedsChanges => 'Richiesta modifica',
        };
    }

    public function allowedNext(): array
    {
        return match ($this) {
            self::Draft => [self::InReview],
            self::InReview => [self::Approved, self::NeedsChanges, self::Draft],
            self::Approved => [self::Scheduled, self::NeedsChanges],
            self::Scheduled => [self::Published, self::NeedsChanges],
            self::Published => [],
            // Da "richiesta modifica" si torna in BOZZA, mai direttamente ad approvato:
            // il team riprende in mano il contenuto e rifà il giro.
            self::NeedsChanges => [self::Draft],
        };
    }

    public function canTransitionTo(self $target): bool
    {
        return in_array($target, $this->allowedNext(), true);
    }
}
