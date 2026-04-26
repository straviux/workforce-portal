<template>
    <div class="space-y-4">
        <div class="ios-section pb-4">
            <div class="ios-card p-4 flex items-start justify-between gap-4 flex-wrap">
                <div>
                    <p class="text-sm font-medium text-surface-800 dark:text-surface-100">{{ subject?.display_name ||
                        'No subject selected' }}</p>
                    <p class="text-sm text-surface-500 mt-1">{{ subjectMetaLine }}</p>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <Tag :value="moduleTag" severity="info" />
                    <Tag :value="`${completedTaskCount}/5 tasks`"
                        :severity="completedTaskCount === 5 ? 'success' : 'warning'" />
                </div>
            </div>
        </div>

        <div class="ios-section pb-4">
            <div class="flex items-center justify-between gap-3 flex-wrap mb-2">
                <p class="ios-section-label !mb-0">Task Encoding</p>
                <Button label="Save Tasks" icon="pi pi-save" class="rounded" size="small" :loading="isSavingTasks"
                    :disabled="!canManage || !hasValidTasks || isSavingReport" @click="emitSaveTasks" />
            </div>

            <div class="ios-card p-4 space-y-3">
                <p class="text-xs text-surface-400">
                    Exactly 5 tasks are required for each subject. Task type can be countable or check/blank (-).
                </p>

                <div v-for="row in taskRows" :key="row.sort_order"
                    class="grid gap-3 md:grid-cols-[auto_minmax(0,1fr)_220px] items-start">
                    <Tag :value="`Task ${row.sort_order}`" severity="secondary" />

                    <div class="ios-form-group">
                        <InputText v-model="row.task_name" class="w-full" placeholder="Enter task description"
                            :disabled="!canManage || isSavingTasks || isSavingReport" />
                    </div>

                    <div class="ios-form-group">
                        <Select v-model="row.task_type" :options="taskTypeOptions" optionLabel="label"
                            optionValue="value" class="w-full"
                            :disabled="!canManage || isSavingTasks || isSavingReport" />
                    </div>
                </div>
            </div>
        </div>

        <div class="ios-section pb-4">
            <div class="flex items-center justify-between gap-3 flex-wrap mb-2">
                <p class="ios-section-label !mb-0">Generate SWA</p>
                <div class="flex items-center gap-2 flex-wrap">
                    <Button label="Prepare Daily Values" icon="pi pi-sparkles" severity="secondary" outlined
                        class="rounded" size="small" :disabled="!canPrepareDraft || isSavingReport"
                        @click="prepareDraftValues" />
                    <Button label="Save SWA" icon="pi pi-check" class="rounded" size="small" :loading="isSavingReport"
                        :disabled="!canManage || !draftRows.length || isSavingTasks" @click="emitSaveReport" />
                </div>
            </div>

            <div class="ios-card p-4 space-y-4">
                <div>
                    <p class="text-xs uppercase tracking-[0.2em] text-surface-400 mb-2">Work Schedule</p>
                    <div class="flex flex-wrap gap-2">
                        <button v-for="day in weekdayOptions" :key="day.value" type="button"
                            class="rounded-2xl px-3 py-2 text-sm border transition"
                            :class="generation.work_days.includes(day.value)
                                ? 'border-sky-500 bg-sky-500 text-white'
                                : 'border-surface-200 bg-white text-surface-600 dark:border-surface-700 dark:bg-surface-900 dark:text-surface-300'"
                            :disabled="!canManage || isSavingReport || isSavingTasks" @click="toggleWorkDay(day.value)">
                            {{ day.label }}
                        </button>
                    </div>
                </div>

                <div class="grid gap-3 md:grid-cols-2">
                    <div class="ios-form-group">
                        <label class="ios-label">Start Date</label>
                        <DatePicker v-model="generation.period_start_date" class="w-full" showIcon fluid
                            dateFormat="MM d, yy" :disabled="!canManage || isSavingReport || isSavingTasks" />
                    </div>

                    <div class="ios-form-group">
                        <label class="ios-label">End Date</label>
                        <DatePicker v-model="generation.period_end_date" class="w-full" showIcon fluid
                            dateFormat="MM d, yy" :disabled="!canManage || isSavingReport || isSavingTasks" />
                    </div>
                </div>

                <div v-if="blockedEventsInRange.length"
                    class="rounded-2xl border border-amber-200 bg-amber-50 dark:border-amber-400/20 dark:bg-amber-950/20 p-4">
                    <p class="text-sm font-medium text-amber-900 dark:text-amber-100">Excluded Calendar Dates</p>
                    <p class="text-sm text-amber-800/80 dark:text-amber-100/80 mt-1">
                        These holiday or suspension dates will be skipped from the SWA draft.
                    </p>
                    <div class="mt-3 flex flex-wrap gap-2">
                        <Tag v-for="event in blockedEventsInRange" :key="`${event.event_date}-${event.title}`"
                            :value="`${formatShortDate(event.event_date)} · ${event.title}`" severity="warn" />
                    </div>
                </div>
            </div>
        </div>

        <div v-if="draftRows.length" class="ios-section pb-4">
            <p class="ios-section-label">Daily Values Draft</p>
            <div class="ios-card p-4">
                <p class="text-xs text-surface-400 mb-3">
                    Default values are randomized for now. You can edit them before saving the SWA record.
                </p>

                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm border-separate border-spacing-0">
                        <thead>
                            <tr>
                                <th
                                    class="sticky left-0 bg-surface-0 dark:bg-surface-950 text-left px-3 py-2 border-b border-surface-200 dark:border-surface-800 min-w-[260px]">
                                    Task</th>
                                <th v-for="workDate in draftDates" :key="workDate"
                                    class="px-3 py-2 border-b border-surface-200 dark:border-surface-800 text-center whitespace-nowrap min-w-[110px]">
                                    {{ formatShortDate(workDate) }}
                                </th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr v-for="row in draftRows" :key="row.sort_order">
                                <td
                                    class="sticky left-0 bg-surface-0 dark:bg-surface-950 px-3 py-3 border-b border-surface-100 dark:border-surface-900 align-top">
                                    <p class="font-medium text-surface-800 dark:text-surface-100">{{ row.task_name }}
                                    </p>
                                    <p class="text-xs text-surface-400 mt-1">{{ formatTaskType(row.task_type) }}</p>
                                </td>

                                <td v-for="cell in row.daily_values" :key="`${row.sort_order}-${cell.work_date}`"
                                    class="px-3 py-3 border-b border-surface-100 dark:border-surface-900 text-center align-middle">
                                    <input v-if="row.task_type === 'countable'" v-model.number="cell.numeric_value"
                                        type="number" min="0" step="0.01"
                                        class="w-24 rounded-xl border border-surface-200 dark:border-surface-700 bg-white dark:bg-surface-900 px-2 py-1 text-right"
                                        :disabled="!canManage || isSavingReport" />

                                    <button v-else type="button"
                                        class="rounded-xl px-3 py-1 text-xs font-medium border transition min-w-[72px]"
                                        :class="cell.mark_value === 'check'
                                            ? 'border-emerald-500 bg-emerald-500 text-white'
                                            : 'border-surface-300 bg-surface-100 text-surface-600 dark:border-surface-700 dark:bg-surface-900 dark:text-surface-300'"
                                        :disabled="!canManage || isSavingReport" @click="toggleMarkValue(cell)">
                                        {{ cell.mark_value === 'check' ? 'Check' : '-' }}
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="ios-section pb-4">
            <p class="ios-section-label">Generated SWA Records</p>
            <div class="ios-card p-4">
                <div v-if="reports.length" class="space-y-3">
                    <div v-for="report in reports" :key="report.id"
                        class="rounded-2xl border border-surface-200 dark:border-surface-800 px-4 py-3 flex items-start justify-between gap-3 flex-wrap">
                        <div>
                            <p class="text-sm font-medium text-surface-800 dark:text-surface-100">
                                {{ formatReportRange(report) }}
                            </p>
                            <p class="text-xs text-surface-400 mt-1">
                                {{ (report.work_days || []).map(formatDayLabel).join(', ') }}
                                <span v-if="report.generated_by_name">· Generated by {{ report.generated_by_name
                                    }}</span>
                            </p>
                        </div>

                        <div class="flex items-center gap-2 flex-wrap">
                            <Tag :value="`${report.task_count} tasks`" severity="secondary" />
                            <Tag :value="formatCreatedAt(report.created_at)" severity="info" />
                        </div>
                    </div>
                </div>

                <div v-else class="text-center py-8 text-surface-400">
                    <i class="pi pi-calendar-clock text-3xl block mb-3"></i>
                    <p class="text-sm">No SWA records generated yet.</p>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import dayjs from 'dayjs';
