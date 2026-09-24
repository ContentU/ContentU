<?php

use App\Models\User;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Usato da docker/start.sh: sui piani senza Shell (Render free) il primo seed
// parte all'avvio, ma solo su database vuoto, così non reimposta la password
// dell'admin né i tipi di contenuto modificati dalla UI a ogni riavvio.
Artisan::command('ped:seed-if-empty', function () {
    if (User::query()->exists()) {
        $this->info('Utenti già presenti: seed saltato.');

        return;
    }

    $this->call('db:seed', ['--force' => true]);
})->purpose('Esegue il seed iniziale solo se non esistono utenti');

Schedule::command('ped:check-alerts')->dailyAt('07:00');
