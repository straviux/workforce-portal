<template>
    <Dialog :visible="show" @update:visible="val => emit('update:show', val)" modal
        :pt="{ root: { class: 'ios-dialog-root' }, mask: { class: 'ios-dialog-mask' } }">
        <template #container>
            <div ref="elModal" class="ios-modal w-220 max-w-[95vw]" :style="computedModalStyle">

                <div class="ios-nav-bar" @pointerdown="onDragStart">
                    <button class="ios-nav-btn ios-nav-cancel" @click="emit('update:show', false)">
                        <AppIcon name="X" />
                    </button>
                    <span class="ios-nav-title">{{ mode === 'create' ? 'New Employee' : 'Edit Employee' }}</span>
                    <button class="ios-nav-btn ios-nav-action" :disabled="saveAction.disabled"
                        :class="saveAction.morphClass" @click="submit">
                        {{ saveAction.label }}
                    </button>
                </div>

                <div class="ios-body text-xs">

                    <!-- Employee Type -->
                    <div class="ios-section pb-4">
                        <p class="ios-section-label">Employee Type</p>
                        <div class="ios-card">
                            <div class="flex">
                                <div v-for="opt in typeOptions" :key="opt.value"
                                    class="flex-1 flex items-center gap-2 px-4 py-3 cursor-pointer border-r border-surface-200 dark:border-surface-700 last:border-r-0"
                                    @click="form.employee_type = opt.value">
                                    <AppIcon v-if="form.employee_type === opt.value" name="Check" :size="12" />
                                    <span class="text-sm"
                                        :class="form.employee_type === opt.value ? 'text-gray-600 font-medium' : ''">{{
                                            opt.label }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Basic Info -->
                    <div class="ios-section pb-4 -mt-1!">
                        <p class="ios-section-label">Basic Information</p>
                        <div class="ios-card p-4 space-y-3">
                            <div class="grid gap-3" style="grid-template-columns: 1fr 1fr 1fr 5rem">
                                <div class="ios-form-group">
                                    <label class="ios-label">Last Name <span class="text-red-500">*</span></label>
                                    <InputText v-model="form.last_name" placeholder="dela Cruz" class="w-full"
                                        size="small" />
                                    <span v-if="errors.last_name" class="ios-hint ios-error">{{ errors.last_name
                                        }}</span>
                                </div>
                                <div class="ios-form-group">
                                    <label class="ios-label">First Name <span class="text-red-500">*</span></label>
                                    <InputText v-model="form.first_name" placeholder="Juan" class="w-full"
                                        size="small" />
                                    <span v-if="errors.first_name" class="ios-hint ios-error">{{ errors.first_name
                                        }}</span>
                                </div>
                                <div class="ios-form-group">
                                    <label class="ios-label">Middle Name <span
                                            class="text-surface-400 font-normal">(optional)</span></label>
                                    <InputText v-model="form.middle_name" placeholder="Santos" class="w-full"
                                        size="small" />
                                </div>
                                <div class="ios-form-group">
                                    <label class="ios-label">Extension</label>
                                    <InputText v-model="form.name_extension" placeholder="JR/SR/II" class="w-full"
                                        size="small" />
                                </div>
                            </div>
                            <div class="grid gap-3" style="grid-template-columns: 1fr 1fr 1fr 5rem">
                                <div class="ios-form-group">
                                    <label class="ios-label">Employee No. <span
                                            class="text-surface-400 font-normal">(optional)</span></label>
                                    <InputText v-model="form.employee_no" placeholder="e.g. PGP-001" class="w-full"
                                        size="small" />
                                    <span v-if="errors.employee_no" class="ios-hint ios-error">{{ errors.employee_no
                                    }}</span>
                                </div>
                                <div class="ios-form-group" style="grid-column: span 3">
                                    <label class="ios-label">Address</label>
                                    <InputText v-model="form.address" placeholder="City/Municipality" class="w-full"
                                        size="small" />
                                </div>
                            </div>

                        </div>
                    </div>

                    <!-- COS-specific Fields -->
                    <div v-if="form.employee_type === 'contract_of_service'" class="ios-section pb-6 -mt-1!">
                        <p class="ios-section-label">Contract of Service Details</p>
                        <div class="ios-card p-4 space-y-3">
                            <div class="grid grid-cols-3 gap-3">
                                <div class="ios-form-group">
                                    <label class="ios-label">Designation / Position</label>
                                    <InputText v-model="form.designation" placeholder="e.g. Administrative Aide"
                                        class="w-full" size="small" />
                                </div>
                                <div class="ios-form-group">
                                    <label class="ios-label">Office / Unit</label>
                                    <InputText v-model="form.office" placeholder="Office or department" class="w-full"
                                        size="small" />
                                </div>
                                <div class="ios-form-group">
                                    <label class="ios-label">Agency <span
                                            class="text-surface-400 font-normal">(optional)</span></label>
                                    <InputText v-model="form.agency" placeholder="Implementing agency" class="w-full"
                                        size="small" />
                                </div>
                            </div>
                            <div class="ios-form-group">
                                <label class="ios-label">Office of Assignment <span
                                        class="text-surface-400 font-normal">(optional)</span></label>
                                <InputText v-model="form.office_of_assignment"
                                    placeholder="Assigned office, if different from Office" class="w-full"
                                    size="small" />
                            </div>
                            <div class="grid grid-cols-3 gap-3">
                                <div class="ios-form-group">
                                    <label class="ios-label">Contract Ref. No.</label>
                                    <InputText v-model="form.contract_ref_no" placeholder="Contract reference"
                                        class="w-full" size="small" />
                                </div>
                                <div class="ios-form-group">
                                    <label class="ios-label">ATM Account No.</label>
                                    <InputText v-model="form.atm_account_no" placeholder="ATM account number"
                                        class="w-full" size="small" />
                                </div>
                                <div class="ios-form-group">
                                    <label class="ios-label">Monthly Compensation <span
                                            class="text-red-500">*</span></label>
                                    <InputNumber v-model="form.monthly_compensation" mode="currency" currency="PHP"
                                        locale="en-PH" class="w-full" placeholder="0.00" inputClass="w-full"
                                        size="small" />
                                    <span v-if="errors.monthly_compensation" class="ios-hint ios-error">{{
                                        errors.monthly_compensation }}</span>
                                </div>
                            </div>
                            <div class="grid grid-cols-3 gap-3">
                                <div class="ios-form-group">
                                    <label class="ios-label">SSS Deduction</label>
                                    <InputNumber v-model="form.deduction_sss" mode="currency" currency="PHP"
                                        locale="en-PH" class="w-full" placeholder="0.00" size="small" />
                                </div>
                                <div class="ios-form-group">
                                    <label class="ios-label">PhilHealth Deduction</label>
                                    <InputNumber v-model="form.deduction_philhealth" mode="currency" currency="PHP"
                                        locale="en-PH" class="w-full" placeholder="0.00" size="small" />
                                </div>
                                <div class="ios-form-group">
                                    <label class="ios-label">HDMF Deduction</label>
                                    <InputNumber v-model="form.deduction_hdmf" mode="currency" currency="PHP"
                                        locale="en-PH" class="w-full" placeholder="0.00" size="small" />
                                </div>
                            </div>

                        </div>
                    </div>

                    <!-- Project-Based Fields -->
                    <div v-if="form.employee_type === 'project_based'" class="ios-section pb-6 -mt-1!">
                        <p class="ios-section-label">Project-Based Details</p>
                        <div class="ios-card p-4">
                            <div class="grid grid-cols-3 gap-3">
                                <div class="ios-form-group">
                                    <label class="ios-label">Office / Unit</label>
                                    <InputText v-model="form.office" placeholder="Office or department" class="w-full"
                                        size="small" />
                                </div>
                                <div class="ios-form-group">
                                    <label class="ios-label">Agency</label>
                                    <InputText v-model="form.agency" placeholder="Implementing agency" class="w-full"
                                        size="small" />
                                </div>
                                <div class="ios-form-group">
                                    <label class="ios-label">Amount</label>
                                    <InputNumber v-model="form.amount" mode="currency" currency="PHP" locale="en-PH"
                                        class="w-full" placeholder="0.00" inputClass="w-full" size="small" />
                                </div>
                            </div>
                            <div class="ios-form-group mt-3">
                                <label class="ios-label">Office of Assignment <span
                                        class="text-surface-400 font-normal">(optional)</span></label>
                                <InputText v-model="form.office_of_assignment"
                                    placeholder="Assigned office, if different from Office" class="w-full"
                                    size="small" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </Dialog>
</template>

<script setup>
import { ref, reactive, watch, computed } from 'vue';
import { useToast } from 'primevue/usetoast';
import axios from 'axios';
import { useActionButton } from '@/Composables/useActionButton';

const props = defineProps({
    show: Boolean,
    employee: { type: Object, default: null },
    mode: { type: String, default: 'create' }, // 'create' | 'edit'
});

const emit = defineEmits(['update:show', 'saved']);

const toast = useToast();
// NOTE: wrapped in reactive() — the composable returns an object of refs,
// and template prop bindings (e.g. :class="saveAction.morphClass") do NOT
// unwrap nested refs in script-setup SFCs (verified: raw use renders
// class="dep __v_isRef"). reactive() unwraps them on access; run() still
// works because it closes over the composable's own refs. In script,
// read saveAction.state as a plain string, never .value.
const saveAction = reactive(useActionButton({ idleLabel: 'Save', processingLabel: 'Saving...', successLabel: 'Saved' }));
const errors = ref({});

const typeOptions = [
    { label: 'Contract of Service', value: 'contract_of_service' },
    { label: 'Project-Based', value: 'project_based' },
];

const defaultForm = () => ({
    employee_no: '',
    first_name: '',
    middle_name: '',
    last_name: '',
    name_extension: '',
    address: '',
    office: '',
    office_of_assignment: '',
    designation: '',
    employee_type: 'contract_of_service',
    contract_ref_no: '',
    atm_account_no: '',
    monthly_compensation: null,
    deduction_sss: null,
    deduction_philhealth: null,
    deduction_hdmf: null,
    agency: '',
    amount: null,
});

const form = reactive(defaultForm());

watch(() => props.show, (val) => {
    if (val) {
        errors.value = {};
        if (props.mode === 'edit' && props.employee) {
            Object.assign(form, {
                employee_no: props.employee.employee_no || '',
                first_name: props.employee.first_name || '',
                middle_name: props.employee.middle_name || '',
                last_name: props.employee.last_name || '',
                name_extension: props.employee.name_extension || '',
                address: props.employee.address || '',
                office: props.employee.office || '',
                office_of_assignment: props.employee.office_of_assignment || '',
                designation: props.employee.designation || '',
                employee_type: props.employee.employee_type || 'contract_of_service',
                contract_ref_no: props.employee.contract_ref_no || '',
                atm_account_no: props.employee.atm_account_no || '',
                monthly_compensation: props.employee.monthly_compensation ? parseFloat(props.employee.monthly_compensation) : null,
                deduction_sss: props.employee.deduction_sss ? parseFloat(props.employee.deduction_sss) : null,
                deduction_philhealth: props.employee.deduction_philhealth ? parseFloat(props.employee.deduction_philhealth) : null,
                deduction_hdmf: props.employee.deduction_hdmf ? parseFloat(props.employee.deduction_hdmf) : null,
                agency: props.employee.agency || '',
                amount: props.employee.amount ? parseFloat(props.employee.amount) : null,
            });
        } else {
            Object.assign(form, defaultForm());
        }
    }
});

async function submit() {
    if (saveAction.state !== 'idle') return;
    errors.value = {};
    try {
        await saveAction.run(async () => {
            try {
                const payload = { ...form };
                // Clear type-specific fields
                if (payload.employee_type === 'project_based') {
                    payload.contract_ref_no = null;
                    payload.atm_account_no = null;
                    payload.monthly_compensation = null;
                    payload.deduction_sss = null;
                    payload.deduction_philhealth = null;
                    payload.deduction_hdmf = null;
                } else {
                    payload.agency = null;
                    payload.amount = null;
                    payload.designation = payload.designation || null;
                }

                if (props.mode === 'edit') {
                    await axios.put(`/api/employees/${props.employee.id}`, payload);
                } else {
                    await axios.post('/api/employees', payload);
                }

                toast.add({
                    severity: 'success',
                    summary: props.mode === 'edit' ? 'Updated' : 'Created',
                    detail: `Employee ${props.mode === 'edit' ? 'updated' : 'created'} successfully.`,
                    life: 3000,
                });
            } catch (err) {
                if (err.response?.status === 422) {
                    errors.value = err.response.data.errors ?? {};
                    toast.add({ severity: 'warn', summary: 'Validation Error', detail: 'Please check the form.', life: 3500 });
                } else {
                    toast.add({ severity: 'error', summary: 'Error', detail: 'Could not save employee.', life: 3500 });
                }
                throw err;
            }
        });
    } catch {
        return;
    }
    // Only after the full morph cycle (run resolves after success shows).
    emit('saved');
}

// ── Drag ─────────────────────────────────────────────────
const elModal = ref(null);
const dragOffset = ref({ x: 0, y: 0 });
const dragStart = ref(null);

const computedModalStyle = computed(() => ({
    transform: `translate(${dragOffset.value.x}px, ${dragOffset.value.y}px)`,
}));

function onDragStart(e) {
    if (e.target.closest('button, input, .p-select, .p-inputnumber, .p-toggleswitch')) return;
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