import { computed, reactive, ref, watch } from 'vue';

const props = defineProps({
    moduleType: { type: String, required: true },
    subject: { type: Object, default: null },
    tasks: { type: Array, default: () => [] },
    reports: { type: Array, default: () => [] },
    calendarEvents: { type: Array, default: () => [] },
    canManage: { type: Boolean, default: false },
    isSavingTasks: { type: Boolean, default: false },
    isSavingReport: { type: Boolean, default: false },
});

const emit = defineEmits(['save-tasks', 'save-report']);

const weekdayOptions = [
    { label: 'Mon', value: 'monday' },
    { label: 'Tue', value: 'tuesday' },
    { label: 'Wed', value: 'wednesday' },
    { label: 'Thu', value: 'thursday' },
    { label: 'Fri', value: 'friday' },
    { label: 'Sat', value: 'saturday' },
    { label: 'Sun', value: 'sunday' },
];

const taskTypeOptions = [
    { label: 'Countable', value: 'countable' },
    { label: 'Check / Blank (-)', value: 'check_blank' },
];

const taskRows = ref(createEmptyTaskRows());
const draftRows = ref([]);
const draftDates = ref([]);
const generation = reactive({
    work_days: ['monday', 'tuesday', 'wednesday', 'thursday'],
    period_start_date: null,
    period_end_date: null,
});

