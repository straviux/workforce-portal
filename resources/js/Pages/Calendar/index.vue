<template>

    <Head title="Calendar" />

    <div class="flex items-center justify-between mb-5 gap-4 flex-wrap">
        <div>
            <h1 class="text-xl font-bold text-surface-800 dark:text-surface-50">Calendar</h1>
            <p class="text-sm text-surface-400 mt-0.5">
                Maintain legal holidays, local holidays, and work suspensions used by SWA generation.
            </p>
        </div>

        <Button v-if="canManageCalendar" label="Add Calendar Event" icon="pi pi-plus" class="rounded"
            @click="openCreate" />
    </div>

    <div class="ios-section pb-4">
        <div class="ios-card p-4 flex items-start justify-between gap-4 flex-wrap">
            <div>
                <p class="text-sm font-medium text-surface-800 dark:text-surface-100">SWA Blocking Calendar</p>
                <p class="text-sm text-surface-500 mt-1">
                    Active calendar dates are excluded from SWA daily drafts. Seeded nationwide legal holidays stay
                    locked.
                </p>
            </div>

            <div class="flex items-center gap-2 flex-wrap">
                <Tag :value="`${pagination.filtered_total} event${pagination.filtered_total === 1 ? '' : 's'}`"
                    severity="info" />
                <Tag value="System holidays locked" severity="warn" />
            </div>
        </div>
    </div>

    <div class="ios-section pb-4">
        <div class="flex items-center justify-between gap-3 flex-wrap mb-2">
            <p class="ios-section-label !mb-0">Month View</p>
            <div class="flex items-center gap-1.5 sm:gap-2 flex-wrap w-full sm:w-auto justify-between sm:justify-start">
                <Button icon="pi pi-angle-left" severity="secondary" outlined class="rounded" size="small"
                    @click="shiftMonth(-1)" />
                <div
                    class="rounded-2xl border border-surface-200 dark:border-surface-700 px-2.5 sm:px-3 py-1.5 sm:py-2 text-xs sm:text-sm font-medium text-surface-700 dark:text-surface-100 min-w-[132px] sm:min-w-[170px] text-center">
                    {{ monthLabel }}
                </div>
                <Button icon="pi pi-angle-right" severity="secondary" outlined class="rounded" size="small"
                    @click="shiftMonth(1)" />
                <Button label="Today" severity="secondary" text class="rounded" size="small"
                    @click="goToCurrentMonth" />
            </div>
        </div>

        <div class="ios-card p-2.5 sm:p-3 space-y-3 overflow-hidden">
            <div class="flex items-center gap-2 flex-wrap text-xs text-surface-500">
                <Tag value="Active blocks SWA" severity="success" />
                <Tag value="Inactive kept for record" severity="secondary" />
                <Tag value="System locked" severity="warn" />
            </div>

            <div v-if="loadingMonth" class="text-center py-10 text-surface-400">
                <i class="pi pi-spin pi-spinner text-3xl block mb-3"></i>
                <p class="text-sm">Loading month view…</p>
            </div>

            <div v-else class="overflow-x-auto pb-1">
                <div class="space-y-1.5 min-w-[620px] lg:min-w-0">
                    <div class="grid grid-cols-7 gap-1.5 sm:gap-2">
                        <div v-for="weekday in weekdayHeaders" :key="weekday"
                            class="px-1.5 sm:px-2 py-1.5 text-[10px] sm:text-[11px] font-semibold uppercase tracking-[0.12em] text-surface-400 text-center">
                            {{ weekday }}
                        </div>
                    </div>

                    <div class="grid grid-cols-7 gap-1.5 sm:gap-2 auto-rows-fr">
                        <div v-for="day in calendarCells" :key="day.isoDate"
                            class="rounded-2xl border min-h-[74px] sm:min-h-[88px] lg:min-h-[98px] p-1.5 sm:p-2 flex flex-col gap-1.5"
                            :class="[
                                day.isCurrentMonth
                                    ? 'border-surface-200 bg-white dark:border-surface-800 dark:bg-surface-950'
                                    : 'border-surface-100 bg-surface-50/80 text-surface-400 dark:border-surface-900 dark:bg-surface-900/70',
                                day.isToday ? 'ring-2 ring-sky-400/60' : '',
                            ]">
                            <div class="flex items-start justify-between gap-1 sm:gap-2">
                                <div class="min-w-0">
                                    <p class="text-[9px] sm:text-[10px] uppercase tracking-[0.1em]"
                                        :class="day.isCurrentMonth ? 'text-surface-400' : 'text-surface-300 dark:text-surface-600'">
                                        {{ day.dayName }}
                                    </p>
                                    <p class="text-xs sm:text-sm lg:text-base font-semibold leading-tight"
                                        :class="day.isCurrentMonth ? 'text-surface-800 dark:text-surface-100' : 'text-surface-400 dark:text-surface-500'">
                                        {{ day.dayNumber }}
                                    </p>
                                </div>

                                <Tag v-if="day.events.length" :value="`${day.events.length}`" severity="info"
                                    class="shrink-0 scale-90 origin-top-right" />
                            </div>

                            <div v-if="day.events.length" class="space-y-1 flex-1 overflow-hidden">
                                <button v-for="event in day.events.slice(0, 2)" :key="event.id" type="button"
                                    class="w-full rounded-xl border px-1.5 py-1 sm:px-2 sm:py-1.5 text-left transition"
                                    :class="monthEventCardClass(event)"
                                    :disabled="!canManageCalendar || event.is_system"
                                    @click="event.is_system ? null : openEdit(event)">
                                    <div class="flex items-start justify-between gap-2">
                                        <div class="min-w-0">
                                            <p class="text-[10px] sm:text-[11px] font-semibold truncate leading-tight"
                                                :class="event.is_active ? '' : 'line-through opacity-70'">
                                                {{ event.title }}
                                            </p>
                                            <p class="text-[9px] sm:text-[10px] mt-0.5 opacity-80 truncate">{{
                                                formatEventType(event.event_type) }}</p>
                                        </div>
                                        <i v-if="event.is_system"
                                            class="pi pi-lock text-[9px] sm:text-[10px] opacity-70 mt-0.5 shrink-0"></i>
                                    </div>
                                </button>

                                <p v-if="day.events.length > 2"
                                    class="px-1 text-[9px] sm:text-[10px] font-medium text-surface-400">
                                    +{{ day.events.length - 2 }} more
                                </p>
                            </div>

                            <div v-else
                                class="flex-1 rounded-xl border border-dashed border-surface-200 dark:border-surface-800 min-h-[20px]">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="ios-section pb-4">
        <p class="ios-section-label">Filters</p>
        <div class="ios-card p-4 space-y-3">
            <div class="grid gap-3 xl:grid-cols-[minmax(0,1.4fr)_220px_220px_220px_auto] items-end">
                <div class="ios-form-group">
                    <label class="ios-label">Search</label>
                    <IconField>
                        <InputIcon class="pi pi-search" />
                        <InputText v-model="filters.search" class="w-full" placeholder="Search title or description"
                            @keyup.enter="fetchEvents(1)" />
                    </IconField>
                </div>

                <div class="ios-form-group">
                    <label class="ios-label">Event Type</label>
                    <Select v-model="filters.event_type" :options="eventTypeOptions" optionLabel="label"
                        optionValue="value" class="w-full" />
                </div>

                <div class="ios-form-group">
                    <label class="ios-label">From Date</label>
                    <DatePicker v-model="filters.start_date" class="w-full" showIcon fluid dateFormat="MM d, yy" />
                </div>

                <div class="ios-form-group">
                    <label class="ios-label">To Date</label>
                    <DatePicker v-model="filters.end_date" class="w-full" showIcon fluid dateFormat="MM d, yy" />
                </div>

                <div class="flex items-center gap-2 justify-end">
                    <Button icon="pi pi-search" label="Apply" class="rounded" size="small" @click="fetchEvents(1)" />
                    <Button icon="pi pi-filter-slash" severity="secondary" outlined class="rounded" size="small"
                        @click="resetFilters" />
                </div>
            </div>
        </div>
    </div>

    <div class="ios-section pb-4">
        <p class="ios-section-label">Calendar Events</p>
        <div class="overflow-hidden ios-card"
            style="border-radius:1.5rem;border:1px solid var(--p-datatable-border-color);">
            <DataTable :value="events" :loading="loading" showGridlines stripedRows scrollable lazy
                :totalRecords="pagination.filtered_total" :rows="pagination.per_page" :first="paginatorFirst" paginator
                @page="onPage" :rowsPerPageOptions="[15, 25, 50]" scrollHeight="62vh"
                paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink RowsPerPageDropdown"
                :pt="{
                    root: { style: 'border-radius:0;border:none;' },
                    tableContainer: { style: 'border-radius:0;' },
                    paginator: { style: 'border:none;border-top:1px solid var(--p-datatable-border-color);' }
                }">
                <template #empty>
                    <div class="text-center py-12 text-surface-400">
                        <i class="pi pi-calendar text-4xl block mb-3"></i>
                        <p class="text-sm">No calendar events found.</p>
                    </div>
                </template>

                <Column header="Date" style="min-width:150px;">
                    <template #body="{ data }">
                        <div>
                            <p class="font-medium text-surface-800 dark:text-surface-100">{{ formatDate(data.event_date)
                            }}</p>
                            <p class="text-xs text-surface-400 mt-1">{{ formatDayName(data.event_date) }}</p>
                        </div>
                    </template>
                </Column>

                <Column field="title" header="Title" style="min-width:240px;">
                    <template #body="{ data }">
                        <div>
                            <p class="font-medium text-surface-800 dark:text-surface-100">{{ data.title }}</p>
                            <p class="text-xs text-surface-400 mt-1 line-clamp-2">{{ data.description || `No description
                                provided.` }}</p>
                        </div>
                    </template>
                </Column>

                <Column header="Type" style="min-width:180px;">
                    <template #body="{ data }">
                        <Tag :value="formatEventType(data.event_type)" :severity="eventTypeSeverity(data.event_type)" />
                    </template>
                </Column>

                <Column header="Origin" style="min-width:170px;">
                    <template #body="{ data }">
                        <div class="flex flex-wrap gap-2">
                            <Tag :value="data.is_system ? 'System' : 'Manual'"
                                :severity="data.is_system ? 'warn' : 'secondary'" />
                            <Tag :value="data.is_active ? 'Active' : 'Inactive'"
                                :severity="data.is_active ? 'success' : 'secondary'" />
                        </div>
                    </template>
                </Column>

                <Column header="Created By" style="min-width:170px;">
                    <template #body="{ data }">
                        <div>
                            <p class="text-surface-700 dark:text-surface-200">{{ data.creator?.name || 'System' }}</p>
                            <p class="text-xs text-surface-400 mt-1">{{ formatDateTime(data.created_at) }}</p>
                        </div>
                    </template>
                </Column>

                <Column header="" style="width:190px;min-width:190px;" frozen alignFrozen="right">
                    <template #body="{ data }">
                        <div class="flex items-center justify-end gap-2">
                            <Button v-if="canManageCalendar && !data.is_system"
                                :icon="data.is_active ? 'pi pi-eye-slash' : 'pi pi-eye'"
                                :severity="data.is_active ? 'secondary' : 'success'" text rounded size="small"
                                :disabled="statusUpdatingIds.includes(data.id)" @click="toggleActive(data)" />
                            <Button v-if="canManageCalendar && !data.is_system" icon="pi pi-pencil" severity="secondary"
                                text rounded size="small" @click="openEdit(data)" />
                            <Button v-if="canManageCalendar && !data.is_system" icon="pi pi-trash" severity="danger"
                                text rounded size="small" @click="deleteEvent(data)" />
                            <Tag v-else value="Locked" severity="warn" />
                        </div>
                    </template>
                </Column>
            </DataTable>
        </div>
    </div>

    <CalendarEventModal v-model:show="showEventModal" :mode="eventModalMode" :event="selectedEvent"
        @saved="handleSaved" />
