<?php

namespace App\Console\Commands;

use App\Enums\AlertType;
use App\Models\Alert;
use App\Models\Client;
use App\Models\Content;
use App\Models\Quarter;
use App\Models\User;
use App\Notifications\AlertRaised;
use App\Support\WorkloadCalculator;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Notification;

#[Signature('ped:check-alerts')]
#[Description('Verifica le condizioni di alert su tutti i clienti attivi')]
class CheckPedAlerts extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $days = config('ped.alerts.missing_resource_days');
        $quarterDays = config('ped.alerts.quarter_ending_days');
        $raised = 0;

        Client::where('status', 'active')->with('quarters')->each(function (Client $client) use ($days, $quarterDays, &$raised) {

            // 1 — Contenuto in programma entro N giorni senza risorsa caricata.
            $client->quarters->each(function (Quarter $quarter) use ($client, $days, &$raised) {
                $quarter->contents()
                    ->whereNull('resource_url')
                    ->whereNotIn('status', ['published'])
                    ->whereBetween('publish_at', [now(), now()->addDays($days)])
                    ->each(function (Content $content) use ($client, &$raised) {
                        $raised += $this->raise(
                            $client, AlertType::MissingResource, $content,
                            "«{$content->title}» è in programma per il "
                                .$content->publish_at->format('j M').' e non ha ancora la risorsa caricata.',
                        );
                    });
            });

            // 2 — Contenuto fuori da bozza senza caption o senza tag.
            $client->quarters->each(function (Quarter $quarter) use ($client, &$raised) {
                $quarter->contents()
                    ->where('status', '!=', 'draft')
                    ->where(fn ($q) => $q->whereNull('caption')->orWhereDoesntHave('tags'))
                    ->each(function (Content $content) use ($client, &$raised) {
                        $manca = blank($content->caption) ? 'la caption' : 'i tag interni';

                        $raised += $this->raise(
                            $client, AlertType::MissingCaptionTag, $content,
                            "«{$content->title}» è uscito da bozza ma manca {$manca}.",
                        );
                    });
            });

            // 3 — Contenuti in esaurimento: ultimo contenuto programmato vicino.
            $lastPlanned = Content::whereHas('quarter', fn ($q) => $q->where('client_id', $client->id))
                ->whereIn('status', ['approved', 'scheduled'])
                ->max('publish_at');

            if ($lastPlanned !== null
                && Carbon::parse($lastPlanned)->lte(now()->addDays($days))) {
                $current = $client->quarters()->current()->first();

                if ($current) {
                    $raised += $this->raise(
                        $client, AlertType::RunningOutContent, $current,
                        "L'ultimo contenuto pianificato per {$client->name} è il "
                            .Carbon::parse($lastPlanned)->format('j M').': il PED sta finendo.',
                    );
                }
            }

            // 4 — Trimestre in scadenza senza il successivo almeno in bozza.
            $client->quarters()
                ->whereIn('status', ['approved', 'closed'])
                ->whereBetween('ends_on', [today(), today()->addDays($quarterDays)])
                ->each(function (Quarter $quarter) use ($client, &$raised) {
                    $nextYear = $quarter->quarter_number === 4 ? $quarter->year + 1 : $quarter->year;
                    $nextNumber = $quarter->quarter_number === 4 ? 1 : $quarter->quarter_number + 1;

                    $hasNext = $client->quarters()
                        ->where('year', $nextYear)
                        ->where('quarter_number', $nextNumber)
                        ->exists();

                    if (! $hasNext) {
                        $raised += $this->raise(
                            $client, AlertType::NextQuarterMissing, $quarter,
                            "{$quarter->label} chiude il ".$quarter->ends_on->format('j M')
                                .", ma Q{$nextNumber} {$nextYear} non è ancora pianificato.",
                        );
                    }
                });
        });

        // 5 — Sovraccarico shooting (Fase 12, priorità S).
        $max = config('ped.shooting.max_days_per_month');

        foreach ([CarbonImmutable::now(), CarbonImmutable::now()->addMonth()] as $month) {
            WorkloadCalculator::forMonth($month)
                ->filter(fn ($row) => $row['days'] > $max)
                ->each(function ($row) use ($month, &$raised, $max) {
                    $user = User::find($row['userId']);

                    // L'alert non è legato a un cliente specifico: si usa il primo cliente
                    // attivo come ancoraggio, oppure si salta se non ce ne sono.
                    $client = Client::where('status', 'active')->first();

                    if ($client) {
                        $raised += Alert::raise(
                            $client, AlertType::ShootingOverload, $user,
                            "{$user->name} ha {$row['days']} giornate di shooting a "
                                .$month->translatedFormat('F Y').", oltre il tetto di {$max}.",
                        ) ? 1 : 0;
                    }
                });
        }

        // Chiude gli alert la cui condizione non è più vera.
        $this->resolveFixedAlerts();

        $this->info("Alert generati: {$raised}");

        return self::SUCCESS;
    }

    /** Crea l'alert se nuovo e avvisa il team del cliente. Un solo posto per questa coppia di azioni. */
    private function raise(Client $client, AlertType $type, Model $subject, string $message): int
    {
        $alert = Alert::raise($client, $type, $subject, $message);

        if ($alert === null) {
            return 0;
        }

        Notification::send($client->teamMembers, new AlertRaised($alert));

        return 1;
    }

    /** Chiude gli alert la cui condizione originaria non è più vera. */
    private function resolveFixedAlerts(): void
    {
        Alert::open()->where('type', AlertType::MissingResource->value)
            ->with('subject')
            ->each(function (Alert $alert) {
                if ($alert->subject instanceof Content && filled($alert->subject->resource_url)) {
                    $alert->update(['resolved_at' => now()]);
                }
            });

        Alert::open()->where('type', AlertType::MissingCaptionTag->value)
            ->with('subject')
            ->each(function (Alert $alert) {
                $content = $alert->subject;

                if ($content instanceof Content && filled($content->caption) && $content->tags()->exists()) {
                    $alert->update(['resolved_at' => now()]);
                }
            });

        Alert::open()->where('type', AlertType::NextQuarterMissing->value)
            ->with('subject')
            ->each(function (Alert $alert) {
                $quarter = $alert->subject;

                if (! $quarter instanceof Quarter) {
                    return;
                }

                $nextYear = $quarter->quarter_number === 4 ? $quarter->year + 1 : $quarter->year;
                $nextNumber = $quarter->quarter_number === 4 ? 1 : $quarter->quarter_number + 1;

                $hasNext = $quarter->client->quarters()
                    ->where('year', $nextYear)
                    ->where('quarter_number', $nextNumber)
                    ->exists();

                if ($hasNext) {
                    $alert->update(['resolved_at' => now()]);
                }
            });
    }
}
