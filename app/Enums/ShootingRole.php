<?php

namespace App\Enums;

enum ShootingRole: string
{
    case Photo = 'photo';         // F
    case Video = 'video';         // V
    case Coordination = 'coordination';  // C

    public function label(): string
    {
        return match ($this) {
            self::Photo => 'Foto',
            self::Video => 'Video',
            self::Coordination => 'Coordinamento',
        };
    }

    public function initial(): string
    {
        return match ($this) {
            self::Photo => 'F', self::Video => 'V', self::Coordination => 'C',
        };
    }
}
