<?php

namespace App\Http\Controllers;

use App\Models\Quarter;
use App\Models\TopicPreview;
use App\Models\TopicPreviewItem;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class TopicPreviewController extends Controller
{
    use AuthorizesRequests;

    public function index(Quarter $quarter): Response
    {
        $this->authorize('view', $quarter->client);

        return Inertia::render('topics/index', [
            'quarter' => [
                'id' => $quarter->id,
                'label' => $quarter->label,
                'client' => [
                    'id' => $quarter->client->id,
                    'name' => $quarter->client->name,
                ],
            ],
            'months' => $quarter->topicPreviews()
                ->with('items.contentType:id,label')
                ->orderBy('month_order')
                ->get()
                ->map(fn (TopicPreview $preview) => $this->monthPayload($preview)),
        ]);
    }

    public function store(Request $request, Quarter $quarter): RedirectResponse
    {
        $this->authorize('update', $quarter->client);

        $data = $request->validate([
            'month_label' => ['required', 'string', 'max:32'],
            'month_order' => ['required', 'integer', 'min:1', 'max:12'],
        ]);

        $quarter->topicPreviews()->create($data);

        Inertia::flash('message', 'Mese aggiunto alla pre-verifica.');

        return back();
    }

    public function update(Request $request, TopicPreview $topicPreview): RedirectResponse
    {
        $this->authorize('update', $topicPreview->quarter->client);

        $data = $request->validate([
            'month_label' => ['required', 'string', 'max:32'],
            'status' => ['required', Rule::in(['draft', 'ready'])],
            'note' => ['nullable', 'string'],
        ]);

        $topicPreview->update($data);

        Inertia::flash('message', 'Mese aggiornato.');

        return back();
    }

    public function destroy(TopicPreview $topicPreview): RedirectResponse
    {
        $this->authorize('update', $topicPreview->quarter->client);

        $topicPreview->delete();

        Inertia::flash('message', 'Mese eliminato.');

        return back();
    }

    /** @return array<string, mixed> */
    private function monthPayload(TopicPreview $preview): array
    {
        return [
            'id' => $preview->id,
            'monthLabel' => $preview->month_label,
            'monthOrder' => $preview->month_order,
            'status' => $preview->status,
            'statusLabel' => $preview->statusLabel(),
            'note' => $preview->note,
            'items' => $preview->items->map(fn (TopicPreviewItem $item) => [
                'id' => $item->id,
                'contentTypeId' => $item->content_type_id,
                'formatLabel' => $item->format_label,
                'periodLabel' => $item->period_label,
                'title' => $item->title,
                'theme' => $item->theme,
                'objective' => $item->objective,
                'footnote' => $item->footnote,
                'sortOrder' => $item->sort_order,
                'contentId' => $item->content_id,
            ]),
        ];
    }
}