const moduleTag = computed(() => props.moduleType === 'personal' ? 'Personal' : 'Employee');
const completedTaskCount = computed(() => normalizedTasks.value.filter((task) => task.task_name).length);
const hasValidTasks = computed(() => completedTaskCount.value === 5);
const canPrepareDraft = computed(() => props.canManage
    && hasValidTasks.value
    && generation.work_days.length > 0
    && generation.period_start_date
    && generation.period_end_date
    && !dayjs(generation.period_end_date).isBefore(dayjs(generation.period_start_date), 'day'));
const blockedEventsInRange = computed(() => {
    if (!generation.period_start_date || !generation.period_end_date) return [];

    const start = dayjs(generation.period_start_date).startOf('day');
    const end = dayjs(generation.period_end_date).endOf('day');

    return (props.calendarEvents ?? [])
        .filter((event) => {
            const eventDate = dayjs(event.event_date);
            return eventDate.isValid() && !eventDate.isBefore(start, 'day') && !eventDate.isAfter(end, 'day');
        })
        .sort((left, right) => left.event_date.localeCompare(right.event_date));
});
const blockedDateSet = computed(() => new Set(blockedEventsInRange.value.map((event) => event.event_date)));
const normalizedTasks = computed(() => taskRows.value.map((row) => ({
    sort_order: row.sort_order,
    task_name: String(row.task_name ?? '').trim(),
    task_type: row.task_type,
})));
const subjectMetaLine = computed(() => {
    if (!props.subject) return 'Select a subject to encode tasks and generate SWA.';

    const parts = [props.subject.employee_no, props.subject.office, props.subject.designation || props.subject.secondary_label]
        .filter(Boolean);

    return parts.join(' · ') || 'SWA subject ready.';
});

