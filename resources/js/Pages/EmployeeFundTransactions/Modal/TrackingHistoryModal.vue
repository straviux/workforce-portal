<template>
    <Dialog :visible="show" @update:visible="val => emit('update:show', val)" modal
        :pt="{ root: { class: 'ios-dialog-root' }, mask: { class: 'ios-dialog-mask' } }">
        <template #container>
            <div ref="elModal" class="ios-modal w-130 max-w-[92vw]" :style="modalStyle">

                <div class="ios-nav-bar" @pointerdown="onDragStart">
                    <button class="ios-nav-btn ios-nav-cancel" @click="emit('update:show', false)">
                        <i class="pi pi-times"></i>
                    </button>
                    <span class="ios-nav-title">Tracking History</span>
                </div>

                <div class="ios-body !py-6">

                    <div class="ios-section mt-2" v-if="transaction">
                        <div class="flex text-xs text-surface-400 uppercase tracking-wide mb-2">
                            <p class="w-1/4">Transaction:</p>
                            <span class="font-semibold text-surface-300">{{ transaction.transaction_id }}</span>
                        </div>
                        <div class="flex text-xs text-surface-400 uppercase tracking-wide mb-2">
                            <p class="w-1/4">Payee:</p>
                            <span class="font-semibold text-surface-300">{{ transaction.payee_name }}</span>
                        </div>
                    </div>

                    <div class="ios-section" v-if="!isLoading && trackingData?.obr_info">
                        <div class="ios-card p-4 space-y-2">
                            <div class="flex text-xs text-surface-400 items-center">
                                <p class="w-1/4">OBR status:</p>
                                <Tag :value="trackingData.obr_info.obr_status || '—'"
                                    :severity="obrStatusSeverity(trackingData.obr_info.obr_status)" />
                            </div>
                            <div class="flex text-xs text-surface-400">
                                <p class="w-1/4">OBR date:</p>
                                <span class="font-semibold text-surface-300">{{
                                    formatDate(trackingData.obr_info.obr_date) }}</span>
                            </div>
                            <div v-if="latestTrackingRemark" class="flex text-xs text-surface-400">
                                <p class="w-1/4">Latest update:</p>
                                <span class="font-semibold text-surface-300">{{ latestTrackingRemark }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="ios-section" v-if="isLoading">
                        <div class="ios-card flex flex-col items-center gap-3 py-8 text-center">
                            <ProgressSpinner style="width: 2.5rem; height: 2.5rem" strokeWidth="6" />
                            <p class="text-sm text-surface-400">Loading tracking history...</p>
                        </div>
                    </div>

                    <!-- Timeline -->
                    <div class="ios-section" v-else-if="hasHistory">
                        <div class="relative">
                            <!-- Vertical line -->
                            <div class="absolute left-3.5 top-4 bottom-4 w-0.5 bg-surface-200 dark:bg-surface-600">
                            </div>

                            <div v-for="(entry, index) in trackingEntries" :key="index"
                                class="relative flex gap-4 pb-6 last:pb-0">
                                <!-- Dot -->
                                <div class="relative z-10 shrink-0 w-7 h-7 rounded-full flex items-center justify-center mt-0.5"
                                    :class="index === 0 ? 'bg-primary-500' : 'bg-surface-300 dark:bg-surface-600'">
                                    <div class="w-2.5 h-2.5 rounded-full bg-white"></div>
                                </div>

                                <!-- Content -->
                                <div class="flex-1 min-w-0 pt-0.5">
                                    <p class="text-sm font-medium text-surface-800 dark:text-surface-100 leading-snug">
                                        {{ entry.trn_remarks || entry.transaction_description || entry.description ||
                                        '—' }}
                                    </p>
                                    <p class="text-xs text-surface-400 mt-0.5">
                                        {{ formatDate(entry.trn_date || entry.transaction_date || entry.date) }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="ios-section" v-else>
                        <div class="ios-card flex flex-col items-center gap-3 py-8 text-center">
                            <i class="pi pi-history text-4xl text-surface-300"></i>
                            <p class="text-sm text-surface-400">No tracking history available.</p>
                        </div>
                    </div>

                </div>

            </div>
        </template>
    </Dialog>
</template>

<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
    show: Boolean,
    transaction: { type: Object, default: null },
    trackingData: { type: Object, default: null },
    isLoading: { type: Boolean, default: false },
});

const emit = defineEmits(['update:show']);

const trackingEntries = computed(() => {
    return Array.isArray(props.trackingData?.tracking_information)
        ? props.trackingData.tracking_information
        : [];
});

const latestTrackingRemark = computed(() => trackingEntries.value[0]?.trn_remarks || null);

const hasHistory = computed(() => {
    return trackingEntries.value.length > 0;
});

function formatDate(val) {
    if (!val) return '—';
    return new Date(val).toLocaleDateString('en-PH', {
        year: 'numeric', month: 'short', day: 'numeric',
        hour: '2-digit', minute: '2-digit',
    });
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

const elModal = ref(null);
const dragOffset = ref({ x: 0, y: 0 });
const dragStart = ref(null);

const modalStyle = computed(() => ({
    transform: `translate(${dragOffset.value.x}px, ${dragOffset.value.y}px)`,
}));

function onDragStart(e) {
    if (e.target.closest('button')) return;
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
