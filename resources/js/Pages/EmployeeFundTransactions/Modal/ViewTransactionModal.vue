<template>
    <Dialog :visible="show" @update:visible="val => emit('update:show', val)" modal
        :pt="{ root: { class: 'ios-dialog-root' }, mask: { class: 'ios-dialog-mask' } }">
        <template #container>
            <div ref="elModal" class="ios-modal w-175 max-w-[95vw]" :style="modalStyle">

                <div class="ios-nav-bar" @pointerdown="onDragStart">
                    <button class="ios-nav-btn ios-nav-cancel" @click="emit('update:show', false)">
                        <i class="pi pi-times"></i>
                    </button>
                    <span class="ios-nav-title">Transaction Details</span>
                </div>

                <div class="ios-body py-4!" v-if="transaction">

                    <!-- Status badge -->
                    <div class="flex items-center gap-2 mb-4">
                        <Tag :value="transaction.transaction_status"
                            :severity="statusSeverity(transaction.transaction_status)" class="capitalize" />
                        <Tag :value="formatEmployeeType(transaction.employee_type)" severity="secondary" />
                    </div>

                    <!-- Common Fields -->
                    <div class="ios-section">
                        <div class="ios-card p-4">
                            <div class="grid grid-cols-2 gap-x-6 gap-y-3 text-sm">
                                <div>
                                    <p class="text-xs text-surface-400 uppercase tracking-wide">Transaction ID</p>
                                    <p class="font-semibold">{{ transaction.transaction_id }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-surface-400 uppercase tracking-wide">Payee Name</p>
                                    <p class="font-semibold">{{ transaction.payee_name }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-surface-400 uppercase tracking-wide">Office / Unit</p>
                                    <p>{{ transaction.office || '—' }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-surface-400 uppercase tracking-wide">Payee Address</p>
                                    <p>{{ transaction.payee_address || '—' }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-surface-400 uppercase tracking-wide">Responsibility Center
                                    </p>
                                    <p>{{ transaction.responsibility_center?.name || transaction.responsibility_center
                                        || '—' }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-surface-400 uppercase tracking-wide">Account Code</p>
                                    <p>{{ transaction.account_code || '—' }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-surface-400 uppercase tracking-wide">Particulars</p>
                                    <p>{{ transaction.particulars_name || '—' }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-surface-400 uppercase tracking-wide">Amount</p>
                                    <p class="font-semibold">{{ money(transaction.amount) }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-surface-400 uppercase tracking-wide">Disbursement Type</p>
                                    <p class="capitalize">{{ transaction.disbursement_type || '—' }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-surface-400 uppercase tracking-wide">OBR Type</p>
                                    <p class="capitalize">{{ transaction.obr_type || '—' }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-surface-400 uppercase tracking-wide">OBR No.</p>
                                    <p>{{ transaction.obr_no || '—' }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-surface-400 uppercase tracking-wide">DV No.</p>
                                    <p>{{ transaction.dv_no || '—' }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-surface-400 uppercase tracking-wide">Date Obligated</p>
                                    <p>{{ formatDate(transaction.date_obligated) }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-surface-400 uppercase tracking-wide">Fiscal Year</p>
                                    <p>{{ transaction.fiscal_year || '—' }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-surface-400 uppercase tracking-wide">Created By</p>
                                    <p>{{ transaction.created_by?.name || '—' }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-surface-400 uppercase tracking-wide">Created At</p>
                                    <p>{{ formatDate(transaction.created_at) }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Particulars Description -->
                    <div class="ios-section pb-4" v-if="transaction.particulars_description">
                        <p class="ios-section-label">Particulars Description</p>
                        <div class="ios-card prose prose-sm max-w-none p-4"
                            v-html="transaction.particulars_description">
                        </div>
                    </div>

                    <!-- Remarks -->
                    <div class="ios-section pb-4" v-if="transaction.remarks">
                        <p class="ios-section-label">Remarks</p>
                        <div class="ios-card prose prose-sm max-w-none p-4" v-html="transaction.remarks"></div>
                    </div>

                </div>

                <div class="ios-body" v-else>
                    <p class="text-center text-surface-400 py-8">No transaction selected.</p>
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
});

const emit = defineEmits(['update:show']);

const elModal = ref(null);
const dragOffset = ref({ x: 0, y: 0 });
const dragStart = ref(null);

const modalStyle = computed(() => ({
    transform: `translate(${dragOffset.value.x}px, ${dragOffset.value.y}px)`,
}));

function onDragStart(e) {
    if (e.target.closest('button, input, select, textarea, .p-editor, .p-select')) return;
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

function formatDate(val) {
    if (!val) return '—';
    return new Date(val).toLocaleDateString('en-PH', { year: 'numeric', month: 'short', day: 'numeric' });
}

function money(val) {
    if (!val && val !== 0) return '—';
    return new Intl.NumberFormat('en-PH', { style: 'currency', currency: 'PHP' }).format(Number(val));
}

function statusSeverity(status) {
    const map = {
        pending: 'warn',
        approved: 'success',
        active: 'info',
        denied: 'danger',
        suspended: 'secondary',
    };
    return map[status] || 'secondary';
}

function formatEmployeeType(type) {
    if (!type) return '—';
    return type.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase());
}
</script>
