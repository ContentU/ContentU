<?php

namespace App\Http\Controllers;

use App\Enums\ContentStatus;
use App\Models\Content;
use App\Models\ContentType;
use App\Models\Quarter;
use App\Models\Tag;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class ContentController extends Controller
{
    use AuthorizesRequests;

    public function create(Quarter $quarter)
    {
        $this->authorize('update', $quarter->client);

        return Inertia::render('contents/create', [
            'quarter' => $this->quarterPayload($quarter),
            'contentTypes' => $this->contentTypesPayload(),
            'tags' => $this->tagsPayload($quarter),
            'channels' => config('ped.channels'),
        ]);
    }

    public function store(Request $request, Quarter $quarter)
    {
        $this->authorize('update', $quarter->client);

        $data = $this->validated($request);
        $tagIds = $data['tag_ids'] ?? [];
        unset($data['tag_ids']);

        $content = $quarter->contents()->create($data);
        $content->tags()->sync($tagIds);

        Inertia::flash('message', 'Contenuto creato.');

        return to_route('contents.edit', $content);
    }

    public function edit(Content $content)
    {
        $this->authorize('update', $content->quarter->client);

        $content->loadMissing('contentType', 'tags');

        return Inertia::render('contents/edit', [
            'quarter' => $this->quarterPayload($content->quarter),
            'content' => [
                'id' => $content->id,
                'contentTypeId' => $content->content_type_id,
                'title' => $content->title,
                'caption' => $content->caption,
                'hashtags' => $content->hashtags,
                'resourceUrl' => $content->resource_url,
                'coverResourceUrl' => $content->cover_resource_url,
                'publishAt' => $content->publish_at->format('Y-m-d\TH:i'),
                'channels' => $content->channels ?? [],
                'tagIds' => $content->tags->pluck('id'),
            ],
            'contentTypes' => $this->contentTypesPayload(),
            'tags' => $this->tagsPayload($content->quarter),
            'channels' => config('ped.channels'),
            'readiness' => $content->readiness(),
        ]);
    }

    public function update(Request $request, Content $content)
    {
        $this->authorize('update', $content->quarter->client);

        $data = $this->validated($request);
        $tagIds = $data['tag_ids'] ?? [];
        unset($data['tag_ids']);

        $content->update($data);
        $content->tags()->sync($tagIds);

        Inertia::flash('message', 'Contenuto aggiornato.');

        return back();
    }

    public function destroy(Content $content)
    {
        $this->authorize('update', $content->quarter->client);

        $quarter = $content->quarter;
        $content->delete();

        Inertia::flash('message', 'Contenuto eliminato.');

        return to_route('quarters.show', $quarter);
    }

    public function updateStatus(Request $request, Content $content)
    {
        $this->authorize('update', $content->quarter->client);

        $target = ContentStatus::from($request->validate([
            'status' => ['required', Rule::enum(ContentStatus::class)],
        ])['status']);

        if (! $content->status->canTransitionTo($target)) {
            return back()->withErrors([
                'status' => "Non si può passare da «{$content->status->label()}» a «{$target->label()}».",
            ]);
        }

        // Un copywriter non pubblica né approva (§02 del PED).
        if (! $request->user()->isAdmin() && ! $request->user()->isAccountManager()
            && in_array($target, [ContentStatus::Approved, ContentStatus::Scheduled, ContentStatus::Published], true)) {
            abort(403, 'Il tuo ruolo non può approvare o pubblicare contenuti.');
        }

        // Checklist pre-pubblicazione (§3.2 S, inclusa in v1).
        if ($target === ContentStatus::Scheduled) {
            $blocking = $content->loadMissing('contentType')->readiness()['blocking'];

            if ($blocking !== []) {
                return back()->withErrors(['status' => implode(' ', $blocking)]);
            }
        }

        $content->update(['status' => $target->value]);
        Inertia::flash('message', 'Stato del contenuto aggiornato.');

        return back();
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'content_type_id' => ['required', 'exists:content_types,id'],
            'title' => ['required', 'string', 'max:255'],
            'caption' => ['nullable', 'string'],
            'hashtags' => ['nullable', 'string', 'max:2000'],
            'resource_url' => ['nullable', 'url', 'max:2048'],
            'cover_resource_url' => ['nullable', 'url', 'max:2048'],
            'publish_at' => ['required', 'date'],
            'channels' => ['nullable', 'array'],
            'channels.*' => ['string', Rule::in(config('ped.channels'))],
            'tag_ids' => ['nullable', 'array'],
            'tag_ids.*' => ['integer', 'exists:tags,id'],
        ]);
    }

    private function quarterPayload(Quarter $quarter): array
    {
        return [
            'id' => $quarter->id,
            'label' => $quarter->label,
            'client' => [
                'id' => $quarter->client->id,
                'name' => $quarter->client->name,
            ],
        ];
    }

    private function contentTypesPayload()
    {
        return ContentType::active()->get()->map(fn (ContentType $type) => [
            'id' => $type->id,
            'label' => $type->label,
            'requiresSecondaryAsset' => $type->requires_secondary_asset,
        ]);
    }

    private function tagsPayload(Quarter $quarter)
    {
        return Tag::where('client_id', $quarter->client_id)
            ->orWhereNull('client_id')
            ->orderBy('label')
            ->get(['id', 'label']);
    }
}
