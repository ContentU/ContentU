<?php

namespace App\Enums;

enum ClientStatus: string
{
    case Active = 'active';
    case Paused = 'paused';
    case Archived = 'archived';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Attivo',
            self::Paused => 'In pausa',
            self::Archived => 'Archiviato',
        };
    }
}
