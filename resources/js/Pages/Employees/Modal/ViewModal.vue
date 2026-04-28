<template>
    <Dialog :visible="show" @update:visible="val => emit('update:show', val)" modal
        :pt="{ root: { class: 'ios-dialog-root' }, mask: { class: 'ios-dialog-mask' } }">
        <template #container>
            <div ref="elModal" class="ios-modal w-175 max-w-[95vw]" :style="modalStyle">

                <div class="ios-nav-bar" @pointerdown="onDragStart">
                    <button class="ios-nav-btn ios-nav-cancel" @click="emit('update:show', false)">
                        <i class="pi pi-times"></i>
                    </button>
                    <span class="ios-nav-title">Employee Details</span>
                    <button class="ios-nav-btn ios-nav-action" @click="emit('edit', employee)"
                        v-tooltip="'Edit Employee'">
                        <i class="pi pi-pencil text-amber-500"></i>
                    </button>
                </div>

                <div class="ios-body !pt-2 !pb-6" v-if="employee">

                    <!-- Status badges -->
                    <div class="flex items-center gap-2 mt-4 mb-2">
                        <Tag :value="formatType(employee.employee_type)" severity="secondary" />
                        <Tag :value="employee.is_active ? 'Active' : 'Inactive'"
                            :severity="employee.is_active ? 'success' : 'secondary'" />

                    </div>

                    <!-- Basic Info -->
                    <div class="ios-section">
                        <p class="ios-section-label">Basic Information</p>
                        <div class="ios-card">
                            <div class="grid grid-cols-2 gap-x-6 gap-y-3 text-sm p-4">
                                <div>
                                    <p class="text-xs text-surface-400 uppercase tracking-wide">Employee No.</p>
                                    <p class="font-mono font-semibold">{{ employee.employee_no || '—' }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-surface-400 uppercase tracking-wide">Last Name</p>
                                    <p class="font-semibold">{{ employee.last_name }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-surface-400 uppercase tracking-wide">First Name</p>
                                    <p class="font-semibold">{{ employee.first_name }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-surface-400 uppercase tracking-wide">Middle Name</p>
                                    <p>{{ employee.middle_name || '—' }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-surface-400 uppercase tracking-wide">Extension</p>
                                    <p>{{ employee.name_extension || '—' }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-surface-400 uppercase tracking-wide">Address</p>
                                    <p>{{ employee.address || '—' }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-surface-400 uppercase tracking-wide">Office / Unit</p>
                                    <p>{{ employee.office || '—' }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-surface-400 uppercase tracking-wide">Employee Type</p>
                                    <p>{{ formatType(employee.employee_type) }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-surface-400 uppercase tracking-wide">Created By</p>
                                    <p>{{ employee.creator?.name || '—' }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-surface-400 uppercase tracking-wide">Created At</p>
                                    <p>{{ formatDate(employee.created_at) }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- COS Details -->
                    <div class="ios-section" v-if="employee.employee_type === 'contract_of_service'">
                        <p class="ios-section-label text-primary-400">Contract of Service Details</p>
                        <div class="ios-card">
                            <div class="grid grid-cols-2 gap-x-6 gap-y-3 text-sm p-4">
                                <div>
                                    <p class="text-xs text-surface-400 uppercase tracking-wide">Contract Ref. No.</p>
                                    <p>{{ employee.contract_ref_no || '—' }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-surface-400 uppercase tracking-wide">ATM Account No.</p>
                                    <p>{{ employee.atm_account_no || '—' }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-surface-400 uppercase tracking-wide">Monthly Compensation</p>
                                    <p class="font-semibold">{{ money(employee.monthly_compensation) }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-surface-400 uppercase tracking-wide">SSS Deduction</p>
                                    <p>{{ money(employee.deduction_sss) }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-surface-400 uppercase tracking-wide">PhilHealth Deduction</p>
                                    <p>{{ money(employee.deduction_philhealth) }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-surface-400 uppercase tracking-wide">HDMF Deduction</p>
                                    <p>{{ money(employee.deduction_hdmf) }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-surface-400 uppercase tracking-wide">Net Pay</p>
                                    <p class="font-semibold text-green-600 dark:text-green-400">{{ netPay }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <div class="ios-body" v-else>
                    <p class="text-center text-surface-400 py-8">No employee selected.</p>
                </div>

            </div>
        </template>
    </Dialog>
</template>

<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
    show: Boolean,
    employee: { type: Object, default: null },
});

const emit = defineEmits(['update:show', 'edit']);

const netPay = computed(() => {
    if (!props.employee?.monthly_compensation) return '—';
    const gross = parseFloat(props.employee.monthly_compensation) || 0;
    const sss = parseFloat(props.employee.deduction_sss) || 0;
    const ph = parseFloat(props.employee.deduction_philhealth) || 0;
    const hdmf = parseFloat(props.employee.deduction_hdmf) || 0;
    return money(gross - sss - ph - hdmf);
});

function formatType(val) {
    return val === 'contract_of_service' ? 'Contract of Service' : 'Project-Based';
}

function formatDate(val) {
    if (!val) return '—';
    return new Date(val).toLocaleDateString('en-PH', { year: 'numeric', month: 'short', day: 'numeric' });
}

function money(val) {
    if (!val && val !== 0) return '—';
    return new Intl.NumberFormat('en-PH', { style: 'currency', currency: 'PHP' }).format(val);
}

// ── Drag ─────────────────────────────────────────────────
const elModal = ref(null);
const dragOffset = ref({ x: 0, y: 0 });
const dragStart = ref(null);

const modalStyle = computed(() => ({
    transform: `translate(${dragOffset.value.x}px, ${dragOffset.value.y}px)`,
}));

function onDragStart(e) {
    if (e.target.closest('button, input, select, textarea')) return;
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
