<template>
    <Dialog :visible="show" @update:visible="value => emit('update:show', value)" modal :draggable="false"
        :closable="false" :closeOnEscape="false" :pt="{ root: { class: 'ios-dialog-root' }, mask: { class: 'ios-dialog-mask' } }">
        <template #container>
            <div class="ios-modal w-[96vw] max-w-[1380px]" :style="modalStyle">
                <div class="ios-nav-bar cursor-grab active:cursor-grabbing select-none" @pointerdown="onDragStart">
                    <button type="button" class="ios-nav-btn ios-nav-cancel" @click="emit('update:show', false)">
                        <AppIcon name="X" />
                    </button>
                    <span class="ios-nav-title">Personal SWA</span>
                    <div class="min-w-[3.5rem] shrink-0"></div>
                </div>

                <div class="ios-body max-h-[82vh] overflow-y-auto">
                    <div v-if="loadingSetup" class="ios-section pb-4">
                        <div class="ios-card p-8 text-center text-surface-400">
                            <AppIcon name="LoaderCircle" :size="36" class="animate-spin block mb-3" />
                            <p class="text-sm">Loading personal SWA workspace…</p>
                        </div>
                    </div>

                    <SwaWorkspace v-else module-type="personal" :subject="subject" :tasks="tasks" :reports="reports"
                        :calendar-events="calendarEvents" :office-heads="officeHeads" :can-manage="canManageSwa"
                        :is-saving-tasks="isSavingTasks" :is-saving-report="isSavingReport"
                        :is-deleting-report="deletingReport" :tasks-action="tasksActionState"
                        :save-action="saveReportActionState" :update-action="updateReportActionState"
                        @save-tasks="handleSaveTasks"
                        @save-report="handleSaveReport" @update-report="handleUpdateReport"
                        @delete-report="handleDeleteReport" />
                </div>
            </div>
        </template>
    </Dialog>
</template>

