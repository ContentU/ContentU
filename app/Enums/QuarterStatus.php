<?php

namespace App\Enums;

enum QuarterStatus: string
{
    case Draft = 'draft';
    case InReview = 'in_review';
    case Approved = 'approved';
    case Closed = 'closed';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Bozza',
            self::InReview => 'In approvazione',
            self::Approved => 'Approvato',
            self::Closed => 'Concluso',
        };
    }

    /**
     * Stati raggiungibili da questo stato. Nessun salto consentito.
     *
     * @return list<self>
     */
    public function allowedNext(): array
    {
        return match ($this) {
            self::Draft => [self::InReview],
            self::InReview => [self::Approved, self::Draft],   // può tornare indietro per rework
            self::Approved => [self::Closed, self::InReview],
            self::Closed => [],
        };
    }

    public function canTransitionTo(self $target): bool
    {
        return in_array($target, $this->allowedNext(), true);
    }
}
