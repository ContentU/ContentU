<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContentType;
use App\Support\ArtifactPromptRules;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class ContentTypeController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('settings/content-types', [
            'contentTypes' => ContentType::orderBy('sort_order')
                ->get()
                ->map(fn (ContentType $type) => [
                    'id' => $type->id,
                    'key' => $type->key,
                    'label' => $type->label,
                    'requiresSecondaryAsset' => $type->requires_secondary_asset,
                    'isActive' => $type->is_active,
                    'sortOrder' => $type->sort_order,
                ]),
            'artifactPromptRules' => ArtifactPromptRules::current(),
        ]);
    }

    /** Regole globali del prompt per gli artifact Claude (Fase 02). */
    public function updateArtifactPromptRules(Request $request): RedirectResponse
    {
        $data = $request->validate(['value' => ['nullable', 'string']]);

        ArtifactPromptRules::set($data['value'] ?? null);

        Inertia::flash('message', 'Regole prompt artifact aggiornate.');

        return back();
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        $data['sort_order'] = (int) ContentType::max('sort_order') + 1;

        ContentType::create($data);

        Inertia::flash('message', 'Tipologia creata.');

        return back();
    }

    public function update(Request $request, ContentType $contentType): RedirectResponse
    {
        $data = $this->validated($request, $contentType->id);

        $contentType->update($data);

        Inertia::flash('message', 'Tipologia aggiornata.');

        return back();
    }

    public function destroy(ContentType $contentType): RedirectResponse
    {
        if ($contentType->contents()->exists()) {
            return back()->withErrors([
                'key' => 'Questa tipologia è usata da contenuti esistenti: puoi solo disattivarla.',
            ]);
        }

        $contentType->delete();

        Inertia::flash('message', 'Tipologia eliminata.');

        return back();
    }

    /** @return array<string, mixed> */
    private function validated(Request $request, ?int $id = null): array
    {
        return $request->validate([
            'key' => ['required', 'alpha_dash', 'max:50', Rule::unique('content_types', 'key')->ignore($id)],
            'label' => ['required', 'string', 'max:100'],
            'requires_secondary_asset' => ['boolean'],
            'is_active' => ['boolean'],
        ]);
    }
}
