<?php

namespace App\Http\Controllers;

use App\Enums\ContentStatus;
use App\Models\Client;
use App\Models\ClientPublicLink;
use App\Models\Content;
use App\Models\User;
use App\Notifications\ClientActionTaken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;

class PublicPedController extends Controller
{
    public function entry(Request $request, string $token)
    {
        $link = $this->link($request);
        $user = $request->user();

        if ($user !== null) {
            return to_route('ped.feed', $token);
        }

        return Inertia::render('public-ped/entry', [
            'client' => $this->clientPayload($link->client),
            'token' => $token,
            'mode' => null,
            'email' => null,
            'passwordRules' => Password::defaults()->toPasswordRulesString(),
        ]);
    }

    public function checkEmail(Request $request, string $token)
    {
        $link = $this->link($request);

        $data = $request->validate([
            'email' => ['required', 'email', 'max:255'],
        ]);

        $exists = User::where('email', $data['email'])
            ->where('role', 'client')
            ->whereHas('clients', fn ($q) => $q->whereKey($link->client_id))
            ->exists();

        return Inertia::render('public-ped/entry', [
            'client' => $this->clientPayload($link->client),
            'token' => $token,
            'mode' => $exists ? 'login' : 'register',
            'email' => $data['email'],
            'passwordRules' => Password::defaults()->toPasswordRulesString(),
        ]);
    }

    public function feed(Request $request, string $token)
    {
        $link = $this->link($request);

        $quarter = $link->client->quarters()->current()->first()
            ?? $link->client->quarters()->orderByDesc('year')->orderByDesc('quarter_number')->first();

        $contents = $quarter
            ? $quarter->contents()
                ->whereIn('status', $link->visibleStatuses())
                ->orderBy('publish_at')
                ->get()
                ->map(fn (Content $c) => $c->toFeedArray())
            : collect();

        return Inertia::render('public-ped/feed', [
            'client' => $this->clientPayload($link->client),
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

    public function approve(Request $request, string $token, Content $content)
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

    public function reject(Request $request, string $token, Content $content)
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

    public function comment(Request $request, string $token, Content $content)
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

    private function link(Request $request): ClientPublicLink
    {
        return $request->attributes->get('publicLink');
    }

    /** Solo i campi pubblici del cliente: mai note interne, tone of voice, dati di altri clienti. */
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