</template>

<script setup>
import dayjs from 'dayjs';
import { computed, onMounted, reactive, ref } from 'vue';
import { Head, usePage } from '@inertiajs/vue3';
import axios from 'axios';
import { useToast } from 'primevue/usetoast';
import WorkforceLayout from '@/Layouts/WorkforceLayout.vue';
import CalendarEventModal from '@/Pages/Calendar/Modal/CalendarEventModal.vue';

defineOptions({ layout: WorkforceLayout });

const toast = useToast();
const page = usePage();

const loading = ref(false);
const loadingMonth = ref(false);
const events = ref([]);
const monthEvents = ref([]);
const showEventModal = ref(false);
const eventModalMode = ref('create');
const selectedEvent = ref(null);
const monthCursor = ref(dayjs().startOf('month'));
const statusUpdatingIds = ref([]);

const filters = reactive({
    search: '',
    event_type: '',
    start_date: null,
    end_date: null,
});

const pagination = reactive({
    filtered_total: 0,
    per_page: 15,
    current_page: 1,
    last_page: 1,
});

const eventTypeOptions = [
    { label: 'All Types', value: '' },
    { label: 'Legal Holiday', value: 'legal_holiday' },
    { label: 'Local Holiday', value: 'local_holiday' },
    { label: 'Work Suspension', value: 'work_suspension' },
];

