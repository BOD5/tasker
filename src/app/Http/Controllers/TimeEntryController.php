<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTimeEntryRequest;
use App\Http\Requests\UpdateTimeEntryRequest;
use App\Models\CustomFieldDefinition;
use App\Models\Task;
use App\Models\Team;
use App\Policies\TimeEntryPolicy;
use App\Models\TimeEntry;
use App\Models\TimeEntryCustomFieldValue;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class TimeEntryController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request): InertiaResponse
    {
        $validated = $request->validate([
            'start_date' => 'nullable|date_format:Y-m-d',
            'end_date' => 'nullable|date_format:Y-m-d|after_or_equal:start_date',
            'sort' => ['nullable', 'string', Rule::in(['description', 'started_at', 'ended_at'])],
            'direction' => ['nullable', 'string', Rule::in(['asc', 'desc'])],
        ]);

        $user = Auth::user()->loadMissing('teams:id');
        $startDate = $validated['start_date'] ?? null;
        $endDate = $validated['end_date'] ?? null;
        $sortColumn = $validated['sort'] ?? 'started_at';
        $sortDirection = $validated['direction'] ?? 'desc';
        $perPage = 30;
        $effectiveStartDate = $startDate ? Carbon::parse($startDate) : null;
        $effectiveEndDate = $endDate ? Carbon::parse($endDate) : null;

        $activeTimer = TimeEntry::where('user_id', $user->id)
            ->whereNull('ended_at')
            ->with([
                'task:id,title',
                'team:id,name',
                'customFieldValues.definition:id,code,type,name,options,is_required'
            ])
            ->first();

        $timeEntriesQuery = TimeEntry::where('user_id', $user->id)
            ->with([
                'task:id,title',
                'team:id,name',
                'customFieldValues.definition:id,code,type,name,options,is_required'
            ]);

        if ($effectiveStartDate) {
            $queryStart = $effectiveStartDate->copy()->startOfDay();
            $queryEnd = $effectiveEndDate ? $effectiveEndDate->copy()->endOfDay() : $effectiveStartDate->copy()->endOfDay();
            $timeEntriesQuery->whereBetween('started_at', [$queryStart, $queryEnd]);
        }

        $timeEntriesQuery->orderBy($sortColumn, $sortDirection);
        if ($sortColumn !== 'started_at') {
            $timeEntriesQuery->orderBy('started_at', 'desc');
        }

        $timeEntries = $timeEntriesQuery->paginate($perPage)->withQueryString();

        $availableTeams = $user->teams()->select('teams.id', 'teams.name')->get();;
        $availableTasks = Task::query()
            ->where(function ($query) use ($user) {
                $query->whereRelation('assignees', 'users.id', $user->id);
            })
            ->whereNotIn('status', ['done', 'archived'])
            ->select('id', 'title', 'project_id')->orderBy('title')->get();

        $userTeamIds = $user->teams()->pluck('teams.id')->toArray();
        $customFieldDefinitions = CustomFieldDefinition::query()
            ->whereNull('team_id')
            ->orWhereIn('team_id', $userTeamIds)
            ->orderBy('name')
            ->get(['id', 'team_id', 'name', 'code', 'type', 'options', 'is_required']);

        $lastEntry = TimeEntry::where('user_id', $user->id)->latest('id')->first();
        $lastTeamId = $lastEntry?->team_id;
        $generalTeamId = Team::where('name', 'General')->value('id');


        return Inertia::render('TimeTracking/Dashboard', [
            'activeTimer' => $activeTimer,
            'timeEntries' => $timeEntries,
            'availableTeams' => $availableTeams,
            'availableTasks' => $availableTasks,
            'customFieldDefinitions' => $customFieldDefinitions,
            'lastTeamId' => $lastTeamId ?? null,
            'generalTeamId' => $generalTeamId ?? null,
            'filters' => [
                'start_date' => $effectiveStartDate?->toDateString(),
                'end_date' => $effectiveEndDate?->toDateString(),
                'sort' => $sortColumn,
                'direction' => $sortDirection,
            ],
            'errors' => session('errors') ? session('errors')->getBag('default')->getMessages() : (object) [],
        ]);
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTimeEntryRequest $request): RedirectResponse
    {
        $user = Auth::user();
        $validated = $request->validated();

        $timeEntry = TimeEntry::create([
            'user_id'     => $user->id,
            'team_id'     => $validated['team_id'],
            'task_id'     => $validated['task_id'] ?? null,
            'description' => $validated['description'],
            'started_at'  => isset($validated['started_at']) ? Carbon::parse($validated['started_at']) : now(),
            'ended_at'    => isset($validated['ended_at']) ? Carbon::parse($validated['ended_at']) : null,
        ]);

        $customFieldsInput = $validated['custom_fields'] ?? [];
        if (!empty($customFieldsInput)) {
            $definitions = CustomFieldDefinition::query()
                ->where(function ($q) use ($validated) {
                    $q->whereNull('team_id')
                        ->orWhere('team_id', $validated['team_id']);
                })
                ->whereIn('code', array_keys($customFieldsInput))
                ->get()
                ->keyBy('code');

            $valuesToInsert = [];
            foreach ($customFieldsInput as $code => $value) {
                if ($definitions->has($code) && ($value !== null && $value !== '')) {
                    if ($definitions[$code]->type === 'boolean') {
                        $value = filter_var($value, FILTER_VALIDATE_BOOLEAN) ? '1' : '0';
                    }
                    $valuesToInsert[] = [
                        'time_entry_id' => $timeEntry->id,
                        'custom_field_definition_id' => $definitions[$code]->id,
                        'value' => (string) $value,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }
            if (!empty($valuesToInsert)) {
                TimeEntryCustomFieldValue::insert($valuesToInsert);
            }
        }
        return redirect()->route('app.time-tracking.index')->with('flash_notification', [
            'type' => 'success',
            'title' => 'Успіх!',
            'message' => 'Таймер запущено.',
        ]);
    }


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    public function update(UpdateTimeEntryRequest $request, TimeEntry $timeEntry): RedirectResponse
    {

        $validated = $request->validated();
        $updateData = [];

        if (isset($validated['description'])) {
            $updateData['description'] = $validated['description'];
        }
        if (isset($validated['team_id'])) {
            $updateData['team_id'] = $validated['team_id'];
        }
        if (array_key_exists('task_id', $validated)) {
            $updateData['task_id'] = $validated['task_id'];
        }
        if (isset($validated['started_at'])) {
            $updateData['started_at'] = Carbon::parse($validated['started_at']);
        }
        if (isset($validated['ended_at'])) {
            $updateData['ended_at'] = Carbon::parse($validated['ended_at']);
        } elseif (array_key_exists('ended_at', $validated) && $validated['ended_at'] === null) {
            $updateData['ended_at'] = null;
        }
        if (!empty($updateData)) {
            $timeEntry->update($updateData);
        }

        $customFieldsInput = $validated['custom_fields'] ?? [];
        if (!empty($customFieldsInput)) {
            $definitions = CustomFieldDefinition::query()
                ->where(function ($q) use ($validated) {
                    $q->whereNull('team_id')->orWhere('team_id', $validated['team_id']);
                })
                ->whereIn('code', array_keys($customFieldsInput))
                ->get()
                ->keyBy('code');

            foreach ($customFieldsInput as $code => $value) {
                if ($definitions->has($code)) {
                    $definitionId = $definitions[$code]->id;
                    $fieldValue = ($definitions[$code]->type === 'boolean') ? filter_var($value, FILTER_VALIDATE_BOOLEAN) : $value;

                    TimeEntryCustomFieldValue::updateOrCreate(
                        [
                            'time_entry_id' => $timeEntry->id,
                            'custom_field_definition_id' => $definitionId,
                        ],
                        [
                            'value' => (string) $fieldValue,
                        ]
                    );
                }
            }
        }

        return redirect()->route('app.time-tracking.index')->with('flash_notification', [
            'type' => 'success',
            'title' => 'Успіх!',
            'message' => 'Запис оновлено.'
        ]);
    }
    public function stop(TimeEntry $timeEntry): RedirectResponse
    {
        if (Auth::id() !== $timeEntry->user_id) {
            abort(403, 'Unauthorized action.');
        }

        if ($timeEntry->ended_at !== null) {
            return back()->with('error', 'Цей таймер вже зупинено.');
        }

        $updated = $timeEntry->update(['ended_at' => now()]);

        if ($updated) {
            return redirect()->route('app.time-tracking.index')->with('flash_notification', [
                'type' => 'success',
                'title' => 'Успіх!',
                'message' => 'Таймер зупинено!',
            ]);
        } else {
            return back()->with('flash_notification', [
                'type' => 'error',
                'title' => 'Помилка!',
                'message' => 'Не вдалося зупинити таймер',
            ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TimeEntry $timeEntry): RedirectResponse
    {
        $this->authorize('delete', $timeEntry);

        $timeEntry->delete();

        return redirect()->route('app.time-tracking.index')
            ->with('flash_notification', [
                'type' => 'success',
                'title' => 'Успіх!',
                'message' => 'Запис видалено',
            ]);
    }
}
