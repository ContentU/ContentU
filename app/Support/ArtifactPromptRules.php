<?php

namespace App\Support;

use App\Models\Setting;

/**
 * Regole globali del prompt per gli artifact Claude (Fase 02), modificabili
 * dall'admin nelle impostazioni. Salvate in `settings` sotto la chiave
 * `artifact.prompt_rules`, sullo stesso schema di `shooting.planning_rules`.
 */
class ArtifactPromptRules
{
    public const string SETTING_KEY = 'artifact.prompt_rules';

    public const string DEFAULT = <<<'TXT'
        Layout a sezioni:
        - intestazione con il logo del cliente e il logo Contentu, titolo "{Brand} Q{n} {anno}" e sottotitolo con i mesi del trimestre;
        - una sezione "Il senso del trimestre";
        - una sezione per ciascun mese, con i contenuti numerati (formato, periodo, titolo, tema, "Obiettivo — …");
        - una sezione "Cosa ci serve da voi";
        - una sezione "Materiali da produrre" (reel/foto).

        Colori: usa la palette del cliente come colore d'accento, testo scuro su fondo chiaro.
        Font: Google Fonts, leggibili.
        Layout responsive, adatto anche da smartphone.
        Non includere mai dati interni (note interne, tone of voice, costi, contatti).
        TXT;

    public static function current(): string
    {
        return Setting::get(self::SETTING_KEY) ?? self::DEFAULT;
    }

    public static function set(?string $value): void
    {
        Setting::set(self::SETTING_KEY, $value);
    }
}
