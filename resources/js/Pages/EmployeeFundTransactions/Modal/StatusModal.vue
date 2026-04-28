<template>
    <Dialog :visible="show" @update:visible="val => emit('update:show', val)" modal
        :pt="{ root: { class: 'ios-dialog-root' }, mask: { class: 'ios-dialog-mask' } }">
        <template #container>
            <div ref="elModal" class="ios-modal w-115 max-w-[92vw]" :style="modalStyle">

                <div class="ios-nav-bar" @pointerdown="onDragStart">
                    <button class="ios-nav-btn ios-nav-cancel" @click="emit('update:show', false)">
                        <i class="pi pi-times"></i>
                    </button>
                    <span class="ios-nav-title">Update Status & OBR</span>
                    <button class="ios-nav-btn ios-nav-action font-semibold" @click="handleSave" :disabled="isSaving">
                        <i class="pi pi-save text-emerald-500"></i>
                    </button>
                </div>

                <div class="ios-body">

                    <div class="ios-section mt-8" v-if="modelValue">
                        <div class="flex text-xs text-surface-400 uppercase tracking-wide mb-2">
                            <p class="w-1/4">Transaction: </p><span class="font-semibold text-surface-300">{{
                                modelValue.transaction_id
                            }}</span>
                        </div>
                        <div class="flex text-xs text-surface-400 uppercase tracking-wide mb-2">
                            <p class="w-1/4">Payee: </p><span class="font-semibold text-surface-300">{{
                                modelValue.payee_name
                                }}</span>
                        </div>

                        <div class="flex text-xs text-surface-400">
                            <p class="w-1/4">Current status:</p>
                            <Tag :value="formatTransactionStatus(modelValue.transaction_status)"
                                :severity="statusSeverity(modelValue.transaction_status)" />
                        </div>
                        <div class="flex text-xs text-surface-400 mt-2">
                            <p class="w-1/4">OBR No:</p>
                            <span class="font-semibold text-surface-300">{{ modelValue.obr_no || '—' }}</span>
                        </div>
                        <div class="flex text-xs text-surface-400 mt-2 items-center">
                            <p class="w-1/4">Live OBR status:</p>
                            <div class="flex-1 min-w-0">
                                <Tag v-if="isTrackingLoading" value="Checking..." severity="secondary" />
                                <Tag v-else-if="trackedObrStatus" :value="trackedObrStatus"
                                    :severity="obrStatusSeverity(trackedObrStatus)" />
                                <span v-else-if="trackingError" class="text-amber-500">{{ trackingError }}</span>
                                <span v-else class="font-semibold text-surface-300">
                                    {{ hasObrReference ? `No live status found` : `Add fiscal year and OBR number to
                                    check` }}
                                </span>
                            </div>
                        </div>
                        <div v-if="latestTrackingRemark" class="flex text-xs text-surface-400 mt-2">
                            <p class="w-1/4">Latest update:</p>
                            <span class="font-semibold text-surface-300">{{ latestTrackingRemark }}</span>
                        </div>
                    </div>

                    <div class="ios-section">
                        <label class="text-xs text-surface-400 uppercase tracking-wide mb-2 block">New Status</label>
                        <Select v-model="selectedStatus" :options="statusOptions" optionLabel="label"
                            optionValue="value" placeholder="Select status" class="w-full" />
                    </div>

                    <div class="ios-section pb-8">
                        <label class="text-xs text-surface-400 uppercase tracking-wide mb-2 block">OBR Details</label>
                        <div class="ios-card space-y-3 p-4">
                            <div>
                                <label class="text-xs text-surface-400 uppercase tracking-wide mb-2 block">Fiscal
                                    Year</label>
                                <InputText v-model="fiscalYear" type="number" placeholder="e.g., 2026" class="w-full" />
                            </div>
                            <div>
                                <label class="text-xs text-surface-400 uppercase tracking-wide mb-2 block">OBR
                                    Number</label>
                                <InputText v-model="obrNumber" type="text" placeholder="Enter OBR number"
                                    class="w-full" />
                            </div>
                        </div>
                    </div>


                </div>

            </div>
        </template>
    </Dialog>
</template>

<script setup>
import { ref, watch, computed } from 'vue';

