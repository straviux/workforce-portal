<template>
    <Dialog :visible="show" @update:visible="value => emit('update:show', value)" modal :draggable="false"
        :closable="false" :pt="{ root: { class: 'ios-dialog-root' }, mask: { class: 'ios-dialog-mask' } }">
        <template #container>
            <div class="ios-modal w-[96vw] max-w-[1450px]" :style="modalStyle">
                <div class="ios-nav-bar cursor-grab active:cursor-grabbing select-none" @pointerdown="onDragStart">
                    <button type="button" class="ios-nav-btn ios-nav-cancel" @click="emit('update:show', false)">
                        <i class="pi pi-times"></i>
                    </button>
                    <span class="ios-nav-title">Employee SWA</span>
                    <div class="min-w-[3.5rem] shrink-0"></div>
                </div>

                <div class="ios-body max-h-[82vh] overflow-y-auto">
                    <div class="ios-section pb-4">
                        <p class="ios-section-label">Select Employee</p>
                        <div class="ios-card p-4 space-y-4">
                            <div class="flex gap-3 flex-wrap">
                                <IconField class="flex-1 min-w-60">
                                    <InputIcon class="pi pi-search" />
                                    <InputText v-model="filters.search" class="w-full"
                                        placeholder="Search employee name, employee no., office, or designation"
                                        @keyup.enter="fetchEmployees" />
                                </IconField>

                                <Button icon="pi pi-search" label="Search" class="rounded" size="small"
                                    :loading="loadingEmployees" @click="fetchEmployees" />
                            </div>

                            <div v-if="employees.length" class="grid gap-3 lg:grid-cols-2">
                                <button v-for="employee in employees" :key="employee.id" type="button"
                                    class="rounded-3xl border px-4 py-4 text-left transition"
                                    :class="selectedEmployeeId === employee.id
                                        ? 'border-sky-500 bg-sky-50 dark:bg-sky-950/20'
                                        : 'border-surface-200 bg-white dark:border-surface-800 dark:bg-surface-900 hover:border-surface-300 dark:hover:border-surface-700'"
                                    @click="selectEmployee(employee.id)">
                                    <div class="flex items-start justify-between gap-3">
                                        <div>
                                            <p class="font-medium text-surface-800 dark:text-surface-100">{{
                                                employee.full_name }}</p>
                                            <p class="text-xs text-surface-400 mt-1">
                                                {{
                                                    [employee.employee_no, employee.office, employee.designation]
                                                        .filter(Boolean)
                                                        .join(' · ') || 'Employee details unavailable'
                                                }}
                                            </p>
                                        </div>

                                        <div class="flex items-center gap-2 flex-wrap justify-end">
                                            <Tag :value="`${employee.active_swa_tasks_count}/5 tasks`"
                                                :severity="employee.active_swa_tasks_count === 5 ? 'success' : 'warning'" />
                                            <Tag :value="`${employee.swa_reports_count} reports`"
                                                severity="secondary" />
                                        </div>
                                    </div>
                                </button>
                            </div>

                            <div v-else class="text-center py-8 text-surface-400">
                                <i class="pi pi-users text-3xl block mb-3"></i>
                                <p class="text-sm">No employee subjects found.</p>
                            </div>
                        </div>
                    </div>

                    <div v-if="loadingSetup" class="ios-section pb-4">
                        <div class="ios-card p-8 text-center text-surface-400">
                            <i class="pi pi-spin pi-spinner text-3xl block mb-3"></i>
                            <p class="text-sm">Loading employee SWA workspace…</p>
                        </div>
                    </div>

                    <SwaWorkspace v-else-if="subject" module-type="employee" :subject="subject" :tasks="tasks"
                        :reports="reports" :calendar-events="calendarEvents" :can-manage="canManageSwa"
                        :is-saving-tasks="savingTasks" :is-saving-report="savingReport" @save-tasks="handleSaveTasks"
                        @save-report="handleSaveReport" />
                </div>
            </div>
        </template>
    </Dialog>
</template>