const paginatorFirst = computed(() => (pagination.current_page - 1) * pagination.per_page);
const currentPermissions = computed(() => page.props.auth?.user?.permissions ?? []);
const canManageCalendar = computed(() => currentPermissions.value.includes('calendar.manage'));
const weekdayHeaders = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
const monthLabel = computed(() => monthCursor.value.format('MMMM YYYY'));
const monthEventMap = computed(() => monthEvents.value.reduce((map, event) => {
    const key = dayjs(event.event_date).format('YYYY-MM-DD');
    if (!map[key]) {
        map[key] = [];
    }

    map[key].push(event);
    map[key].sort((left, right) => {
        if (left.is_active !== right.is_active) {
            return left.is_active ? -1 : 1;
        }

        return String(left.title).localeCompare(String(right.title));
    });

    return map;
}, {}));
const calendarCells = computed(() => {
    const start = monthCursor.value.startOf('month').startOf('week');
    const end = monthCursor.value.endOf('month').endOf('week');
    const cells = [];
    let cursor = start;

    while (cursor.isBefore(end) || cursor.isSame(end, 'day')) {
        const isoDate = cursor.format('YYYY-MM-DD');

        cells.push({
            isoDate,
            dayNumber: cursor.date(),
            dayName: cursor.format('ddd'),
            isCurrentMonth: cursor.isSame(monthCursor.value, 'month'),
            isToday: cursor.isSame(dayjs(), 'day'),
            events: monthEventMap.value[isoDate] ?? [],
        });

        cursor = cursor.add(1, 'day');
    }

    return cells;
});

