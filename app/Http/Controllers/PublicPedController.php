<?php

namespace App\Http\Controllers;

use App\Enums\ContentStatus;
use App\Models\Client;
use App\Models\ClientPublicLink;
use App\Models\Content;
use App\Models\Quarter;
use App\Models\ShootingSession;
use App\Models\TopicPreview;
use App\Models\User;
use App\Notifications\ClientActionTaken;
use App\Notifications\TopicPreviewResponded;
use App\Support\PedInvite;
use App\Support\TopicSynthesis;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class PublicPedController extends Controller
{
    public function entry(Request $request, string $clientSlug): Response|RedirectResponse
    {
        $link = $this->link($request);
        $user = $request->user();

        if ($user !== null) {
            return to_route('ped.feed', $clientSlug);
        }

        return Inertia::render('public-ped/entry', [
            'client' => $this->clientPayload($link->client),
            'clientSlug' => $clientSlug,
            'mode' => null,
            'email' => null,
        ]);
    }

    public function checkEmail(Request $request, string $clientSlug): Response
    {
        $link = $this->link($request);

        $data = $request->validate([
            'email' => ['required', 'email', 'max:255'],
        ]);

        $user = $link->client->pedAccessUsers()->where('email', $data['email'])->first();

        $mode = match (true) {
            $user === null => 'not_allowed',
            ! $user->hasSetPassword() => 'pending_invite',
            default => 'login',
        };

        if ($mode === 'pending_invite') {
            $this->resendInvite($user, $link->client);
        }

        return Inertia::render('public-ped/entry', [
            'client' => $this->clientPayload($link->client),
            'clientSlug' => $clientSlug,
            'mode' => $mode,
            'email' => $data['email'],
        ]);
    }

    public function topics(Request $request, string $clientSlug): Response
    {
        $link = $this->link($request);
        $quarter = $this->currentQuarter($link->client);

        $months = $quarter
            ? $quarter->topicPreviews()->with('items')->orderBy('month_order')->get()
            : collect();

        return Inertia::render('public-ped/topic-preview', [
            'client' => $this->clientPayload($link->client),
            'clientSlug' => $clientSlug,
            'quarter' => $quarter ? ['label' => $quarter->label] : null,
            'months' => $months->map(fn (TopicPreview $preview) => [
                'id' => $preview->id,
                'label' => $preview->month_label,
                'count' => $preview->items->count(),
                'status' => $preview->status,
                'statusLabel' => $preview->statusLabel(),
                'note' => $preview->note,
                'approvalStatus' => $preview->latestApproval?->status,
                'items' => $preview->items->values()->map(fn ($item, $index) => [
                    'index' => $index + 1,
                    'formatLabel' => $item->format_label,
                    'periodLabel' => $item->period_label,
                    'title' => $item->title,
                    'theme' => $item->theme,
                    'objective' => $item->objective,
                    'footnote' => $item->footnote,
                ]),
            ]),
            'synthesis' => $quarter ? [
                'recurringThemes' => TopicSynthesis::recurringThemes($quarter),
                'materialToProduce' => TopicSynthesis::materialToProduce($quarter),
            ] : null,
        ]);
    }

    public function respondToTopics(Request $request, string $clientSlug, TopicPreview $topicPreview): RedirectResponse
    {
        $link = $this->link($request);

        abort_unless($topicPreview->quarter->client_id === $link->client_id, 404);
        abort_unless($request->user()->isClient(), 403);

        $data = $request->validate([
            'status' => ['required', 'in:approved,approved_with_notes,revise'],
            'comment' => ['nullable', 'string', 'max:5000', 'required_unless:status,approved'],
        ]);

        $topicPreview->approvals()->create([
            ...$data,
            'responded_by' => $request->user()->id,
            'responded_at' => now(),
        ]);

        Notification::send(
            $topicPreview->quarter->client->teamMembers,
            new TopicPreviewResponded($topicPreview, $data['status'], $data['comment'] ?? null),
        );

        Inertia::flash('message', 'Risposta inviata al team.');

        return back();
    }

    public function feed(Request $request, string $clientSlug): Response
    {
        $link = $this->link($request);
        $quarter = $this->currentQuarter($link->client);

        $contents = $quarter
            ? $quarter->contents()
                ->whereIn('status', $link->visibleStatuses())
                ->orderBy('publish_at')
                ->get()
                ->map(fn (Content $c) => $c->toFeedArray())
            : collect();

        return Inertia::render('public-ped/feed', [
            'client' => $this->clientPayload($link->client),
            'clientSlug' => $clientSlug,
            'quarter' => $quarter ? ['label' => $quarter->label] : null,
            'contents' => $contents,
            'actions' => [
                'canApprove' => true,
                'canReject' => true,
                'canComment' => true,
                'canEdit' => false, // decisione del capo, corretta due volte
            ],
        ]);
    }

    public function shooting(Request $request, string $clientSlug): Response
    {
        $link = $this->link($request);

        return Inertia::render('public-ped/shooting', [
            'client' => $this->clientPayload($link->client),
            'clientSlug' => $clientSlug,
            'sessions' => ShootingSession::where('client_id', $link->client_id)
                ->whereDate('session_date', '>=', today()->subMonths(1))
                ->orderBy('session_date')
                ->get()
                ->map->toClientArray(),
        ]);
    }

    public function approve(Request $request, string $clientSlug, Content $content): RedirectResponse
    {
        $link = $this->link($request);

        // Il contenuto deve appartenere al cliente del link. Sempre, su ogni azione.
        abort_unless($content->quarter->client_id === $link->client_id, 404);
        abort_unless($request->user()->isClient(), 403);

        $content->update(['status' => ContentStatus::Approved->value]);

        $this->notifyTeam($link->client, $content, 'approvato');

        Inertia::flash('message', 'Contenuto approvato.');

        return back();
    }

    public function reject(Request $request, string $clientSlug, Content $content): RedirectResponse
    {
        $link = $this->link($request);

        abort_unless($content->quarter->client_id === $link->client_id, 404);
        abort_unless($request->user()->isClient(), 403);

        $data = $request->validate([
            'comment' => ['required', 'string', 'max:5000'], // un rifiuto senza motivo è inutile al team
        ]);

        $content->recordComment($request->user(), $data['comment']);
        $content->update(['status' => ContentStatus::NeedsChanges->value]);

        $this->notifyTeam($link->client, $content, 'rifiutato');

        return back();
    }

    public function comment(Request $request, string $clientSlug, Content $content): RedirectResponse
    {
        $link = $this->link($request);

        abort_unless($content->quarter->client_id === $link->client_id, 404);
        abort_unless($request->user()->isClient(), 403);

        $data = $request->validate([
            'body' => ['required', 'string', 'max:5000'],
        ]);

        $content->recordComment($request->user(), $data['body']);

        $this->notifyTeam($link->client, $content, 'commentato');

        return back();
    }

    /** Al massimo un invito ogni 60 secondi per email, per non spammare Resend né la casella del cliente. */
    private function resendInvite(User $user, Client $client): void
    {
        $key = 'ped-invite:'.$user->email;

        if (RateLimiter::tooManyAttempts($key, 1)) {
            return;
        }

        RateLimiter::hit($key, 60);

        try {
            PedInvite::send($user, $client);
        } catch (\Throwable $e) {
            report($e);
        }
    }

    private function link(Request $request): ClientPublicLink
    {
        return $request->attributes->get('publicLink');
    }

    /** Un solo posto per scegliere QUALE trimestre mostrare al cliente: il corrente, o il più recente. */
    private function currentQuarter(Client $client): ?Quarter
    {
        return $client->quarters()->current()->first()
            ?? $client->quarters()->orderByDesc('year')->orderByDesc('quarter_number')->first();
    }

    /**
     * Solo i campi pubblici del cliente: mai note interne, tone of voice, dati di altri clienti.
     *
     * @return array{name: string, initials: string, logoUrl: string|null}
     */
    private function clientPayload(Client $client): array
    {
        return [
            'name' => $client->name,
            'initials' => $client->initials(),
            'logoUrl' => $client->logo_path ? Storage::url($client->logo_path) : null,
        ];
    }

    /** Account manager assegnati + tutti gli admin. */
    private function notifyTeam(Client $client, Content $content, string $azione): void
    {
        $recipients = $client->teamMembers
            ->merge(User::where('role', 'admin')->where('is_active', true)->get())
            ->unique('id');

        Notification::send($recipients, new ClientActionTaken($client, $content, $azione));
    }
}
