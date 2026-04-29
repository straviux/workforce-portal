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
                            <div class="grid md:grid-cols-2 gap-3">
                                <div class="ios-form-group">
                                    <label class="ios-label">Honorific</label>
                                    <InputText v-model="form.subject_honorific" placeholder="e.g. Mr., Ms., Dr."
                                        class="w-full" />
                                    <span v-if="errors.subject_honorific" class="ios-hint ios-error">{{
                                        errors.subject_honorific }}</span>
                                </div>

                                <div class="ios-form-group">
                                    <label class="ios-label">First Name <span class="text-red-500">*</span></label>
                                    <InputText v-model="form.subject_firstname" placeholder="e.g. Juan"
                                        class="w-full" />
                                    <span v-if="errors.subject_firstname" class="ios-hint ios-error">{{
                                        errors.subject_firstname }}</span>
                                </div>
                            </div>

                            <div class="grid md:grid-cols-2 gap-3">
                                <div class="ios-form-group">
                                    <label class="ios-label">Middle Name</label>
                                    <InputText v-model="form.subject_middlename" placeholder="e.g. Reyes"
                                        class="w-full" />
                                    <span v-if="errors.subject_middlename" class="ios-hint ios-error">{{
                                        errors.subject_middlename }}</span>
                                </div>

                                <div class="ios-form-group">
                                    <label class="ios-label">Last Name <span class="text-red-500">*</span></label>
                                    <InputText v-model="form.subject_lastname" placeholder="e.g. Santos"
                                        class="w-full" />
                                    <span v-if="errors.subject_lastname" class="ios-hint ios-error">{{
                                        errors.subject_lastname }}</span>
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

                            <div class="grid md:grid-cols-1 gap-3">
                                <div class="ios-form-group">
                                    <label class="ios-label">Date Issued <span class="text-red-500">*</span></label>
                                    <DatePicker v-model="form.issued_date" class="w-full" showIcon fluid
                                        dateFormat="MM d, yy" />
                                    <span v-if="errors.issued_date" class="ios-hint ios-error">{{ errors.issued_date
                                    }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="ios-section pb-4">
                        <p class="ios-section-label">Signatory Snapshot</p>
                        <div class="ios-card p-4 flex items-start justify-between gap-4 flex-wrap">
                            <div class="space-y-1 text-sm">
                                <p class="font-medium text-surface-800 dark:text-surface-100" :style="form.signatory_name_underline
                                    ? 'display:inline-block;padding-bottom:2px;border-bottom:1px solid currentColor;'
                                    : ''">
                                    {{ selectedOfficeHead?.name || 'No signatory selected' }}
                                </p>
                                <template v-if="selectedOfficeHead">
                                    <p v-for="line in signatoryPreviewLines" :key="line" class="text-surface-500">{{
                                        line }}</p>
                                    <p v-if="!signatoryPreviewLines.length" class="text-surface-400">
                                        Only the signatory name will be printed.
                                    </p>
                                </template>
                                <p v-else class="text-surface-400">Choose the signatory and display options for the
                                    certification footer.</p>
                            </div>

                            <Button type="button" icon="pi pi-user-edit" label="Configure" class="rounded" size="small"
                                @click="showSnapshotModal = true" :disabled="!officeHeads.length" />
                        </div>
                        <span v-if="errors.office_head_id" class="ios-hint ios-error mt-2 block">{{
                            errors.office_head_id }}</span>
                    </div>
                </div>

            </div>
        </template>
    </Dialog>

    <SignatorySnapshotModal v-model:show="showSnapshotModal" :office-heads="officeHeads"
        :initial-value="signatorySnapshotForm" :allow-title-selection="true" :allow-display-options="true"
        @apply="applySignatorySnapshot" />
</template>

<script setup>
import { computed, reactive, ref, watch } from 'vue';
import axios from 'axios';
import { useToast } from 'primevue/usetoast';
import { resolveCertificationSubjectParts } from '@/Pages/Certifications/support/subjectName';

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
    subject_honorific: '',
    subject_firstname: '',
    subject_middlename: '',
    subject_lastname: '',
    designation: '',
    office: '',
    issued_date: new Date(),
    office_head_id: null,
    signatory_titles: [],
    signatory_name_underline: false,
    signatory_show_designation: true,
    signatory_show_office: true,
    signatory_info_order: 'designation_first',
});

const form = reactive(defaultForm());
const showSnapshotModal = ref(false);