onMounted(() => {
    fetchEvents();
    fetchMonthEvents();
});

async function fetchEvents(pageNumber = pagination.current_page) {
    loading.value = true;

    try {
        const params = {
            page: pageNumber,
            per_page: pagination.per_page,
        };

        if (filters.search) params.search = filters.search;
        if (filters.event_type) params.event_type = filters.event_type;
        if (filters.start_date) params.start_date = formatDateForApi(filters.start_date);
        if (filters.end_date) params.end_date = formatDateForApi(filters.end_date);

        const { data } = await axios.get('/api/calendar', { params });

        events.value = data.data ?? [];
        pagination.filtered_total = data.filtered_total ?? 0;
        pagination.per_page = data.per_page ?? 15;
        pagination.current_page = data.current_page ?? 1;
        pagination.last_page = data.last_page ?? 1;
    } catch (error) {
        toast.add({
            severity: 'error',
            summary: 'Error',
            detail: error.response?.data?.message ?? 'Could not load calendar events.',
            life: 4000,
        });
    } finally {
        loading.value = false;
    }
}

async function fetchMonthEvents(targetMonth = monthCursor.value) {
    loadingMonth.value = true;

    try {
        const { data } = await axios.get('/api/calendar', {
            params: {
                page: 1,
                per_page: 50,
                start_date: targetMonth.startOf('month').format('YYYY-MM-DD'),
                end_date: targetMonth.endOf('month').format('YYYY-MM-DD'),
            },
        });

        monthEvents.value = data.data ?? [];
    } catch (error) {
        toast.add({
            severity: 'error',
            summary: 'Error',
            detail: error.response?.data?.message ?? 'Could not load month calendar view.',
            life: 4000,
        });
    } finally {
        loadingMonth.value = false;
    }
}

function openCreate() {
    selectedEvent.value = null;
    eventModalMode.value = 'create';
    showEventModal.value = true;
}

