<?php

namespace App\Enums;

enum AlertType: string
{
    case MissingResource = 'missing_resource';
    case MissingCaptionTag = 'missing_caption_tag';
    case RunningOutContent = 'running_out_content';
    case NextQuarterMissing = 'next_quarter_missing';
    case ShootingOverload = 'shooting_overload';   // Fase 12

    public function label(): string
    {
        return match ($this) {
            self::MissingResource => 'Risorsa mancante',
            self::MissingCaptionTag => 'Caption o tag mancanti',
            self::RunningOutContent => 'Contenuti in esaurimento',
            self::NextQuarterMissing => 'Trimestre successivo non pianificato',
            self::ShootingOverload => 'Carico shooting oltre il tetto',
        };
    }
}
