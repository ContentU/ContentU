<?php

return [
    // Fase 03 — upload logo cliente
    'logo_max_kb' => env('PED_LOGO_MAX_KB', 2048),

    // Fase 10 — soglie alert
    'alerts' => [
        'missing_resource_days' => env('PED_ALERT_MISSING_RESOURCE_DAYS', 7),
        'quarter_ending_days' => env('PED_ALERT_QUARTER_ENDING_DAYS', 30),
    ],

    // Fase 12 — carico shooting
    'shooting' => [
        'target_days_per_month' => env('PED_SHOOTING_TARGET_DAYS', 3),
        'max_days_per_month' => env('PED_SHOOTING_MAX_DAYS', 4),
    ],

    // Fase 05 — canali disponibili per i contenuti
    'channels' => ['instagram', 'facebook', 'tiktok', 'linkedin'],
];