const props = defineProps({
    show: Boolean,
    modelValue: { type: Object, default: null },
    statusOptions: {
        type: Array,
        default: () => [
            { label: 'On Process', value: 'on_process' },
            { label: 'Claimed', value: 'claimed' },
            { label: 'Cancelled', value: 'cancelled' },
            { label: 'Suspended', value: 'suspended' },
        ],
    },
    isSaving: { type: Boolean, default: false },
    liveTrackingData: { type: Object, default: null },
    isTrackingLoading: { type: Boolean, default: false },
    trackingError: { type: String, default: '' },
});

const emit = defineEmits(['update:show', 'save']);

const selectedStatus = ref(null);
const fiscalYear = ref('');
const obrNumber = ref('');

const trackedObrStatus = computed(() => props.liveTrackingData?.obr_info?.obr_status || null);
const latestTrackingRemark = computed(() => props.liveTrackingData?.tracking_information?.[0]?.trn_remarks || null);
const hasObrReference = computed(() => Boolean(props.modelValue?.fiscal_year && props.modelValue?.obr_no));

watch(() => props.modelValue, (val) => {
    hydrateForm(val);
}, { immediate: true });

watch(() => props.show, (val) => {
    if (val && props.modelValue) {
        hydrateForm(props.modelValue);
    }
});

function handleSave() {
    if (!selectedStatus.value) return;
    emit('save', {
        transaction_status: normalizeTransactionStatus(selectedStatus.value),
        fiscal_year: normalizeFiscalYear(fiscalYear.value),
        obr_no: normalizeTextField(obrNumber.value),
    });
}

function hydrateForm(value) {
    selectedStatus.value = normalizeTransactionStatus(value?.transaction_status) || null;
    fiscalYear.value = value?.fiscal_year != null ? String(value.fiscal_year) : '';
    obrNumber.value = value?.obr_no || '';
}

function statusSeverity(status) {
    const map = {
        on_process: 'warn',
        claimed: 'success',
        approved: 'success',
        active: 'info',
        cancelled: 'danger',
        denied: 'danger',
        suspended: 'secondary',
    };
    return map[status] || 'secondary';
}

function obrStatusSeverity(status) {
    const map = {
        Active: 'success',
        Cancelled: 'danger',
        Canceled: 'danger',
        Suspended: 'warn',
        Denied: 'danger',
        Closed: 'secondary',
    };

    return map[status] || 'info';
}

function formatTransactionStatus(status) {
    const map = {
        on_process: 'On Process',
        claimed: 'Claimed',
        approved: 'Approved',
        active: 'Active',
        cancelled: 'Cancelled',
        denied: 'Denied',
        suspended: 'Suspended',
    };

    return map[status] || status || '—';
}

function normalizeTransactionStatus(status) {
    if (status && typeof status === 'object' && 'value' in status) {
        return normalizeTransactionStatus(status.value);
    }

    if (typeof status !== 'string') {
        return status;
    }

    const normalized = status.trim().toLowerCase().replace(/[\s-]+/g, '_');

    if (normalized === 'canceled') {
        return 'cancelled';
    }

    return ['on_process', 'claimed', 'cancelled', 'suspended', 'approved', 'active', 'denied'].includes(normalized)
        ? normalized
        : status;
}

function normalizeFiscalYear(value) {
    if (value === null || value === undefined || value === '') {
        return null;
    }

    const parsed = Number.parseInt(value, 10);
    return Number.isNaN(parsed) ? null : parsed;
}

function normalizeTextField(value) {
    if (typeof value !== 'string') {
        return value ?? null;
    }

    const trimmed = value.trim();
    return trimmed === '' ? null : trimmed;
}

const elModal = ref(null);
const dragOffset = ref({ x: 0, y: 0 });
const dragStart = ref(null);

const modalStyle = computed(() => ({
    transform: `translate(${dragOffset.value.x}px, ${dragOffset.value.y}px)`,
}));

function onDragStart(e) {
    if (e.target.closest('button, input, .p-select')) return;
    dragStart.value = { x: e.clientX - dragOffset.value.x, y: e.clientY - dragOffset.value.y };
    window.addEventListener('pointermove', onDragMove);
    window.addEventListener('pointerup', onDragEnd);
}

function onDragMove(e) {
    if (!dragStart.value) return;
    dragOffset.value = { x: e.clientX - dragStart.value.x, y: e.clientY - dragStart.value.y };
}

function onDragEnd() {
    dragStart.value = null;
    window.removeEventListener('pointermove', onDragMove);
    window.removeEventListener('pointerup', onDragEnd);
}
</script>
