<?php

namespace App\Http\Controllers;

use App\Enums\ContentStatus;
use App\Models\ContentType;
use App\Models\TopicPreview;
use App\Models\TopicPreviewItem;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TopicPreviewItemController extends Controller
{
    use AuthorizesRequests;

    public function store(Request $request, TopicPreview $topicPreview): RedirectResponse
    {
        $this->authorize('update', $topicPreview->quarter->client);

        $data = $this->validated($request);
        $data['sort_order'] = (int) $topicPreview->items()->max('sort_order') + 1;

        $topicPreview->items()->create($data);

        Inertia::flash('message', 'Argomento aggiunto.');

        return back();
    }

    public function update(Request $request, TopicPreviewItem $item): RedirectResponse
    {
        $this->authorize('update', $item->preview->quarter->client);

        $item->update($this->validated($request));

        Inertia::flash('message', 'Argomento aggiornato.');

        return back();
    }

    public function destroy(TopicPreviewItem $item): RedirectResponse
    {
        $this->authorize('update', $item->preview->quarter->client);

        $item->delete();

        Inertia::flash('message', 'Argomento eliminato.');

        return back();
    }

    /** Trasforma un tema approvato in un contenuto reale. */
    public function promote(TopicPreviewItem $item): RedirectResponse
    {
        $this->authorize('update', $item->preview->quarter->client);

        abort_if($item->content_id !== null, 409, 'Questo tema è già stato trasformato in un contenuto.');

        $content = $item->preview->quarter->contents()->create([
            'content_type_id' => $item->content_type_id ?? ContentType::active()->first()?->id,
            'title' => $item->title,
            'caption' => null,
            'publish_at' => $item->preview->quarter->starts_on,   // il team la sposterà
            'status' => ContentStatus::Draft->value,
        ]);

        $item->update(['content_id' => $content->id]);

        return to_route('contents.edit', $content);
    }

    /** @return array<string, mixed> */
    private function validated(Request $request): array
    {
        return $request->validate([
            'content_type_id' => ['nullable', 'exists:content_types,id'],
            'format_label' => ['required', 'string', 'max:64'],
            'period_label' => ['required', 'string', 'max:48'],
            'title' => ['required', 'string', 'max:255'],
            'theme' => ['required', 'string', 'max:255'],
            'objective' => ['required', 'string', 'max:255'],
            'footnote' => ['nullable', 'string'],
        ]);
    }
}