watch(() => props.tasks, (tasks) => {
    taskRows.value = createEmptyTaskRows();

    (tasks ?? []).forEach((task) => {
        const index = Math.max(0, Math.min(4, Number(task.sort_order ?? 1) - 1));
        taskRows.value[index] = {
            sort_order: index + 1,
            task_name: task.task_name ?? '',
            task_type: task.task_type ?? 'countable',
        };
    });
}, { immediate: true, deep: true });

watch(
    [taskRows, () => generation.work_days.slice(), () => generation.period_start_date, () => generation.period_end_date],
    () => {
        draftRows.value = [];
        draftDates.value = [];
    },
    { deep: true },
);

function emitSaveTasks() {
    emit('save-tasks', normalizedTasks.value);
}

function emitSaveReport() {
    emit('save-report', {
        tasks: normalizedTasks.value,
        period_start_date: formatDateForApi(generation.period_start_date),
        period_end_date: formatDateForApi(generation.period_end_date),
        work_days: [...generation.work_days],
        draft_rows: draftRows.value.map((row) => ({
            sort_order: row.sort_order,
            daily_values: row.daily_values.map((value) => ({
                work_date: value.work_date,
                numeric_value: row.task_type === 'countable' ? Number(value.numeric_value ?? 0) : null,
                mark_value: row.task_type === 'check_blank' ? value.mark_value : null,
            })),
        })),
    });
}

function toggleWorkDay(day) {
    if (!props.canManage || props.isSavingTasks || props.isSavingReport) return;

    if (generation.work_days.includes(day)) {
        generation.work_days = generation.work_days.filter((value) => value !== day);
        return;
    }

    generation.work_days = weekdayOptions
        .map((option) => option.value)
        .filter((value) => value === day || generation.work_days.includes(value));
}

function prepareDraftValues() {
    if (!canPrepareDraft.value) return;

    const workDates = buildWorkDates();
    draftDates.value = workDates;
    draftRows.value = normalizedTasks.value.map((task) => ({
        ...task,
        daily_values: workDates.map((workDate) => ({
            work_date: workDate,
            numeric_value: task.task_type === 'countable' ? randomCountableValue() : null,
            mark_value: task.task_type === 'check_blank' ? randomMarkValue() : null,
        })),
    }));
}

function buildWorkDates() {
    const start = dayjs(generation.period_start_date);
    const end = dayjs(generation.period_end_date);
    const selectedDays = new Set(generation.work_days);
    const dates = [];
    let cursor = start.startOf('day');

    while (!cursor.isAfter(end, 'day')) {
        const currentDate = cursor.format('YYYY-MM-DD');

        if (selectedDays.has(cursor.format('dddd').toLowerCase()) && !blockedDateSet.value.has(currentDate)) {
            dates.push(currentDate);
        }

        cursor = cursor.add(1, 'day');
    }

    return dates;
}

function toggleMarkValue(cell) {
    if (!props.canManage || props.isSavingReport) return;

    cell.mark_value = cell.mark_value === 'check' ? 'dash' : 'check';
}

function createEmptyTaskRows() {
    return Array.from({ length: 5 }, (_, index) => ({
        sort_order: index + 1,
        task_name: '',
        task_type: 'countable',
    }));
}

function formatDateForApi(value) {
    return value ? dayjs(value).format('YYYY-MM-DD') : '';
}

function formatShortDate(value) {
    return dayjs(value).format('MMM D');
}

function formatTaskType(value) {
    return value === 'check_blank' ? 'Check / Blank (-)' : 'Countable';
}

function formatDayLabel(value) {
    const match = weekdayOptions.find((option) => option.value === value);
    return match?.label ?? value;
}

function formatReportRange(report) {
    return `${dayjs(report.period_start_date).format('MMM D, YYYY')} - ${dayjs(report.period_end_date).format('MMM D, YYYY')}`;
}

function formatCreatedAt(value) {
    return value ? dayjs(value).format('MMM D, YYYY h:mm A') : 'Saved';
}

function randomCountableValue() {
    return Math.floor(Math.random() * 9) + 1;
}

function randomMarkValue() {
    return Math.random() >= 0.35 ? 'check' : 'dash';
}
</script>