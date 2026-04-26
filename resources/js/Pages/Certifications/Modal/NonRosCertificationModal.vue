<template>
    <Dialog :visible="show" @update:visible="val => emit('update:show', val)" modal
        :pt="{ root: { class: 'ios-dialog-root' }, mask: { class: 'ios-dialog-mask' } }">
        <template #container>
            <div ref="elModal" class="ios-modal w-240 max-w-[95vw]" :style="modalStyle">

                <div class="ios-nav-bar" @pointerdown="onDragStart">
                    <button class="ios-nav-btn ios-nav-cancel" @click="emit('update:show', false)">
                        <i class="pi pi-times"></i>
                    </button>
                    <span class="ios-nav-title">{{ mode === 'edit' ? `Edit Non-ROS Certification` : `New Non-ROS
                        Certification` }}</span>
                    <button class="ios-nav-btn ios-nav-action" :disabled="saving || !officeHeads.length"
                        @click="submit">
                        {{ saving ? 'Saving…' : 'Save' }}
                    </button>
                </div>

                <div class="ios-body">
                    <div v-if="!officeHeads.length" class="ios-section pb-4">
                        <div
                            class="ios-card p-4 border border-amber-200 bg-amber-50 dark:border-amber-400/20 dark:bg-amber-950/20">
                            <p class="text-sm font-medium text-amber-900 dark:text-amber-100">No office head signatory
                                configured</p>
                            <p class="text-sm text-amber-800/80 dark:text-amber-100/80 mt-1">
                                Add at least one Part A office head in Signatories before saving a certification.
                            </p>
                        </div>
                    </div>

                    <div class="ios-section pb-4">
                        <p class="ios-section-label">Certification Details</p>
                        <div class="ios-card p-4 space-y-3">
                            <div class="grid md:grid-cols-[180px_minmax(0,1fr)] gap-3">
                                <div class="ios-form-group">
                                    <label class="ios-label">Honorific</label>
                                    <InputText v-model="form.subject_honorific" placeholder="e.g. Mr., Ms., Dr."
                                        class="w-full" />
                                    <span v-if="errors.subject_honorific" class="ios-hint ios-error">{{
                                        errors.subject_honorific }}</span>
                                </div>

                                <div class="ios-form-group">
                                    <label class="ios-label">Name <span class="text-red-500">*</span></label>
                                    <InputText v-model="form.subject_name" placeholder="e.g. Juan Santos"
                                        class="w-full" />
                                    <span v-if="errors.subject_name" class="ios-hint ios-error">{{ errors.subject_name
                                        }}</span>
                                </div>
                            </div>

                            <div class="grid md:grid-cols-2 gap-3">
                                <div class="ios-form-group">
                                    <label class="ios-label">Designation <span class="text-red-500">*</span></label>
                                    <InputText v-model="form.designation" placeholder="e.g. Administrative Aide VI"
                                        class="w-full" />
                                    <span v-if="errors.designation" class="ios-hint ios-error">{{ errors.designation
                                        }}</span>
                                </div>

                                <div class="ios-form-group">
                                    <label class="ios-label">Office <span class="text-red-500">*</span></label>
                                    <InputText v-model="form.office" placeholder="e.g. Human Resource Management Office"
                                        class="w-full" />
                                    <span v-if="errors.office" class="ios-hint ios-error">{{ errors.office }}</span>
                                </div>
                            </div>

                            <div class="grid md:grid-cols-2 gap-3">
                                <div class="ios-form-group">
                                    <label class="ios-label">Date Issued <span class="text-red-500">*</span></label>
                                    <DatePicker v-model="form.issued_date" class="w-full" showIcon fluid
                                        dateFormat="MM d, yy" />
                                    <span v-if="errors.issued_date" class="ios-hint ios-error">{{ errors.issued_date
                                        }}</span>
                                </div>

                                <div class="ios-form-group">
                                    <label class="ios-label">Office Head Signatory <span
                                            class="text-red-500">*</span></label>
                                    <Select v-model="form.office_head_id" :options="officeHeads" optionLabel="name"
                                        optionValue="id" placeholder="Select office head" class="w-full" />
                                    <span v-if="errors.office_head_id" class="ios-hint ios-error">{{
                                        errors.office_head_id }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div v-if="selectedOfficeHead" class="ios-section pb-4">
                        <p class="ios-section-label">Signatory Snapshot</p>
                        <div class="ios-card p-4 space-y-1 text-sm">
                            <p class="font-medium text-surface-800 dark:text-surface-100">{{ selectedOfficeHead.name }}
                            </p>
                            <p class="text-surface-500">{{ selectedOfficeHead.office || '—' }}</p>
                            <p v-if="selectedOfficeHead.titles?.length" class="text-surface-400">
                                {{ selectedOfficeHead.titles.join(' / ') }}
                            </p>
                        </div>
                    </div>
                </div>

            </div>
        </template>
    </Dialog>
</template>

<script setup>
import { computed, reactive, ref, watch } from 'vue';
import axios from 'axios';
import { useToast } from 'primevue/usetoast';

const props = defineProps({
    show: Boolean,
    certification: { type: Object, default: null },
    mode: { type: String, default: 'create' },
    officeHeads: { type: Array, default: () => [] },
});

const emit = defineEmits(['update:show', 'saved']);

const toast = useToast();
const saving = ref(false);
const errors = ref({});

const defaultForm = () => ({
    subject_name: '',
    subject_honorific: '',
    designation: '',
    office: '',
    issued_date: new Date(),
    office_head_id: null,
});

const form = reactive(defaultForm());

watch(() => props.show, (visible) => {
    if (!visible) return;

    errors.value = {};

    if (props.mode === 'edit' && props.certification) {
        form.subject_name = props.certification.subject_name ?? '';
        form.subject_honorific = props.certification.subject_honorific ?? '';
        form.designation = props.certification.designation ?? '';
        form.office = props.certification.office ?? '';
        form.issued_date = parseDate(props.certification.issued_date) ?? new Date();
        form.office_head_id = props.certification.office_head_signatory_id ?? props.officeHeads[0]?.id ?? null;
    } else {
        Object.assign(form, defaultForm());
        form.office_head_id = props.officeHeads[0]?.id ?? null;
    }

    dragOffset.value = { x: 0, y: 0 };
});

const selectedOfficeHead = computed(() => props.officeHeads.find((officeHead) => officeHead.id === form.office_head_id) ?? null);

async function submit() {
    errors.value = {};

    const payload = {
        subject_name: form.subject_name,
        subject_honorific: form.subject_honorific,
        designation: form.designation,
        office: form.office,
        issued_date: formatDateForApi(form.issued_date),
        office_head_id: form.office_head_id,
    };

    saving.value = true;

    try {
        const response = props.mode === 'edit'
            ? await axios.put(`/api/certifications/non-ros/${props.certification.id}`, payload)
            : await axios.post('/api/certifications/non-ros', payload);

        toast.add({
            severity: 'success',
            summary: 'Saved',
            detail: props.mode === 'edit' ? 'Certification updated.' : 'Certification saved.',
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
                detail: data?.message ?? 'Could not save certification.',
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
    if (!(value instanceof Date) || Number.isNaN(value.getTime())) {
        return '';
    }

    const year = value.getFullYear();
    const month = String(value.getMonth() + 1).padStart(2, '0');
    const day = String(value.getDate()).padStart(2, '0');

    return `${year}-${month}-${day}`;
}

const elModal = ref(null);
const dragOffset = ref({ x: 0, y: 0 });
const dragStart = ref(null);

const modalStyle = computed(() => ({
    transform: `translate(${dragOffset.value.x}px, ${dragOffset.value.y}px)`,
}));

function onDragStart(event) {
    if (event.target.closest('button, input, textarea, .p-inputtext, .p-datepicker, .p-select')) return;
    dragStart.value = { x: event.clientX - dragOffset.value.x, y: event.clientY - dragOffset.value.y };
    window.addEventListener('pointermove', onDragMove);
    window.addEventListener('pointerup', onDragEnd);
}

function onDragMove(event) {
    if (!dragStart.value) return;
    dragOffset.value = { x: event.clientX - dragStart.value.x, y: event.clientY - dragStart.value.y };
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
:deep(.p-datepicker-input) {
    border-radius: 1rem;
}
</style>