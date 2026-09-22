<?php

use Illuminate\Support\Facades\File;

function sourceFiles(): array
{
    return array_merge(
        File::allFiles(resource_path('js')),
        File::allFiles(resource_path('views')),
    );
}

it('non contiene colori esadecimali fuori da app.css', function () {
    $offenders = [];

    foreach (sourceFiles() as $file) {
        if (preg_match('/#[0-9a-fA-F]{3,8}\b/', $file->getContents(), $m)) {
            $offenders[] = $file->getRelativePathname().' → '.$m[0];
        }
    }

    expect($offenders)->toBeEmpty(
        "Colori esadecimali fuori da app.css:\n".implode("\n", $offenders)
    );
});

it('non usa la palette di default di Tailwind al posto dei token di brand', function () {
    // Colori di default che non appartengono alla palette ContentU.
    $banned = '/\b(?:bg|text|border|ring|fill|stroke)-(?:red|green|blue|yellow|orange|purple|pink|indigo|emerald|teal|cyan|sky|lime|amber|violet|fuchsia|rose)-\d{2,3}\b/';
    $offenders = [];

    foreach (sourceFiles() as $file) {
        if (preg_match($banned, $file->getContents(), $m)) {
            $offenders[] = $file->getRelativePathname().' → '.$m[0];
        }
    }

    expect($offenders)->toBeEmpty(
        "Usa i token di brand (bg-primary, text-brand-teal, …) invece di:\n".implode("\n", $offenders)
    );
});

it('non esiste un secondo file di configurazione dello stile', function () {
    expect(File::exists(base_path('tailwind.config.js')))->toBeFalse();
    expect(File::exists(base_path('tailwind.config.ts')))->toBeFalse();
    expect(File::get(resource_path('css/app.css')))->not->toContain('@config');
});
