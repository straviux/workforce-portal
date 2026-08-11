<?php

namespace App\Services;

use App\Models\CalendarEvent;
use App\Models\DtrReport;
use App\Models\DtrReportDailyValue;
use App\Models\Employee;
use App\Models\Signatory;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DtrService
{
    private const TIME_FIELDS = [
        'am_arrival',
        'am_departure',
        'pm_arrival',
        'pm_departure',
    ];

    public function setupPayload(Model $subject): array
    {
        return [
            'subject' => $this->subjectPayload($subject),
            'reports' => $this->reportsPayload($subject),
            'calendar_events' => $this->calendarEventsPayload(),
            'office_heads' => $this->officeHeadsPayload(),
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
            'office' => $subject->office ?: $subject->office_designation,
            'designation' => $subject->designation ?: $subject->office_designation,
            'secondary_label' => $subject->email,
        ];
    }

    public function officeHeadsPayload(): array
    {
        return Signatory::query()
            ->where('part', 'A')
            ->orderBy('id')
            ->get()
            ->map(fn (Signatory $signatory) => [
                'id' => $signatory->id,
                'name' => $signatory->name,
                'office' => $signatory->office,
                'titles' => array_values(array_filter($signatory->title ?? [])),
            ])
            ->values()
            ->all();
    }

    public function reportsPayload(Model $subject): array
    {
        return $subject->dtrReports()
            ->withCount('dailyValues')
            ->with('generator')
            ->latest()
            ->limit(10)
            ->get()
            ->map(fn (DtrReport $report) => $this->reportSummary($report))
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
            ->map(fn (CalendarEvent $event) => [
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

    public function reportSummary(DtrReport $report): array
    {
        return [
            'id' => $report->id,
            'module_type' => $report->module_type,
            'period_start_date' => optional($report->period_start_date)->toDateString(),
            'period_end_date' => optional($report->period_end_date)->toDateString(),
            'work_days' => $report->work_days ?? [],
            'signatory_name_underline' => $report->signatory_name_underline !== false,
            'signatory_show_designation' => $report->signatory_show_designation !== false,
            'signatory_show_office' => $report->signatory_show_office !== false,
            'signatory_info_order' => $report->signatory_info_order === 'office_first' ? 'office_first' : 'designation_first',
            'day_count' => (int) ($report->daily_values_count ?? $report->dailyValues()->count()),
            'generated_by_name' => $report->generator?->name,
            'created_at' => optional($report->created_at)->toISOString(),
        ];
    }

    public function reportDetail(DtrReport $report): array
    {
        $report->loadMissing(['dailyValues', 'generator']);

        return array_merge($this->reportSummary($report), [
            'office_head_signatory_id' => $report->office_head_signatory_id,
            'signatory_name' => $report->signatory_name,
            'signatory_office' => $report->signatory_office,
            'signatory_titles' => $report->signatory_titles ?? [],
            'daily_values' => $report->dailyValues
                ->map(fn (DtrReportDailyValue $value) => $this->dailyValuePayload($value))
                ->values()
                ->all(),
        ]);
    }

    public function createReport(Model $subject, string $moduleType, array $validated): DtrReport
    {
        $workDays = collect($validated['work_days'])
            ->map(fn ($day) => strtolower((string) $day))
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

        $signatory = $this->resolveSignatory($validated);
        $dailyValuesByDate = collect($validated['daily_values'])
            ->keyBy(fn (array $value) => Carbon::parse($value['work_date'])->toDateString());

        if ($dailyValuesByDate->keys()->sort()->values()->all() !== $expectedDates->sort()->values()->all()) {
            throw ValidationException::withMessages([
                'daily_values' => 'Daily values do not match the selected work schedule and date range.',
            ]);
        }

        return DB::transaction(function () use ($subject, $moduleType, $validated, $workDays, $expectedDates, $signatory, $dailyValuesByDate) {
            $report = DtrReport::query()->create([
                'module_type' => $moduleType,
                'subject_type' => $subject->getMorphClass(),
                'subject_id' => $subject->getKey(),
                ...$signatory,
                'period_start_date' => $validated['period_start_date'],
                'period_end_date' => $validated['period_end_date'],
                'work_days' => $workDays->all(),
            ]);

            foreach ($expectedDates as $date) {
                $report->dailyValues()->create([
                    'work_date' => $date,
                    ...$this->dailyFieldsFromRow($dailyValuesByDate->get($date, [])),
                ]);
            }

            return $report->loadCount('dailyValues')->load('generator');
        });
    }

    public function updateReport(DtrReport $report, array $validated): DtrReport
    {
        $workDays = collect($validated['work_days'])
            ->map(fn ($day) => strtolower((string) $day))
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

        $signatory = $this->resolveSignatory($validated);
        $dailyValuesByDate = collect($validated['daily_values'])
            ->keyBy(fn (array $value) => Carbon::parse($value['work_date'])->toDateString());

        if ($dailyValuesByDate->keys()->sort()->values()->all() !== $expectedDates->sort()->values()->all()) {
            throw ValidationException::withMessages([
                'daily_values' => 'Daily values do not match the selected work schedule and date range.',
            ]);
        }

        return DB::transaction(function () use ($report, $validated, $workDays, $expectedDates, $signatory, $dailyValuesByDate) {
            $report->update([
                ...$signatory,
                'period_start_date' => $validated['period_start_date'],
                'period_end_date' => $validated['period_end_date'],
                'work_days' => $workDays->all(),
            ]);

            $report->dailyValues()->delete();

            foreach ($expectedDates as $date) {
                $report->dailyValues()->create([
                    'work_date' => $date,
                    ...$this->dailyFieldsFromRow($dailyValuesByDate->get($date, [])),
                ]);
            }

            return $report->refresh()->loadCount('dailyValues')->load('generator');
        });
    }

    public function deleteReport(DtrReport $report): void
    {
        DB::transaction(function () use ($report) {
            $report->forceDelete();
        });
    }

    private function dailyValuePayload(DtrReportDailyValue $value): array
    {
        return [
            'work_date' => optional($value->work_date)->toDateString(),
            ...collect(self::TIME_FIELDS)->mapWithKeys(fn ($field) => [$field => $value->{$field}])->all(),
            'undertime_hours' => $value->undertime_hours,
            'undertime_minutes' => $value->undertime_minutes,
            'travel_label' => $value->travel_label,
        ];
    }

    private function dailyFieldsFromRow(array $row): array
    {
        $travelLabel = $this->normalizeTravelLabel($row['travel_label'] ?? null);

        // A travel/OB day has no time-in/out — it's rendered as a merged note instead.
        if ($travelLabel !== null) {
            return [
                ...collect(self::TIME_FIELDS)->mapWithKeys(fn ($field) => [$field => null])->all(),
                'undertime_hours' => 0,
                'undertime_minutes' => 0,
                'travel_label' => $travelLabel,
            ];
        }

        return [
            ...collect(self::TIME_FIELDS)
                ->mapWithKeys(fn ($field) => [$field => $this->normalizeTime($row[$field] ?? null)])
                ->all(),
            'undertime_hours' => $this->normalizeUndertimeUnit($row['undertime_hours'] ?? null),
            'undertime_minutes' => $this->normalizeUndertimeUnit($row['undertime_minutes'] ?? null),
            'travel_label' => null,
        ];
    }

    private function normalizeTime(?string $value): ?string
    {
        $value = trim((string) $value);

        return $value !== '' ? $value : null;
    }

    private function normalizeTravelLabel(?string $value): ?string
    {
        $value = trim((string) $value);

        return $value !== '' ? $value : null;
    }

    private function normalizeUndertimeUnit(mixed $value): int
    {
        return max(0, (int) $value);
    }

    private function resolveSignatory(array $validated): array
    {
        $officeHead = Signatory::query()
            ->where('part', 'A')
            ->findOrFail($validated['office_head_id']);

        $availableTitles = collect($officeHead->title ?? [])
            ->map(fn ($title) => trim((string) $title))
            ->filter()
            ->values();

        $selectedTitles = array_key_exists('signatory_titles', $validated)
            ? $availableTitles
                ->filter(fn ($title) => collect($validated['signatory_titles'] ?? [])->contains($title))
                ->values()
            : $availableTitles;

        return [
            'office_head_signatory_id' => $officeHead->id,
            'signatory_name' => $officeHead->name,
            'signatory_office' => $officeHead->office,
            'signatory_titles' => $selectedTitles->all(),
            'signatory_name_underline' => array_key_exists('signatory_name_underline', $validated)
                ? (bool) $validated['signatory_name_underline']
                : true,
            'signatory_show_designation' => array_key_exists('signatory_show_designation', $validated)
                ? (bool) $validated['signatory_show_designation']
                : true,
            'signatory_show_office' => array_key_exists('signatory_show_office', $validated)
                ? (bool) $validated['signatory_show_office']
                : true,
            'signatory_info_order' => ($validated['signatory_info_order'] ?? 'designation_first') === 'office_first'
                ? 'office_first'
                : 'designation_first',
        ];
    }

    private function expectedWorkDates(string $startDate, string $endDate, Collection $workDays): Collection
    {
        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->startOfDay();
        $allowedDays = $workDays->map(fn ($day) => strtolower((string) $day))->values();
        $blockedDates = CalendarEvent::query()
            ->active()
            ->betweenDates($start->toDateString(), $end->toDateString())
            ->pluck('event_date')
            ->map(fn ($date) => Carbon::parse($date)->toDateString())
            ->unique();

        $dates = collect();
        $cursor = $start->copy();

        while ($cursor->lte($end)) {
            if (
                $allowedDays->contains(strtolower($cursor->englishDayOfWeek))
                && ! $blockedDates->contains($cursor->toDateString())
            ) {
                $dates->push($cursor->toDateString());
            }

            $cursor->addDay();
        }

        return $dates;
    }
}
