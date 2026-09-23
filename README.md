# ContentU PED

Tool di gestione social e piano editoriale multi-cliente per ContentU.

## Stack
Laravel 13 · React 19 · Inertia 3 · Tailwind 4 · shadcn/ui · MySQL · Pest 4

## Avvio in locale
    ./vendor/bin/sail up -d
    ./vendor/bin/sail artisan migrate --seed
    ./vendor/bin/sail npm run dev

App: http://localhost · Email di test (Mailpit): http://localhost:8025

## Test
    ./vendor/bin/sail pest                          # tutta la suite
    ./vendor/bin/sail pest tests/Browser            # solo i test browser

## Design system
**Tutti** i colori, i font, le spaziature e i raggi stanno in `resources/css/app.css`.
Per cambiare il tema si modifica solo quel file. Non esiste `tailwind.config.js`:
Tailwind 4 è CSS-first. I colori hardcoded nei componenti sono bloccati da
`tests/Feature/DesignSystemTest.php`.

## Variabili d'ambiente specifiche
Vedi `.env.example`: soglie alert (`PED_ALERT_*`), carico shooting (`PED_SHOOTING_*`),
dimensione massima del logo cliente (`PED_LOGO_MAX_KB`), credenziali del seed (`SEED_ADMIN_*`).

## Task schedulato
`ped:check-alerts` gira ogni giorno alle 07:00 (`routes/console.php`).
In produzione serve il cron di Laravel: vedi https://laravel.com/docs/13.x/scheduling

## Funzionalità volutamente FUORI dalla versione 1
Non sono dimenticanze. Vedi `PIANO_SVILUPPO_PED.md` §4 per l'elenco completo e le motivazioni:
pubblicazione diretta sui social via API, analytics di engagement, fatturazione,
integrazione OAuth Drive/Dropbox, upload diretto file, versionamento contenuti,
vista calendario mese/settimana, export PDF e iCal, report automatico,
thread di risposta ai commenti, log attività, log accessi ai link pubblici.

## Limite noto sui test browser multi-step
Il plugin browser di Pest esegue le richieste reali in un server Amp che gira
nello stesso processo/container Laravel del test: lo stato di autenticazione
può "incollarsi" fra una `visit()`/`navigate()` e la successiva in modo non
deterministico, verificato anche su database appena migrato. Per questo
`tests/Browser/FullJourneyTest.php` copre solo il percorso admin (una sola
identità, una sessione continua); il resto del percorso reale (registrazione
cliente, risposta agli argomenti, rifiuto contenuto, visibilità ridotta dello
shooting) è coperto in modo affidabile dalla suite Feature via HTTP
(`PrivacyTest`, `PayloadAuditTest`, `ShootingTest`, `PublicPortalTest`,
`TopicPreviewTest`). Dettagli nel commento in cima al file del test.
