<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Quarter;
use App\Models\Setting;
use App\Models\ShootingSession;
use App\Models\ShootingSessionAssignment;
use App\Models\ShootingTarget;
use App\Models\User;
use App\Support\WorkloadCalculator;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class ShootingController extends Controller
{
    public function index()
    {
        $sessions = ShootingSession::with(['client:id,name', 'assignments.user:id,name'])
            ->where('session_date', '>=', today()->subDays(7))
            ->orderBy('session_date')
            ->get();

        return Inertia::render('shooting/index', [
            'targets' => ShootingTarget::with(['client:id,name', 'quarter:id,label'])
                ->orderByDesc('quarter_id')
                ->get()
                ->map(fn (ShootingTarget $t) => [
                    'id' => $t->id,
                    'clientName' => $t->client->name,
                    'periodLabel' => $t->period_label,
                    'weight' => $t->weight,
                    'idealSessions' => $t->ideal_sessions,
                    'plannedSessions' => $t->planned_sessions,
                    'potentialSessions' => $t->potential_sessions,
                    'statusNote' => $t->status_note,
                    'atRisk' => ($t->planned_sessions + $t->potential_sessions) < $t->ideal_sessions,
                ]),
            'sessions' => $sessions->map(fn (ShootingSession $s) => [
                'id' => $s->id,
                'date' => $s->session_date->toDateString(),
                'dateLabel' => $s->session_date->translatedFormat('D j M'),
                'clientName' => $s->client->name,
                'type' => $s->type->value,
                'typeLabel' => $s->type->label(),
                'checkpointRequired' => $s->checkpoint_required,
                'checkpointNote' => $s->checkpoint_note,
                'assignments' => $s->assignments->map(fn (ShootingSessionAssignment $a) => [
                    'role' => $a->role->value,
                    'roleInitial' => $a->role->initial(),
                    'userName' => $a->user->name,
                    'isAlternative' => $a->is_alternative,
                ]),
            ]),
            'workload' => WorkloadCalculator::forMonth(CarbonImmutable::now()),
            'clients' => Client::where('status', 'active')->orderBy('name')->get(['id', 'name']),
            'quarters' => Client::query()
                ->with(['quarters:id,client_id,label'])
                ->get()
                ->flatMap(fn (Client $c) => $c->quarters->map(fn ($q) => [
                    'id' => $q->id, 'clientId' => $c->id, 'label' => $q->label,
                ]))
                ->values(),
            'assignableUsers' => User::whereIn('role', ['admin', 'account_manager', 'copywriter'])
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name']),
            'planningRules' => Setting::get('shooting.planning_rules'),
        ]);
    }

    public function storeTarget(Request $request)
    {
        $data = $request->validate([
            'client_id' => ['required', 'exists:clients,id'],
            'quarter_id' => ['required', 'exists:quarters,id'],
            'ideal_sessions' => ['required', 'integer', 'min:0', 'max:255'],
            'planned_sessions' => ['required', 'integer', 'min:0', 'max:255'],
            'potential_sessions' => ['required', 'integer', 'min:0', 'max:255'],
            'weight' => ['required', Rule::in(['S', 'M', 'L'])],
            'status_note' => ['nullable', 'string'],
        ]);

        $quarter = Quarter::findOrFail($data['quarter_id']);

        ShootingTarget::updateOrCreate(
            ['client_id' => $data['client_id'], 'quarter_id' => $data['quarter_id']],
            [...$data, 'period_label' => $quarter->label],
        );

        Inertia::flash('message', 'Target salvato.');

        return back();
    }

    public function storeSession(Request $request)
    {
        $data = $request->validate([
            'client_id' => ['required', 'exists:clients,id'],
            'session_date' => ['required', 'date'],
            'type' => ['required', Rule::in(['photo', 'video', 'photo_video'])],
            'is_tentative' => ['boolean'],
            'checkpoint_required' => ['boolean'],
            'checkpoint_note' => ['nullable', 'string'],
            'internal_note' => ['nullable', 'string'],
            'assignments' => ['nullable', 'array'],
            'assignments.*.user_id' => ['required', 'exists:users,id'],
            'assignments.*.role' => ['required', Rule::in(['photo', 'video', 'coordination'])],
            'assignments.*.is_alternative' => ['boolean'],
        ]);

        $assignments = $data['assignments'] ?? [];
        unset($data['assignments']);

        $session = ShootingSession::create($data);

        foreach ($assignments as $assignment) {
            $session->assignments()->create($assignment);
        }

        Inertia::flash('message', 'Sessione creata.');

        return back();
    }

    public function updatePlanningRules(Request $request)
    {
        $data = $request->validate(['value' => ['nullable', 'string']]);

        Setting::set('shooting.planning_rules', $data['value'] ?? null);

        return back();
    }
}
