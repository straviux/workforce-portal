<template>
    <Dialog :visible="show" @update:visible="value => emit('update:show', value)" modal
        :pt="{ root: { class: 'ios-dialog-root' }, mask: { class: 'ios-dialog-mask' } }">
        <template #container>
            <div class="ios-modal w-220 max-w-[95vw]" :style="modalStyle">
                <div class="ios-nav-bar" @pointerdown="onDragStart">
                    <button type="button" class="ios-nav-btn ios-nav-cancel" @click="emit('update:show', false)">
                        <i class="pi pi-times"></i>
                    </button>
                    <span class="ios-nav-title">{{ mode === 'edit' ? 'Edit Calendar Event' : 'New Calendar Event'
                        }}</span>
                    <button type="button" class="ios-nav-btn ios-nav-action" :disabled="saving" @click="submit">
                        {{ saving ? 'Saving…' : 'Save' }}
                    </button>
                </div>

                <div class="ios-body">
                    <div class="ios-section pb-4">
                        <p class="ios-section-label">Event Details</p>
                        <div class="ios-card p-4 space-y-3">
                            <div class="grid md:grid-cols-2 gap-3">
                                <div class="ios-form-group">
                                    <label class="ios-label">Event Date <span class="text-red-500">*</span></label>
                                    <DatePicker v-model="form.event_date" class="w-full" showIcon fluid
                                        dateFormat="MM d, yy" />
                                    <span v-if="errors.event_date" class="ios-hint ios-error">{{ errors.event_date
                                        }}</span>
                                </div>

                                <div class="ios-form-group">
                                    <label class="ios-label">Event Type <span class="text-red-500">*</span></label>
                                    <Select v-model="form.event_type" :options="eventTypeOptions" optionLabel="label"
                                        optionValue="value" class="w-full" />
                                    <span v-if="errors.event_type" class="ios-hint ios-error">{{ errors.event_type
                                        }}</span>
                                </div>
                            </div>

                            <div class="ios-form-group">
                                <label class="ios-label">Title <span class="text-red-500">*</span></label>
                                <InputText v-model="form.title" class="w-full"
                                    placeholder="e.g. Founding Anniversary Holiday" />
                                <span v-if="errors.title" class="ios-hint ios-error">{{ errors.title }}</span>
                            </div>

                            <div class="ios-form-group">
                                <label class="ios-label">Description</label>
                                <Textarea v-model="form.description" rows="4" autoResize class="w-full"
                                    placeholder="Optional note for the declared holiday or work suspension" />
                                <span v-if="errors.description" class="ios-hint ios-error">{{ errors.description
                                    }}</span>
                            </div>

                            <div class="ios-form-group">
                                <div
                                    class="flex items-center justify-between gap-4 rounded-2xl border border-surface-200 dark:border-surface-700 px-4 py-3">
                                    <div>
                                        <label class="ios-label !mb-1">Active for SWA blocking</label>
                                        <p class="text-xs text-surface-400">
                                            Inactive events stay in the registry but will not block SWA dates.
                                        </p>
                                    </div>

                                    <ToggleSwitch v-model="form.is_active" />
                                </div>
                                <span v-if="errors.is_active" class="ios-hint ios-error">{{ errors.is_active }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="ios-section pb-4">
                        <div
                            class="ios-card p-4 border border-sky-200 bg-sky-50 dark:border-sky-400/20 dark:bg-sky-950/20">
                            <p class="text-sm font-medium text-sky-900 dark:text-sky-100">Used by SWA generation</p>
                            <p class="text-sm text-sky-800/80 dark:text-sky-100/80 mt-1">
                                Active dates here are automatically excluded from SWA draft values inside the selected
                                report period.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </Dialog>
</template>

<script setup>
import dayjs from 'dayjs';
import { computed, reactive, ref, watch } from 'vue';
import axios from 'axios';
import { useToast } from 'primevue/usetoast';

const props = defineProps({
    show: Boolean,
    event: { type: Object, default: null },
    mode: { type: String, default: 'create' },
});

const emit = defineEmits(['update:show', 'saved']);

const toast = useToast();
const saving = ref(false);
const errors = ref({});

const eventTypeOptions = [
    { label: 'Legal Holiday', value: 'legal_holiday' },
    { label: 'Local Holiday', value: 'local_holiday' },
    { label: 'Work Suspension', value: 'work_suspension' },
];

const defaultForm = () => ({
    event_date: new Date(),
    title: '',
    event_type: 'local_holiday',
    description: '',
    is_active: true,
});

const form = reactive(defaultForm());

watch(() => props.show, (visible) => {
    if (!visible) return;

    errors.value = {};

    if (props.mode === 'edit' && props.event) {
        form.event_date = parseDate(props.event.event_date) ?? new Date();
        form.title = props.event.title ?? '';
        form.event_type = props.event.event_type ?? 'local_holiday';
        form.description = props.event.description ?? '';
        form.is_active = props.event.is_active ?? true;
    } else {
        Object.assign(form, defaultForm());
    }

    dragOffset.value = { x: 0, y: 0 };
});

async function submit() {
    errors.value = {};
    saving.value = true;

    const payload = {
        event_date: formatDateForApi(form.event_date),
        title: form.title,
        event_type: form.event_type,
        description: form.description,
        is_active: !!form.is_active,
    };

    try {
        const response = props.mode === 'edit' && props.event
            ? await axios.put(`/api/calendar/${props.event.id}`, payload)
            : await axios.post('/api/calendar', payload);

        toast.add({
            severity: 'success',
            summary: 'Saved',
            detail: props.mode === 'edit' ? 'Calendar event updated.' : 'Calendar event saved.',
            life: 3000,
        });

        emit('update:show', false);
        emit('saved', response.data.data);
    } catch (error) {
        const data = error.response?.data;

        if (data?.errors) {
            errors.value = Object.fromEntries(
                Object.entries(data.errors).map(([field, messages]) => [field, Array.isArray(messages) ? messages[0] : messages]),
            );
        } else {
            toast.add({
                severity: 'error',
                summary: 'Error',
                detail: data?.message ?? 'Could not save calendar event.',
                life: 4000,
            });
        }
    } finally {
        saving.value = false;
    }
}

function parseDate(value) {
    if (!value) return null;

    const parsed = new Date(value);
    return Number.isNaN(parsed.getTime()) ? null : parsed;
}

function formatDateForApi(value) {
    if (!value) return '';
    return dayjs(value).format('YYYY-MM-DD');
}

const dragOffset = ref({ x: 0, y: 0 });
const dragStart = ref(null);

const modalStyle = computed(() => ({
    transform: `translate(${dragOffset.value.x}px, ${dragOffset.value.y}px)`,
}));

function onDragStart(event) {
    if (event.target.closest('button, input, textarea, .p-inputtext, .p-datepicker, .p-select, .p-toggleswitch')) return;

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
</script>

<style scoped>
:deep(.p-inputtext),
:deep(.p-select),
:deep(.p-datepicker-input),
:deep(.p-textarea) {
    border-radius: 1rem;
}
</style>