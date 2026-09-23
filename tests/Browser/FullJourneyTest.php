<?php

use App\Models\Client;
use App\Models\ClientPublicLink;
use App\Models\User;

/**
 * NOTA IMPORTANTE su questo file (letta prima di modificarlo):
 *
 * Il piano (13.2) chiede un unico test che attraversi l'intero percorso
 * alternando admin e cliente nella stessa sessione browser. Si è rivelato
 * NON realizzabile in modo affidabile con l'attuale plugin browser di Pest:
 * ogni richiesta reale viene gestita da un server Amp che gira nello stesso
 * processo/container Laravel di tutto il test, condividendo lo stato di
 * autenticazione/sessione fra "pagine" diverse in modo non deterministico
 * (verificato empiricamente e riproducibile anche su database appena
 * migrato: dopo un secondo `visit()`/cambio di identità, la pagina può
 * mostrare un utente admin sbagliato o un contenuto sbagliato, pur avendo
 * verificato che il database contiene esattamente i dati attesi).
 * `actingAs()`, un logout reale via UI e `forgetGuards()` non risolvono il
 * problema; solo `refreshApplication()` lo risolve, ma rompe la transazione
 * di RefreshDatabase e i dati creati nel test non sono più visibili.
 *
 * La UI reale è stata verificata manualmente in modo estensivo in questa
 * sessione (screenshot Playwright reali, fuori da Pest) su dashboard,
 * shooting, gestione utenti, portale cliente: tutto funziona correttamente.
 * Qui restano quindi solo i passaggi ammin-only dimostrabilmente affidabili
 * in un'unica sessione browser continua. La copertura del resto del
 * percorso (registrazione cliente, risposta agli argomenti, rifiuto di un
 * contenuto, visibilità ridotta dello shooting) è garantita dalla suite
 * Feature (PrivacyTest, ShootingTest, PublicPortalTest, TopicPreviewTest,
 * ContentTest), che esercita le stesse rotte e li stessi controlli via HTTP
 * in modo affidabile.
 */
it('percorso admin: crea un cliente, un trimestre e genera il link pubblico', function () {
    $admin = User::factory()->admin()->create(['password' => bcrypt('secret123')]);

    $page = visit('/login');

    $page->type('email', $admin->email)
        ->type('password', 'secret123')
        ->click('button[type="submit"]')
        ->assertPathIs('/dashboard');

    $page->click('a[href="/clients/create"]')
        ->type('name', 'Canalotto Farm')
        ->click('button[type="submit"]')
        ->assertSee('Canalotto Farm');

    $client = Client::firstWhere('name', 'Canalotto Farm');

    $page->navigate("/clients/{$client->id}/quarters")
        ->type('quarter_number', '4')
        ->type('year', '2026')
        ->click('Aggiungi trimestre')
        ->assertSee('Q4 2026');

    $page->navigate("/clients/{$client->id}/edit")
        ->click('Genera link')
        ->assertNoJavascriptErrors();

    expect(ClientPublicLink::where('client_id', $client->id)->exists())->toBeTrue();
});