watch(() => props.show, (visible) => {
    if (!visible) return;

    errors.value = {};

    if (props.mode === 'edit' && props.certification) {
        const subjectParts = resolveCertificationSubjectParts(props.certification);

        form.subject_honorific = props.certification.subject_honorific ?? '';
        form.subject_firstname = subjectParts.firstname;
        form.subject_middlename = subjectParts.middlename;
        form.subject_lastname = subjectParts.lastname;
        form.designation = props.certification.designation ?? '';
        form.office = props.certification.office ?? '';
        form.issued_date = parseDate(props.certification.issued_date) ?? new Date();
        form.office_head_id = props.certification.office_head_signatory_id ?? props.officeHeads[0]?.id ?? null;
        form.signatory_titles = sanitizeSelectedTitles(
            props.certification.signatory_titles,
            officeHeadTitlesForId(form.office_head_id),
        );
        form.signatory_name_underline = props.certification.signatory_name_underline === true;
        form.signatory_show_designation = props.certification.signatory_show_designation ?? true;
        form.signatory_show_office = props.certification.signatory_show_office ?? true;
        form.signatory_info_order = props.certification.signatory_info_order === 'office_first'
            ? 'office_first'
            : 'designation_first';
    } else {
        Object.assign(form, defaultForm());
        form.office_head_id = props.officeHeads[0]?.id ?? null;
        form.signatory_titles = officeHeadTitlesForId(form.office_head_id);
    }

    dragOffset.value = { x: 0, y: 0 };
});

const selectedOfficeHead = computed(() => props.officeHeads.find((officeHead) => officeHead.id === form.office_head_id) ?? null);
const signatorySnapshotForm = computed(() => ({
    office_head_id: form.office_head_id,
    signatory_titles: form.signatory_titles,
    signatory_name_underline: form.signatory_name_underline,
    signatory_show_designation: form.signatory_show_designation,
    signatory_show_office: form.signatory_show_office,
    signatory_info_order: form.signatory_info_order,
}));
const signatoryPreviewLines = computed(() => buildSignatoryDetailLines(selectedOfficeHead.value, form));

async function submit() {
    errors.value = {};

    const payload = {
        subject_honorific: form.subject_honorific,
        subject_firstname: form.subject_firstname,
        subject_middlename: form.subject_middlename,
        subject_lastname: form.subject_lastname,
        designation: form.designation,
        office: form.office,
        issued_date: formatDateForApi(form.issued_date),
        office_head_id: form.office_head_id,
        signatory_titles: form.signatory_titles,
        signatory_name_underline: form.signatory_name_underline,
        signatory_show_designation: form.signatory_show_designation,
        signatory_show_office: form.signatory_show_office,
        signatory_info_order: form.signatory_info_order,
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

function applySignatorySnapshot(snapshot) {
    form.office_head_id = snapshot.office_head_id;
    form.signatory_titles = sanitizeSelectedTitles(
        snapshot.signatory_titles,
        officeHeadTitlesForId(snapshot.office_head_id),
    );
    form.signatory_name_underline = snapshot.signatory_name_underline === true;
    form.signatory_show_designation = snapshot.signatory_show_designation;
    form.signatory_show_office = snapshot.signatory_show_office;
    form.signatory_info_order = snapshot.signatory_info_order;

    if (errors.value.office_head_id) {
        delete errors.value.office_head_id;
    }
}

function buildSignatoryDetailLines(officeHead, config) {
    if (!officeHead) {
        return [];
    }

    const titles = sanitizeSelectedTitles(config.signatory_titles, officeHeadTitlesForId(officeHead.id));
    const officeLine = typeof officeHead.office === 'string' ? officeHead.office.trim() : '';
    const designationLines = config.signatory_show_designation ? titles : [];
    const officeLines = config.signatory_show_office && officeLine ? [officeLine] : [];

    return uniqueTextLines(config.signatory_info_order === 'office_first'
        ? [...officeLines, ...designationLines]
        : [...designationLines, ...officeLines]);
}

function officeHeadTitlesForId(officeHeadId) {
    const officeHead = props.officeHeads.find((entry) => entry.id === officeHeadId);
    const titles = Array.isArray(officeHead?.titles) ? officeHead.titles : [];

    return titles
        .map((title) => (typeof title === 'string' ? title.trim() : ''))
        .filter(Boolean);
}

function sanitizeSelectedTitles(selectedTitles, availableTitles) {
    if (!Array.isArray(selectedTitles)) {
        return [...availableTitles];
    }

    return selectedTitles
        .map((title) => (typeof title === 'string' ? title.trim() : ''))
        .filter((title) => availableTitles.includes(title));
}

function uniqueTextLines(lines) {
    const seen = new Set();

    return lines
        .map((line) => (typeof line === 'string' ? line.trim() : ''))
        .filter((line) => {
            if (!line) {
                return false;
            }

            const key = line.toLowerCase();
            if (seen.has(key)) {
                return false;
            }

            seen.add(key);
            return true;
        });
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