<script setup>
import { computed, onBeforeUnmount, ref, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';
import axios from 'axios';
import { useToast } from 'primevue/usetoast';
import SwaWorkspace from '@/Pages/Swa/Modal/SwaWorkspace.vue';
import { useActionButton } from '@/Composables/useActionButton';

const props = defineProps({
    show: Boolean,
});

const emit = defineEmits(['update:show']);

const toast = useToast();
const page = usePage();

const dragOffset = ref({ x: 0, y: 0 });
const dragStart = ref(null);
const loadingSetup = ref(false);
// Morph state for the workspace action buttons. The requests live here,
// so the actions live here too; the workspace only renders the plain
// snapshots below (idle labels stay "Save Tasks" / "Save SWA" /
// "Update SWA", processing/success follow the Save/Update mapping).
const tasksAction = useActionButton({ idleLabel: 'Save Tasks', processingLabel: 'Saving...', successLabel: 'Saved' });
const saveReportAction = useActionButton({ idleLabel: 'Save SWA', processingLabel: 'Saving...', successLabel: 'Saved' });
const updateReportAction = useActionButton({ idleLabel: 'Update SWA', processingLabel: 'Updating...', successLabel: 'Updated' });
const actionStateSnapshot = (action) => computed(() => ({
    state: action.state,
    label: action.label,
    loading: action.loading,
    disabled: action.disabled,
    morphClass: action.morphClass,
}));
const tasksActionState = actionStateSnapshot(tasksAction);
const saveReportActionState = actionStateSnapshot(saveReportAction);
const updateReportActionState = actionStateSnapshot(updateReportAction);
const isSavingTasks = computed(() => tasksAction.disabled);
const isSavingReport = computed(() => saveReportAction.disabled || updateReportAction.disabled);
const deletingReport = ref(false);
const subject = ref(null);
const tasks = ref([]);
const reports = ref([]);
const calendarEvents = ref([]);
const officeHeads = ref([]);

const currentPermissions = computed(() => page.props.auth?.user?.permissions ?? []);
const canManageSwa = computed(() => currentPermissions.value.includes('swa.manage'));

const modalStyle = computed(() => ({
    transform: `translate(${dragOffset.value.x}px, ${dragOffset.value.y}px)`,
}));

watch(() => props.show, (visible) => {
    if (!visible) return;

    dragOffset.value = { x: 0, y: 0 };
    fetchSetup();
});

async function fetchSetup() {
    loadingSetup.value = true;

    try {
        const { data } = await axios.get('/api/swa/personal');
        subject.value = data.subject ?? null;
        tasks.value = data.tasks ?? [];
        reports.value = data.reports ?? [];
        calendarEvents.value = data.calendar_events ?? [];
        officeHeads.value = data.office_heads ?? [];
    } catch (error) {
        toast.add({
            severity: 'error',
            summary: 'Error',
            detail: error.response?.data?.message ?? 'Could not load personal SWA workspace.',
            life: 4000,
        });
    } finally {
        loadingSetup.value = false;
    }
}

// Raw tasks request, shared by the Save Tasks button path (wrapped in
// the tasks morph below) and the Generate path (which must NOT run the
// tasks morph: the report morph already covers the whole generation,
// and a nested tasks success hold would stall the report request).
async function putTasks(payload, options = {}) {
    await axios.put('/api/swa/personal/tasks', { tasks: payload });
    await fetchSetup();

    if (!options.silent) {
        toast.add({ severity: 'success', summary: 'Saved', detail: 'Personal SWA tasks saved.', life: 3000 });
    }
}

async function handleSaveTasks(payload, options = {}) {
    if (!canManageSwa.value || tasksAction.state !== 'idle') return false;

    try {
        await tasksAction.run(async () => {
            await putTasks(payload, options);
        });

        return true;
    } catch (error) {
        toast.add({
            severity: 'error',
            summary: 'Error',
            detail: extractErrorMessage(error, 'Could not save personal SWA tasks.'),
            life: 4000,
        });
        return false;
    }
}

async function handleSaveReport(payload, callbacks = {}) {
    if (!canManageSwa.value || saveReportAction.state !== 'idle') return;

    let tasksError = null;

    try {
        await saveReportAction.run(async () => {
            const { tasks: taskPayload, ...reportPayload } = payload;

            try {
                await putTasks(taskPayload, { silent: true });
            } catch (error) {
                tasksError = error;
                throw error;
            }

            await axios.post('/api/swa/personal/reports', reportPayload);
            await fetchSetup();

            toast.add({ severity: 'success', summary: 'Generated', detail: 'Personal SWA generated.', life: 3000 });
        });
    } catch (error) {
        toast.add({
            severity: 'error',
            summary: 'Error',
            detail: extractErrorMessage(
                tasksError ?? error,
                tasksError ? 'Could not save personal SWA tasks.' : 'Could not generate personal SWA.',
            ),
            life: 4000,
        });
        callbacks.onError?.(error);
        return;
    }

    // After the full morph cycle, so the "Saved" state is visible first.
    callbacks.onSuccess?.();
}

async function handleUpdateReport(payload, callbacks = {}) {
    if (!canManageSwa.value || updateReportAction.state !== 'idle') return;

    try {
        await updateReportAction.run(async () => {
            const { report_id: reportId, ...reportPayload } = payload;

            await axios.put(`/api/swa/personal/reports/${reportId}`, reportPayload);
            await fetchSetup();

            toast.add({ severity: 'success', summary: 'Updated', detail: 'Personal SWA updated.', life: 3000 });
        });
    } catch (error) {
        toast.add({
            severity: 'error',
            summary: 'Error',
            detail: extractErrorMessage(error, 'Could not update personal SWA.'),
            life: 4000,
        });
        callbacks.onError?.(error);
        return;
    }

    // After the full morph cycle, so the "Updated" state is visible first.
    callbacks.onSuccess?.();
}

async function handleDeleteReport(payload, callbacks = {}) {
    if (!canManageSwa.value) return;

    deletingReport.value = true;

    try {
        const reportId = payload?.report_id ?? payload;

        await axios.delete(`/api/swa/personal/reports/${reportId}`);
        await fetchSetup();

        toast.add({ severity: 'success', summary: 'Deleted', detail: 'Personal SWA deleted.', life: 3000 });
        callbacks.onSuccess?.();
    } catch (error) {
        toast.add({
            severity: 'error',
            summary: 'Error',
            detail: extractErrorMessage(error, 'Could not delete personal SWA.'),
            life: 4000,
        });
        callbacks.onError?.(error);
    } finally {
        deletingReport.value = false;
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