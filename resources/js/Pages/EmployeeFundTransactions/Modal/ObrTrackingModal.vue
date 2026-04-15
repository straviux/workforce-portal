<template>
    <Dialog :visible="show" @update:visible="val => emit('update:show', val)" modal
        :pt="{ root: { class: 'ios-dialog-root' }, mask: { class: 'ios-dialog-mask' } }">
        <template #container>
            <div ref="elModal" class="ios-modal" style="width: 90vw; max-width: 500px;" :style="modalStyle">
                <div class="ios-nav-bar" @pointerdown="onDragStart">
                    <button class="ios-nav-btn ios-nav-cancel" @click="emit('update:show', false)">
                        <i class="pi pi-times"></i>
                    </button>
                    <span class="ios-nav-title">Update OBR Info</span>
                    <button v-if="!isComplete" class="ios-nav-btn ios-nav-action" @click="saveOBRTracking"
                        :disabled="isSaving">
                        <i v-if="isSaving" class="pi pi-spin pi-spinner"
                            style="font-size:12px;margin-right:3px;"></i>Save
                    </button>
                    <span v-else class="ios-nav-btn" style="visibility:hidden;right:16px;">_</span>
                </div>
                <div class="ios-body">
                    <div class="p-4">
                        <!-- Transaction Header -->
                        <div v-if="modelValue" class="ios-section">
                            <div class="ios-card px-4 py-2">
                                <p class="flex justify-between">
                                    <span class="text-xs">Payee:</span>
                                    <span class="font-medium text-gray-700 dark:text-gray-100">{{ modelValue.payee_name
                                        }}</span>
                                </p>
                                <p class="flex justify-between">
                                    <span class="text-xs">OBR No:</span>
                                    <span class="font-medium text-gray-700 dark:text-gray-100">{{ modelValue.obr_no ||
                                        '—' }}</span>
                                </p>
                                <p class="flex justify-between">
                                    <span class="text-xs">OBR Type:</span>
                                    <span class="font-medium text-gray-700 dark:text-gray-100">{{ modelValue.obr_type ||
                                        '—' }}</span>
                                </p>
                                <p class="flex justify-between">
                                    <span class="text-xs">OBR Status:</span>
                                    <span class="font-medium text-gray-700 dark:text-gray-100">{{ modelValue.obr_status
                                        || 'No OBR' }}</span>
                                </p>
                                <p class="flex justify-between">
                                    <span class="text-xs">Employee Type:</span>
                                    <span class="font-medium text-gray-700 dark:text-gray-100 capitalize">{{
                                        modelValue.employee_type || '—' }}</span>
                                </p>
                            </div>
                        </div>

                        <div v-if="!isComplete" class="ios-section">
                            <div class="ios-card px-4 py-2">
                                <p class="text-sm text-gray-500">Enter OBR details to update this transaction's tracking
                                    information.</p>
                            </div>
                        </div>

                        <!-- Form Fields -->
                        <div v-if="!isComplete" class="ios-section">
                            <p class="ios-section-label">OBR Details</p>
                            <div class="ios-card">
                                <div class="ios-row">
                                    <span class="ios-row-label">Fiscal Year *</span>
                                    <InputText v-model.number="formData.fiscal_year" type="number"
                                        placeholder="e.g., 2025" class="w-48 text-right" />
                                </div>
                                <div class="ios-row">
                                    <span class="ios-row-label">OBR Number *</span>
                                    <InputText v-model="formData.obr_no" type="text" placeholder="e.g., 200-25-12-24188"
                                        class="w-48 text-right" />
                                </div>
                                <div class="ios-row">
                                    <span class="ios-row-label">DV Number</span>
                                    <InputText v-model="formData.dv_no" type="text" placeholder="DV number"
                                        class="w-48 text-right" />
                                </div>
                                <div class="ios-row">
                                    <span class="ios-row-label">Date Obligated</span>
                                    <InputText v-model="formData.date_obligated" type="date" class="w-48" />
                                </div>
                                <div class="ios-row" style="border-bottom:none;">
                                    <span class="ios-row-label">OBR Status</span>
                                    <Select v-model="formData.obr_status" :options="obrStatusOptions"
                                        optionLabel="label" optionValue="value" placeholder="Select status"
                                        class="w-48" />
                                </div>
                            </div>
                        </div>

                        <!-- Success Result -->
                        <div v-if="isComplete" class="ios-section" style="margin-bottom:16px;">
                            <div class="ios-card" style="padding:14px 16px;background:#f0fdf4;">
                                <p style="font-size:14px;font-weight:600;color:#166534;margin-bottom:8px;">✓ Saved
                                    Successfully</p>
                                <div style="font-size:13px;color:#15803d;line-height:1.8;">
                                    <p><strong>Fiscal Year:</strong> {{ formData.fiscal_year }}</p>
                                    <p><strong>OBR Number:</strong> {{ formData.obr_no }}</p>
                                    <p v-if="formData.dv_no"><strong>DV Number:</strong> {{ formData.dv_no }}</p>
                                    <p v-if="formData.date_obligated"><strong>Date Obligated:</strong> {{
                                        formData.date_obligated }}</p>
                                    <p v-if="formData.obr_status"><strong>OBR Status:</strong> {{ formData.obr_status }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </Dialog>
</template>

<script setup>
import { ref, reactive, computed, watch, onBeforeUnmount } from 'vue';
import Dialog from 'primevue/dialog';
import InputText from 'primevue/inputtext';
import Select from 'primevue/select';
import { useToast } from 'primevue/usetoast';

const props = defineProps({
    show: { type: Boolean, required: true },
    modelValue: { type: Object, default: null },
    isSaving: { type: Boolean, default: false },
    isComplete: { type: Boolean, default: false },
});

const emit = defineEmits(['update:show', 'save']);

const toast = useToast();
const elModal = ref(null);

const formData = reactive({
    fiscal_year: new Date().getFullYear(),
    obr_no: '',
    dv_no: '',
    date_obligated: null,
    obr_status: 'No OBR',
});

const obrStatusOptions = [
    { label: 'No OBR', value: 'No OBR' },
    { label: 'On Process', value: 'On Process' },
    { label: 'LOA', value: 'LOA' },
    { label: 'Irregular', value: 'Irregular' },
    { label: 'Transferred', value: 'Transferred' },
    { label: 'Claimed', value: 'Claimed' },
    { label: 'Paid', value: 'Paid' },
    { label: 'Denied', value: 'Denied' },
];

watch(() => props.show, (visible) => {
    if (visible && props.modelValue) {
        const v = props.modelValue;
        formData.fiscal_year = v.fiscal_year || new Date().getFullYear();
        formData.obr_no = v.obr_no || '';
        formData.dv_no = v.dv_no || '';
        formData.date_obligated = v.date_obligated ? String(v.date_obligated).substring(0, 10) : null;
        formData.obr_status = v.obr_status || 'No OBR';
    }
});

// ── Drag ──────────────────────────────────────────────────────────────
const dragOffset = ref({ x: 0, y: 0 });
const dragStart = ref(null);

const modalStyle = computed(() => ({
    transform: `translate(${dragOffset.value.x}px, ${dragOffset.value.y}px)`,
}));

function onDragStart(e) {
    if (e.target.closest('button, .p-select')) return;
    dragStart.value = { x: e.clientX - dragOffset.value.x, y: e.clientY - dragOffset.value.y };
    document.addEventListener('pointermove', onDragMove);
    document.addEventListener('pointerup', onDragEnd);
}

function onDragMove(e) {
    if (!dragStart.value) return;
    dragOffset.value = { x: e.clientX - dragStart.value.x, y: e.clientY - dragStart.value.y };
}

function onDragEnd() {
    dragStart.value = null;
    document.removeEventListener('pointermove', onDragMove);
    document.removeEventListener('pointerup', onDragEnd);
}

onBeforeUnmount(() => {
    document.removeEventListener('pointermove', onDragMove);
    document.removeEventListener('pointerup', onDragEnd);
});

// ── Save ──────────────────────────────────────────────────────────────
function saveOBRTracking() {
    if (!formData.fiscal_year || !formData.obr_no?.trim()) {
        toast.add({
            severity: 'warn',
            summary: 'Missing Fields',
            detail: 'Fiscal Year and OBR Number are required.',
            life: 3000,
        });
        return;
    }
    emit('save', { ...formData });
}
</script>
