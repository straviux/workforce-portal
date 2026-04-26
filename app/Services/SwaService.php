<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\CalendarEvent;
use App\Models\SwaReport;
use App\Models\SwaTask;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SwaService
{
    public function setupPayload(Model $subject): array
    {
        return [
            'subject' => $this->subjectPayload($subject),
            'tasks' => $this->tasksPayload($subject),
            'reports' => $this->reportsPayload($subject),
            'calendar_events' => $this->calendarEventsPayload(),
        ];
    }

    public function subjectPayload(Model $subject): array
    {
        if ($subject instanceof Employee) {
            return [
                'id' => $subject->id,
                'subject_type' => 'employee',
                'display_name' => $subject->full_name,
                'employee_no' => $subject->employee_no,
                'office' => $subject->office,
                'designation' => $subject->designation,
                'secondary_label' => $subject->employee_type,
            ];
        }

        return [
            'id' => $subject->id,
            'subject_type' => 'user',
            'display_name' => $subject->name,
            'employee_no' => null,
            'office' => $subject->office_designation,
            'designation' => $subject->office_designation,
            'secondary_label' => $subject->email,
        ];
    }

    public function tasksPayload(Model $subject): array
    {
        return $this->activeTasksQuery($subject)
            ->get()
            ->map(fn(SwaTask $task) => [
                'id' => $task->id,
                'task_name' => $task->task_name,
                'task_type' => $task->task_type,
                'sort_order' => $task->sort_order,
            ])
            ->values()
            ->all();
    }

    public function reportsPayload(Model $subject): array
    {
        return $subject->swaReports()
            ->withCount('tasks')
            ->with('generator')
            ->latest()
            ->limit(10)
            ->get()
            ->map(fn(SwaReport $report) => $this->reportSummary($report))
            ->values()
            ->all();
    }

    public function calendarEventsPayload(): array
    {
        return CalendarEvent::query()
            ->active()
            ->orderBy('event_date')
            ->orderBy('title')
            ->get()
            ->map(fn(CalendarEvent $event) => [
                'id' => $event->id,
                'event_date' => optional($event->event_date)->toDateString(),
                'title' => $event->title,
                'event_type' => $event->event_type,
                'description' => $event->description,
                'is_system' => $event->is_system,
            ])
            ->values()
            ->all();
    }

    public function reportSummary(SwaReport $report): array
    {
        return [
            'id' => $report->id,
            'module_type' => $report->module_type,
            'period_start_date' => optional($report->period_start_date)->toDateString(),
            'period_end_date' => optional($report->period_end_date)->toDateString(),
            'work_days' => $report->work_days ?? [],
            'task_count' => (int) ($report->tasks_count ?? $report->tasks()->count()),
            'generated_by_name' => $report->generator?->name,
            'created_at' => optional($report->created_at)->toISOString(),
        ];
    }

    public function syncTasks(Model $subject, array $tasks): array
    {
        $orderedTasks = collect($tasks)
            ->sortBy('sort_order')
            ->values();

        DB::transaction(function () use ($subject, $orderedTasks) {
            $existing = SwaTask::withTrashed()
                ->where('subject_type', $subject->getMorphClass())
                ->where('subject_id', $subject->getKey())
                ->get()
                ->keyBy('sort_order');

            $activeIds = [];

            foreach ($orderedTasks as $row) {
                $task = $existing->get($row['sort_order']) ?? new SwaTask([
                    'subject_type' => $subject->getMorphClass(),
                    'subject_id' => $subject->getKey(),
                ]);

                if ($task->trashed()) {
                    $task->restore();
                }

                $task->fill([
                    'task_name' => trim((string) $row['task_name']),
                    'task_type' => $row['task_type'],
                    'sort_order' => (int) $row['sort_order'],
                    'is_active' => true,
                ]);
                $task->save();

                $activeIds[] = $task->id;
            }

            SwaTask::query()
                ->where('subject_type', $subject->getMorphClass())
                ->where('subject_id', $subject->getKey())
                ->whereNotIn('id', $activeIds)
                ->get()
                ->each(function (SwaTask $task) {
                    $task->is_active = false;
                    $task->save();
                    $task->delete();
                });
        });

        return $this->tasksPayload($subject);
    }

    public function createReport(Model $subject, string $moduleType, array $validated): SwaReport
    {
        $tasks = $this->activeTasksQuery($subject)->get();

        if ($tasks->count() !== 5) {
            throw ValidationException::withMessages([
                'tasks' => 'Encode exactly 5 tasks before generating SWA.',
            ]);
        }

        $workDays = collect($validated['work_days'])
            ->map(fn($day) => strtolower((string) $day))
            ->unique()
            ->values();

        $expectedDates = $this->expectedWorkDates(
            (string) $validated['period_start_date'],
            (string) $validated['period_end_date'],
            $workDays,
        );

        if ($expectedDates->isEmpty()) {
            throw ValidationException::withMessages([
                'work_days' => 'The selected work schedule produced no work dates for the chosen range.',
            ]);
        }

        $draftRows = collect($validated['draft_rows'])->keyBy('sort_order');

        return DB::transaction(function () use ($subject, $moduleType, $validated, $workDays, $expectedDates, $draftRows, $tasks) {
            $report = SwaReport::query()->create([
                'module_type' => $moduleType,
                'subject_type' => $subject->getMorphClass(),
                'subject_id' => $subject->getKey(),
                'period_start_date' => $validated['period_start_date'],
                'period_end_date' => $validated['period_end_date'],
                'work_days' => $workDays->all(),
            ]);

            foreach ($tasks as $task) {
                $row = $draftRows->get($task->sort_order);

                if (!$row) {
                    throw ValidationException::withMessages([
                        'draft_rows' => 'Draft values are missing one or more task rows.',
                    ]);
                }

                $rowValues = collect($row['daily_values'])
                    ->keyBy(fn(array $value) => Carbon::parse($value['work_date'])->toDateString());

                if ($rowValues->keys()->sort()->values()->all() !== $expectedDates->sort()->values()->all()) {
                    throw ValidationException::withMessages([
                        'draft_rows' => 'Draft values do not match the selected work schedule and date range.',
                    ]);
                }

                $reportTask = $report->tasks()->create([
                    'source_task_id' => $task->id,
                    'task_name' => $task->task_name,
                    'task_type' => $task->task_type,
                    'sort_order' => $task->sort_order,
                ]);

                foreach ($expectedDates as $date) {
                    $cell = $rowValues->get($date, []);

                    $reportTask->dailyValues()->create([
                        'work_date' => $date,
                        'numeric_value' => $task->task_type === 'countable'
                            ? (is_numeric($cell['numeric_value'] ?? null) ? (float) $cell['numeric_value'] : 0)
                            : null,
                        'mark_value' => $task->task_type === 'check_blank'
                            ? (($cell['mark_value'] ?? 'dash') === 'check' ? 'check' : 'dash')
                            : null,
                    ]);
                }
            }

            return $report->loadCount('tasks')->load('generator');
        });
    }

    private function activeTasksQuery(Model $subject)
    {
        return SwaTask::query()
            ->where('subject_type', $subject->getMorphClass())
            ->where('subject_id', $subject->getKey())
            ->where('is_active', true)
            ->orderBy('sort_order');
    }

    private function expectedWorkDates(string $startDate, string $endDate, Collection $workDays): Collection
    {
        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->startOfDay();
        $allowedDays = $workDays->map(fn($day) => strtolower((string) $day))->values();
        $blockedDates = CalendarEvent::query()
            ->active()
            ->betweenDates($start->toDateString(), $end->toDateString())
            ->pluck('event_date')
            ->map(fn($date) => Carbon::parse($date)->toDateString())
            ->unique();

        $dates = collect();
        $cursor = $start->copy();

        while ($cursor->lte($end)) {
            if (
                $allowedDays->contains(strtolower($cursor->englishDayOfWeek))
                && !$blockedDates->contains($cursor->toDateString())
            ) {
                $dates->push($cursor->toDateString());
            }

            $cursor->addDay();
        }

        return $dates;
    }
}