function openEdit(event) {
    selectedEvent.value = event;
    eventModalMode.value = 'edit';
    showEventModal.value = true;
}

async function handleSaved() {
    await fetchEvents(pagination.current_page);
    await fetchMonthEvents();
}

async function deleteEvent(event) {
    if (!canManageCalendar.value || event.is_system) return;

    const confirmed = window.confirm(`Delete calendar event "${event.title}"?`);
    if (!confirmed) return;

    try {
        await axios.delete(`/api/calendar/${event.id}`);
        toast.add({ severity: 'success', summary: 'Deleted', detail: 'Calendar event deleted.', life: 3000 });

        const targetPage = events.value.length === 1 && pagination.current_page > 1
            ? pagination.current_page - 1
            : pagination.current_page;

        await fetchEvents(targetPage);
        await fetchMonthEvents();
    } catch (error) {
        toast.add({
            severity: 'error',
            summary: 'Error',
            detail: error.response?.data?.message ?? 'Could not delete calendar event.',
            life: 4000,
        });
    }
}

async function toggleActive(event) {
    if (!canManageCalendar.value || event.is_system) return;

    statusUpdatingIds.value = [...statusUpdatingIds.value, event.id];

    try {
        await axios.put(`/api/calendar/${event.id}`, {
            event_date: dayjs(event.event_date).format('YYYY-MM-DD'),
            title: event.title,
            event_type: event.event_type,
            description: event.description,
            is_active: !event.is_active,
        });

        toast.add({
            severity: 'success',
            summary: 'Updated',
            detail: `Calendar event ${event.is_active ? 'deactivated' : 'activated'}.`,
            life: 3000,
        });

        await fetchEvents(pagination.current_page);
        await fetchMonthEvents();
    } catch (error) {
        toast.add({
            severity: 'error',
            summary: 'Error',
            detail: error.response?.data?.message ?? 'Could not update calendar event status.',
            life: 4000,
        });
    } finally {
        statusUpdatingIds.value = statusUpdatingIds.value.filter((id) => id !== event.id);
    }
}

function onPage(event) {
    pagination.per_page = event.rows;
    fetchEvents(event.page + 1);
}

function shiftMonth(value) {
    monthCursor.value = monthCursor.value.add(value, 'month').startOf('month');
    fetchMonthEvents();
}

function goToCurrentMonth() {
    monthCursor.value = dayjs().startOf('month');
    fetchMonthEvents();
}

function resetFilters() {
    filters.search = '';
    filters.event_type = '';
    filters.start_date = null;
    filters.end_date = null;
    fetchEvents(1);
}

function formatEventType(value) {
    return {
        legal_holiday: 'Legal Holiday',
        local_holiday: 'Local Holiday',
        work_suspension: 'Work Suspension',
    }[value] ?? value;
}

function eventTypeSeverity(value) {
    return {
        legal_holiday: 'danger',
        local_holiday: 'info',
        work_suspension: 'warn',
    }[value] ?? 'secondary';
}

function formatDate(value) {
    return value ? dayjs(value).format('MMM D, YYYY') : '—';
}

function formatDayName(value) {
    return value ? dayjs(value).format('dddd') : '—';
}

function formatDateTime(value) {
    return value ? dayjs(value).format('MMM D, YYYY h:mm A') : 'Saved';
}

function formatDateForApi(value) {
    if (!value) return '';
    return dayjs(value).format('YYYY-MM-DD');
}

function monthEventCardClass(event) {
    if (!event.is_active) {
        return 'border-surface-200 bg-surface-100 text-surface-500 dark:border-surface-700 dark:bg-surface-900 dark:text-surface-400';
    }

    return {
        legal_holiday: 'border-red-200 bg-red-50 text-red-900 dark:border-red-400/20 dark:bg-red-950/20 dark:text-red-100',
        local_holiday: 'border-sky-200 bg-sky-50 text-sky-900 dark:border-sky-400/20 dark:bg-sky-950/20 dark:text-sky-100',
        work_suspension: 'border-amber-200 bg-amber-50 text-amber-900 dark:border-amber-400/20 dark:bg-amber-950/20 dark:text-amber-100',
    }[event.event_type] ?? 'border-surface-200 bg-white text-surface-800 dark:border-surface-800 dark:bg-surface-950 dark:text-surface-100';
}
</script>