<script setup>
import { computed, onBeforeUnmount, reactive, ref, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';
import axios from 'axios';
import { useToast } from 'primevue/usetoast';
import SwaWorkspace from '@/Pages/Swa/Modal/SwaWorkspace.vue';

const props = defineProps({
    show: Boolean,
});

const emit = defineEmits(['update:show']);

const toast = useToast();
const page = usePage();

const dragOffset = ref({ x: 0, y: 0 });
const dragStart = ref(null);
const loadingEmployees = ref(false);
const loadingSetup = ref(false);
const savingTasks = ref(false);
const savingReport = ref(false);
const employees = ref([]);
const selectedEmployeeId = ref(null);
const subject = ref(null);
const tasks = ref([]);
const reports = ref([]);
const calendarEvents = ref([]);
const filters = reactive({ search: '' });

const currentPermissions = computed(() => page.props.auth?.user?.permissions ?? []);
const canManageSwa = computed(() => currentPermissions.value.includes('swa.manage'));

const modalStyle = computed(() => ({
    transform: `translate(${dragOffset.value.x}px, ${dragOffset.value.y}px)`,
}));

watch(() => props.show, (visible) => {
    if (!visible) return;

    dragOffset.value = { x: 0, y: 0 };
    fetchEmployees();
});

async function fetchEmployees() {
    loadingEmployees.value = true;

    try {
        const { data } = await axios.get('/api/swa/employees', {
            params: {
                search: filters.search,
                per_page: 12,

            },
        });
        employees.value = data.data ?? [];

        if (!selectedEmployeeId.value && employees.value.length) {
            await selectEmployee(employees.value[0].id);
        }
    } catch (error) {
        toast.add({
            severity: 'error',
            summary: 'Error',
            detail: error.response?.data?.message ?? 'Could not load employees for SWA.',
            life: 4000,
        });
    } finally {
        loadingEmployees.value = false;
    }
}

async function selectEmployee(employeeId) {
    selectedEmployeeId.value = employeeId;
    await fetchSetup();
}

async function fetchSetup() {
    if (!selectedEmployeeId.value) return;

    loadingSetup.value = true;

    try {
        const { data } = await axios.get(`/api/swa/employees/${selectedEmployeeId.value}`);
        subject.value = data.subject ?? null;
        tasks.value = data.tasks ?? [];
        reports.value = data.reports ?? [];
        calendarEvents.value = data.calendar_events ?? [];
    } catch (error) {
        toast.add({
            severity: 'error',
            summary: 'Error',
            detail: error.response?.data?.message ?? 'Could not load employee SWA workspace.',
            life: 4000,
        });
    } finally {
        loadingSetup.value = false;
    }
}

async function handleSaveTasks(payload, options = {}) {
    if (!canManageSwa.value || !selectedEmployeeId.value) return false;

    savingTasks.value = true;

    try {
        await axios.put(`/api/swa/employees/${selectedEmployeeId.value}/tasks`, { tasks: payload });
        await fetchSetup();
        await fetchEmployees();

        if (!options.silent) {
            toast.add({ severity: 'success', summary: 'Saved', detail: 'Employee SWA tasks saved.', life: 3000 });
        }

        return true;
    } catch (error) {
        toast.add({
            severity: 'error',
            summary: 'Error',
            detail: extractErrorMessage(error, 'Could not save employee SWA tasks.'),
            life: 4000,
        });
        return false;
    } finally {
        savingTasks.value = false;
    }
}

async function handleSaveReport(payload) {
    if (!canManageSwa.value || !selectedEmployeeId.value) return;

    savingReport.value = true;

    try {
        const { tasks: taskPayload, ...reportPayload } = payload;
        const tasksSaved = await handleSaveTasks(taskPayload, { silent: true });

        if (!tasksSaved) return;

        await axios.post(`/api/swa/employees/${selectedEmployeeId.value}/reports`, reportPayload);
        await fetchSetup();
        await fetchEmployees();

        toast.add({ severity: 'success', summary: 'Generated', detail: 'Employee SWA generated.', life: 3000 });
    } catch (error) {
        toast.add({
            severity: 'error',
            summary: 'Error',
            detail: extractErrorMessage(error, 'Could not generate employee SWA.'),
            life: 4000,
        });
    } finally {
        savingReport.value = false;
    }
}

function onDragStart(event) {
    if (event.target.closest('button, input, textarea, .p-inputtext, .p-datepicker, .p-select, .p-button')) return;

    dragStart.value = {
        x: event.clientX - dragOffset.value.x,
        y: event.clientY - dragOffset.value.y,
    };

    window.addEventListener('pointermove', onDragMove);
    window.addEventListener('pointerup', onDragEnd);
}

function onDragMove(event) {
    if (!dragStart.value) return;

    dragOffset.value = {
        x: event.clientX - dragStart.value.x,
        y: event.clientY - dragStart.value.y,
    };
}

function onDragEnd() {
    dragStart.value = null;
    window.removeEventListener('pointermove', onDragMove);
    window.removeEventListener('pointerup', onDragEnd);
}

onBeforeUnmount(() => {
    onDragEnd();
});

function extractErrorMessage(error, fallback) {
    const message = error.response?.data?.message;
    const validationErrors = error.response?.data?.errors;

    if (validationErrors) {
        const firstError = Object.values(validationErrors)[0];
        if (Array.isArray(firstError)) {
            return firstError[0] ?? fallback;
        }

        return firstError ?? fallback;
    }

    return message ?? fallback;
}
</script>