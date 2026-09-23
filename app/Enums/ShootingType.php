<?php

namespace App\Enums;

enum ShootingType: string
{
    case Photo = 'photo';
    case Video = 'video';
    case PhotoVideo = 'photo_video';

    public function label(): string
    {
        return match ($this) {
            self::Photo => 'Foto',
            self::Video => 'Video',
            self::PhotoVideo => 'Foto + Video',
        };
    }
}
