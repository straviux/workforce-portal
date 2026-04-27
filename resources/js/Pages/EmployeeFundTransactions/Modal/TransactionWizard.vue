<template>
    <Dialog :visible="show" @update:visible="val => emit('update:show', val)" modal
        :pt="{ root: { class: 'ios-dialog-root' }, mask: { class: 'ios-dialog-mask' } }">
        <template #container>
            <div ref="elModal" class="ios-modal w-250 max-w-[97vw]" :style="modalStyle">

                <!-- Nav Bar -->
                <div class="ios-nav-bar" @pointerdown="onDragStart">
                    <button class="ios-nav-btn ios-nav-cancel"
                        @click="step === 2 ? step = 1 : emit('update:show', false)">
                        <i :class="step === 2 ? 'pi pi-arrow-left' : 'pi pi-times'"></i>
                    </button>
                    <span class="ios-nav-title">{{ wizardTitle }}</span>
                    <button v-if="step === 1" class="ios-nav-btn ios-nav-action"
                        :disabled="selectedEmployees.length === 0" @click="goNext">
                        <i class="pi pi-chevron-right"></i>
                    </button>
                    <button v-else class="ios-nav-btn ios-nav-action" :disabled="saving" @click="submit">
                        {{ saving ? `Saving…` : (mode === `edit` ? `Update` : `Save`) }}
                    </button>
                </div>

                <!-- Step dots -->
                <div class="py-2.5 flex items-center justify-center gap-1.5 flex-shrink-0">
                    <span v-for="n in 2" :key="n" class="rounded-full transition-all duration-300" :style="step === n
                        ? 'width:20px;height:6px;background:var(--p-primary-500)'
                        : 'width:6px;height:6px;background:#d1d5db'">
                    </span>
                </div>

                <!-- ── Step 1: Select Employee ── -->
                <div v-show="step === 1" class="flex overflow-hidden px-2" style="height:540px;">

                    <!-- Left: Filter & List -->
                    <div class="flex flex-col border-r" style="width:55%;">

                        <!-- Panel header -->
                        <div class="px-5 py-3 border-b border-surface-100 dark:border-surface-700 flex-shrink-0">
                            <p class="text-sm font-semibold text-surface-700 dark:text-surface-200">Select Employee</p>
                        </div>

                        <!-- Info banner -->
                        <div class="mx-4 mt-3 px-3 py-2 rounded-xl flex items-start gap-2 text-xs flex-shrink-0"
                            style="background:#e8f0fe;color:#1e40af;">
                            <i class="pi pi-info-circle mt-0.5 flex-shrink-0" style="font-size:13px;"></i>
                            <span>Select an employee type to filter the list, then search by name.</span>
                        </div>

                        <!-- Type dropdown -->
                        <div class="px-4 pt-3 pb-2 flex-shrink-0">
                            <Select v-model="form.employee_type" :options="typeOptions" optionLabel="label"
                                optionValue="value" class="w-full" size="small" @update:modelValue="setEmployeeType" />
                        </div>

                        <!-- Search -->
                        <div class="px-4 pb-2 flex-shrink-0">
                            <IconField>
                                <InputIcon class="pi pi-search" />
                                <InputText v-model="employeeSearch" placeholder="Search name, employee no…"
                                    class="w-full" size="small" />
                            </IconField>
                        </div>

                        <!-- Count row -->
                        <div class="px-4 py-2 border-y border-surface-100 dark:border-surface-700 flex-shrink-0">
                            <span class="text-xs text-surface-500">
                                <span v-if="loadingEmployees"><i class="pi pi-spin pi-spinner mr-1"
                                        style="font-size:11px;"></i>Loading…</span>
                                <span v-else>{{ filteredEmployees.length }} found</span>
                            </span>
                        </div>

                        <!-- Scrollable list -->
                        <div class="flex-1 overflow-y-auto divide-y divide-surface-100 dark:divide-surface-800">
                            <div v-if="loadingEmployees" class="py-12 text-center text-surface-400 text-xs">
                                <i class="pi pi-spin pi-spinner text-2xl block mb-2"></i>
                                Loading employees…
                            </div>
                            <div v-else-if="filteredEmployees.length === 0"
                                class="py-12 text-center text-surface-400 text-xs">
                                <i class="pi pi-users text-2xl block mb-2"></i>
                                No active employees found.
                            </div>
                            <template v-else>
                                <div v-for="emp in filteredEmployees" :key="emp.id"
                                    class="flex items-center gap-3 px-4 py-2 cursor-pointer transition-colors"
                                    :class="isSelected(emp) ? 'bg-blue-50 dark:bg-primary-900/20' : 'hover:bg-surface-50 dark:hover:bg-surface-800'"
                                    @click="toggleEmployee(emp)">
                                    <Checkbox :modelValue="isSelected(emp)" :binary="true"
                                        class="pointer-events-none flex-shrink-0" />
                                    <span
                                        class="flex-1 text-xs font-semibold text-surface-800 dark:text-surface-100 truncate">{{
                                            emp.full_name }}</span>
                                    <span v-if="emp.office" class="text-xs text-surface-400 truncate"
                                        style="max-width:100px;">{{ emp.office }}</span>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Right: Selected Employee panel -->
                    <div class="flex flex-col" style="flex:1;">

                        <!-- Panel header -->
                        <div class="px-5 py-3 border-b  flex items-center gap-2 flex-shrink-0">
                            <p class="text-sm font-semibold text-surface-700 dark:text-surface-200">Selected Employees
                            </p>
                            <span class="text-xs text-surface-400 font-normal">({{ selectedEmployees.length }})</span>
                        </div>

                        <!-- Content -->
                        <div class="flex-1 overflow-y-auto p-4" style="background:#f2f2f7;">
                            <!-- Empty state -->
                            <div v-if="selectedEmployees.length === 0"
                                class="h-full flex flex-col items-center justify-center gap-2 text-surface-400">
                                <i class="pi pi-users text-4xl opacity-20"></i>
                                <p class="text-sm">No employees selected yet</p>
                            </div>

                            <!-- Selected cards -->
                            <div v-else class="flex flex-col gap-2">
                                <div v-for="emp in selectedEmployees" :key="emp.id"
                                    class="flex items-center justify-between gap-3 px-3 py-2 rounded-xl bg-white dark:bg-primary-900/20">
                                    <div class="min-w-0">
                                        <p
                                            class="text-xs font-semibold text-surface-800 dark:text-surface-100 truncate">
                                            {{ emp.full_name ||
                                                emp.payee_name }}</p>
                                        <p v-if="emp.office" class="text-xs text-surface-400 truncate">{{ emp.office }}
                                        </p>
                                    </div>
                                    <button
                                        class="text-surface-300 hover:text-red-400 transition-colors flex-shrink-0 cursor-pointer"
                                        @click="toggleEmployee(emp)">
                                        <i class="pi pi-times text-xs! text-red-400"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ── Step 2: Transaction Details ── -->
                <div v-show="step === 2" class="ios-body text-xs">

                    <!-- Card 2: Payee + payroll header details -->
                    <div class="ios-section">
                        <div class="ios-card p-4">
                            <div class="grid gap-4" :class="isProjectBased ? 'grid-cols-4' : 'grid-cols-3'">
                                <div class="ios-form-group">
                                    <label class="ios-label">Payee <span class="text-red-500">*</span></label>
                                    <InputText v-model="form.payee_name" class="w-full" size="small"
                                        placeholder="Payee name…" />
                                    <span v-if="errors.payee_name" class="ios-hint ios-error">{{ errors.payee_name
                                    }}</span>
                                </div>

                                <div v-if="isProjectBased" class="ios-form-group">
                                    <label class="ios-label">Implementing Agency</label>
                                    <InputText v-model="form.agency" class="w-full" size="small"
                                        placeholder="Implementing agency for payroll…" />
                                </div>

                                <div class="ios-form-group">
                                    <label class="ios-label">Office</label>
                                    <InputText v-model="form.office" class="w-full" size="small"
                                        placeholder="Office or unit…" />
                                </div>
                                <div class="ios-form-group">
                                    <label class="ios-label">Address</label>
                                    <InputText v-model="form.payee_address" class="w-full" size="small"
                                        placeholder="Enter payee address…" />
                                </div>
                            </div>

                            <p v-if="isProjectBased" class="mt-3 text-xs text-surface-400">
                                Implementing agency and office are used only in payroll generation for project-based
                                transactions.
                            </p>
                        </div>
                    </div>

                    <!-- Card 3: RC / Particulars | Selected Employee + Amount -->
                    <div class="ios-section">
                        <div class="ios-card p-4 flex gap-5" style="min-height:210px;">

                            <!-- Left: RC, Particulars, Account Code -->
                            <div class="flex flex-col gap-3" style="flex:1;">
                                <div class="ios-form-group">
                                    <label class="ios-label">Responsibility Center <span
                                            class="text-red-500">*</span></label>
                                    <ResponsibilityCenterSelect v-model="form.responsibility_center"
                                        :fiscal-year="form.fiscal_year" @change="onRcChange" />
                                    <span v-if="errors.responsibility_center" class="ios-hint ios-error">{{
                                        errors.responsibility_center
                                        }}</span>
                                </div>
                                <div class="ios-form-group">
                                    <label class="ios-label">Particulars</label>
                                    <ParticularsSelect v-model="form.particulars_id"
                                        :responsibility-center-id="form.responsibility_center"
                                        :account-code="form.account_code" @change="onParticularChange" />
                                </div>
                                <div class="ios-form-group">
                                    <label class="ios-label">Account Code</label>
                                    <InputText v-model="form.account_code" class="w-full" size="small" readonly />
                                </div>
                            </div>

                            <!-- Vertical divider -->
                            <div class="w-px self-stretch bg-surface-100 dark:bg-surface-700 flex-shrink-0"></div>

                            <!-- Right: Selected Employees list -->
                            <div class="flex flex-col" style="flex:1;">
                                <div class="flex items-center mb-3">
                                    <span class="text-xs font-semibold text-surface-700 dark:text-surface-200">
                                        Selected Employees ({{ selectedEmployees.length }})
                                    </span>
                                </div>
                                <div v-if="selectedEmployees.length === 0"
                                    class="flex-1 flex items-center justify-center text-xs text-surface-400 italic">
                                    No employees selected
                                </div>
                                <div v-else
                                    class="flex flex-col divide-y divide-surface-100 dark:divide-surface-700 overflow-y-auto">
                                    <div v-for="emp in selectedEmployees" :key="emp.id"
                                        class="flex items-center gap-3 py-2">
                                        <div class="min-w-0 flex-1">
                                            <span
                                                class="block text-xs font-semibold text-primary-600 dark:text-primary-400 truncate uppercase">{{
                                                    emp.full_name || emp.payee_name }}</span>
                                            <span v-if="!isProjectBased && emp.monthly_compensation"
                                                class="text-xs text-surface-400 flex-shrink-0">
                                                {{ money(emp.monthly_compensation) }}/mo
                                            </span>
                                            <span
                                                v-else-if="isProjectBased && resolveSelectedEmployeeAmount(emp) !== null"
                                                class="text-xs text-surface-400 flex-shrink-0">
                                                {{ money(resolveSelectedEmployeeAmount(emp)) }}
                                            </span>
                                        </div>
                                        <div v-if="!isProjectBased" class="flex items-center gap-2 flex-shrink-0">
                                            <label class="text-[11px] text-surface-500 whitespace-nowrap">Lost
                                                Hour</label>
                                            <InputNumber v-model="emp.lost_hour_minutes" inputId="lost-hour-minutes"
                                                :min="0" :useGrouping="false" size="small" placeholder="Minutes"
                                                class="w-28" inputClass="w-full text-right" />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Date From / To -->
                    <div class="ios-section">
                        <div class="ios-card p-4">
                            <div class="grid grid-cols-2 gap-4">
                                <div class="ios-form-group">
                                    <label class="ios-label">Date From</label>
                                    <DatePicker v-model="form.date_from" class="w-full" size="small" showIcon
                                        iconDisplay="input" dateFormat="mm/dd/yy" placeholder="Select date from…" />
                                </div>
                                <div class="ios-form-group">
                                    <label class="ios-label">Date To</label>
                                    <DatePicker v-model="form.date_to" class="w-full" size="small" showIcon
                                        iconDisplay="input" dateFormat="mm/dd/yy" placeholder="Select date to…" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Explanation + Particulars Description -->
                    <div class="ios-section">
                        <div class="ios-card p-4 grid grid-cols-2 gap-4">
                            <div class="ios-form-group">
                                <label class="ios-label">Explanation</label>
                                <Editor v-model="form.explanation" editorStyle="height: 120px">
                                    <template #toolbar>
                                        <span class="ql-formats">
                                            <button class="ql-bold"></button>
                                            <button class="ql-italic"></button>
                                            <button class="ql-underline"></button>
                                        </span>
                                        <span class="ql-formats">
                                            <button class="ql-list" value="ordered"></button>
                                            <button class="ql-list" value="bullet"></button>
                                        </span>
                                        <span class="ql-formats">
                                            <button class="ql-clean"></button>
                                        </span>
                                    </template>
                                </Editor>
                            </div>
                            <div class="ios-form-group">
                                <label class="ios-label">Particulars Description</label>
                                <Editor v-model="form.particulars_description" editorStyle="height: 120px">
                                    <template #toolbar>
                                        <span class="ql-formats">
                                            <button class="ql-bold"></button>
                                            <button class="ql-italic"></button>
                                            <button class="ql-underline"></button>
                                        </span>
                                        <span class="ql-formats">
                                            <button class="ql-list" value="ordered"></button>
                                            <button class="ql-list" value="bullet"></button>
                                        </span>
                                        <span class="ql-formats">
                                            <button class="ql-clean"></button>
                                        </span>
                                    </template>
                                </Editor>
                            </div>
                        </div>
                    </div>

                    <div class="h-6" />
                </div>

            </div>
        </template>
    </Dialog>
</template>

<script setup>
import { ref, reactive, computed, watch, toRaw } from 'vue';
import { useToast } from 'primevue/usetoast';
import axios from 'axios';
import ResponsibilityCenterSelect from '@/Components/selects/ResponsibilityCenterSelect.vue';
import ParticularsSelect from '@/Components/selects/ParticularsSelect.vue';

const props = defineProps({
    show: Boolean,
    transaction: { type: Object, default: null },
    mode: { type: String, default: 'create' }, // 'create' | 'edit'
});

const emit = defineEmits(['update:show', 'saved']);
const toast = useToast();

// ── Wizard state ──
const step = ref(1);
const saving = ref(false);
const errors = ref({});

// ── Employee selection ──
const allEmployees = ref([]);
const loadingEmployees = ref(false);
const employeeSearch = ref('');
const selectedEmployees = ref([]);

const typeOptions = [
    { label: 'Contract of Service', value: 'contract_of_service' },
    { label: 'Project-Based', value: 'project_based' },
];

const obrTypeOptions = [
    { label: 'Regular', value: 'REGULAR' },
    { label: 'Financial Assistance', value: 'FINANCIAL ASSISTANCE' },
    { label: 'Reimbursement', value: 'REIMBURSEMENT' },
];

const transactionStatusOptions = [
    { label: 'Pending', value: 'pending' },
    { label: 'On Process', value: 'on_process' },
    { label: 'Approved', value: 'approved' },
    { label: 'Active', value: 'active' },
    { label: 'Denied', value: 'denied' },
    { label: 'Suspended', value: 'suspended' },
];

const wizardTitle = computed(() => `${props.mode === 'edit' ? 'Edit' : 'Create'} — ${step.value === 1 ? 'Select Employees' : 'Obligation & Employees'}`);;
const stepLabel = computed(() => step.value === 1 ? 'Select Employee' : 'Obligation & Employee');
const isProjectBased = computed(() => form.employee_type === 'project_based');

const filteredEmployees = computed(() => {
    const q = employeeSearch.value.trim().toLowerCase();
    return allEmployees.value.filter(e => {
        if (!q) return true;
        return (
            e.full_name?.toLowerCase().includes(q) ||
            e.employee_no?.toLowerCase().includes(q) ||
            e.office?.toLowerCase().includes(q)
        );
    });
});

function onSearchInput() {
    // Filtering is computed; no additional action needed
}

async function fetchEmployees(type) {
    loadingEmployees.value = true;
    try {
        const res = await axios.get('/api/employees', {
            params: { is_active: 1, employee_type: type, per_page: 300 },
        });
        allEmployees.value = res.data.data ?? [];
    } catch {
        allEmployees.value = [];
    } finally {
        loadingEmployees.value = false;
    }
}

function setEmployeeType(type) {
    form.employee_type = type;
    form.payee_name = '';
    form.payee_address = '';
    form.office = '';
    form.agency = '';
    selectedEmployees.value = [];
    employeeSearch.value = '';
    fetchEmployees(type);
}

function normalizeLostHourMinutes(value) {
    if (value === null || value === undefined || value === '') {
        return null;
    }

    const parsed = Number.parseInt(value, 10);

    if (Number.isNaN(parsed) || parsed < 0) {
        return null;
    }

    return parsed;
}

function normalizeAmount(value) {
    if (value === null || value === undefined || value === '') {
        return null;
    }

    const parsed = Number.parseFloat(value);

    if (Number.isNaN(parsed) || parsed < 0) {
        return null;
    }

    return parsed;
}

function resolveSelectedEmployeeAmount(emp) {
    return normalizeAmount(emp.amount ?? emp.employee_record?.amount ?? emp.employeeRecord?.amount);
}

function normalizeSelectedEmployee(emp) {
    return {
        ...emp,
        amount: resolveSelectedEmployeeAmount(emp),
        lost_hour_minutes: normalizeLostHourMinutes(emp.lost_hour_minutes ?? emp.lostHour),
    };
}

function isSelected(emp) {
    return selectedEmployees.value.some(e => (e.employee_record_id ?? e.id) === emp.id);
}

function toggleEmployee(emp) {
    const idx = selectedEmployees.value.findIndex(e => (e.employee_record_id ?? e.id) === emp.id);
    if (idx >= 0) {
        selectedEmployees.value.splice(idx, 1);
    } else {
        selectedEmployees.value.push(normalizeSelectedEmployee(emp));
    }
}

function goNext() {
    if (selectedEmployees.value.length === 0) return;
    applySelectedEmployeeDefaults();
    step.value = 2;
}

function applySelectedEmployeeDefaults() {
    if (props.mode === 'edit') return;

    const first = selectedEmployees.value[0];

    if (!first) return;

    if (!String(form.payee_name ?? '').trim()) {
        form.payee_name = first.full_name || first.payee_name || '';
    }

    if (!String(form.payee_address ?? '').trim()) {
        form.payee_address = first.address || first.payee_address || '';
    }

    if (!String(form.office ?? '').trim()) {
        form.office = first.office || '';
    }

    if (isProjectBased.value && !String(form.agency ?? '').trim()) {
        form.agency = first.agency || '';
    }
}

// ── Form ──
const defaultForm = () => ({
    employee_type: 'contract_of_service',
    employee_record_id: null,
    payee_name: '',
    payee_address: '',
    agency: '',
    office: '',
    responsibility_center: null,
    particulars_id: null,
    account_code: '',
    particulars_name: '',
    particulars_description: '',
    amount: null,
    fiscal_year: new Date().getFullYear().toString(),
    disbursement_type: '',
    explanation: '',
    transaction_status: 'pending',
    obr_type: '',
    obr_no: '',
    dv_no: '',
    date_obligated: null,
    date_from: null,
    date_to: null,
    employee_id: '',
    contract_ref_no: '',
    atm_account_no: '',
    monthly_compensation: null,
    deduction_sss: null,
    deduction_philhealth: null,
    deduction_hdmf: null,
});

const form = reactive(defaultForm());

// ── Watch show to init ──
watch(() => props.show, (val) => {
    if (val) {
        errors.value = {};
        step.value = 1;
        employeeSearch.value = '';

        if (props.mode === 'edit' && props.transaction) {
            const t = props.transaction;
            Object.assign(form, {
                employee_type: t.employee_type || 'contract_of_service',
                employee_record_id: t.employee_record_id ?? null,
                payee_name: t.payee_name || '',
                payee_address: t.payee_address || '',
                agency: t.agency || '',
                office: t.office || '',
                responsibility_center: t.responsibility_center?.id ?? t.responsibility_center ?? null,
                particulars_id: t.particulars_id ?? null,
                account_code: t.account_code || '',
                particulars_name: t.particulars_name || '',
                particulars_description: t.particulars_description || '',
                amount: t.amount ? Number(t.amount) : null,
                fiscal_year: t.fiscal_year || new Date().getFullYear().toString(),
                disbursement_type: t.disbursement_type || '',
                explanation: t.explanation || '',
                transaction_status: t.transaction_status || 'pending',
                obr_type: t.obr_type || '',
                obr_no: t.obr_no || '',
                dv_no: t.dv_no || '',
                date_obligated: t.date_obligated ? new Date(t.date_obligated) : null,
                date_from: t.date_from ? new Date(t.date_from) : null,
                date_to: t.date_to ? new Date(t.date_to) : null,
                employee_id: t.employee_id || '',
                contract_ref_no: t.contract_ref_no || '',
                atm_account_no: t.atm_account_no || '',
                monthly_compensation: t.monthly_compensation ? Number(t.monthly_compensation) : null,
                deduction_sss: t.deduction_sss ? Number(t.deduction_sss) : null,
                deduction_philhealth: t.deduction_philhealth ? Number(t.deduction_philhealth) : null,
                deduction_hdmf: t.deduction_hdmf ? Number(t.deduction_hdmf) : null,
            });
            // In edit mode, restore selected employees from transaction.employees
            if (t.employees?.length) {
                selectedEmployees.value = t.employees.map(normalizeSelectedEmployee);
            } else if (t.employee_record) {
                selectedEmployees.value = [normalizeSelectedEmployee(t.employee_record)];
            } else {
                selectedEmployees.value = [];
            }
            // Load employees list for the type
            fetchEmployees(form.employee_type);
        } else {
            Object.assign(form, defaultForm());
            selectedEmployees.value = [];
            fetchEmployees('contract_of_service');
        }
    }
});

// ── RC / Particulars callbacks ──
function onRcChange() {
    form.particulars_id = null;
    form.account_code = '';
    form.particulars_name = '';
}

function onParticularChange(particular) {
    if (particular) {
        form.account_code = particular.account_code || '';
        form.particulars_name = particular.name || '';
    }
}

// ── Submit ──
async function submit() {
    saving.value = true;
    errors.value = {};
    try {
        const payload = buildMainPayload();

        if (props.mode === 'edit') {
            await axios.put(`/api/employee-fund-transactions/${props.transaction.id}`, payload);
        } else {
            await axios.post('/api/employee-fund-transactions', payload);
        }

        toast.add({
            severity: 'success',
            summary: props.mode === 'edit' ? 'Updated' : 'Created',
            detail: props.mode === 'edit'
                ? 'Transaction updated successfully.'
                : `Transaction created with ${selectedEmployees.value.length} employee(s).`,
            life: 3000,
        });
        emit('saved');
    } catch (err) {
        if (err.response?.status === 422) {
            errors.value = err.response.data.errors ?? {};
            toast.add({ severity: 'warn', summary: 'Validation Error', detail: 'Please check the form.', life: 3500 });
        } else {
            toast.add({ severity: 'error', summary: 'Error', detail: 'Could not save transaction.', life: 3500 });
        }
    } finally {
        saving.value = false;
    }
}

function localDateStr(d) {
    if (!(d instanceof Date) || isNaN(d)) return d ?? null;
    const y = d.getFullYear();
    const m = String(d.getMonth() + 1).padStart(2, '0');
    const day = String(d.getDate()).padStart(2, '0');
    return `${y}-${m}-${day}`;
}

function buildMainPayload() {
    const payload = { ...toRaw(form) };
    // particulars_id is kept in payload so it's saved to DB

    // In create mode, derive top-level payee info from first selected employee
    if (props.mode !== 'edit') {
        const first = selectedEmployees.value[0];
        if (first) {
            payload.payee_name = payload.payee_name || first.full_name || first.payee_name || '';
            payload.payee_address = payload.payee_address || first.address || first.payee_address || '';
            payload.office = payload.office || first.office || '';
            payload.agency = payload.agency || first.agency || '';
            if (!payload.amount) {
                const defaultAmount = isProjectBased.value
                    ? resolveSelectedEmployeeAmount(first)
                    : normalizeAmount(first.monthly_compensation);

                if (defaultAmount !== null) payload.amount = defaultAmount;
            }
        }
    }

    if (isProjectBased.value) {
        const totalSelectedAmount = selectedEmployees.value.reduce((sum, employee) => {
            return sum + (resolveSelectedEmployeeAmount(employee) ?? 0);
        }, 0);

        if (totalSelectedAmount > 0) {
            payload.amount = totalSelectedAmount;
        }
    }

    payload.date_obligated = localDateStr(payload.date_obligated);
    payload.date_from = localDateStr(payload.date_from);
    payload.date_to = localDateStr(payload.date_to);

    payload.employees = selectedEmployees.value.map(buildEmployeeItem);

    return payload;
}

function buildEmployeeItem(emp) {
    const recordId = emp.employee_record_id ?? emp.id;
    return {
        employee_record_id: recordId ?? null,
        payee_name: emp.full_name || emp.payee_name || '',
        payee_address: emp.address || emp.payee_address || '',
        office: emp.office || '',
        amount: resolveSelectedEmployeeAmount(emp),
        employee_id: emp.employee_no || emp.employee_id || '',
        contract_ref_no: emp.contract_ref_no || '',
        swa: emp.swa || false,
        atm_account_no: emp.atm_account_no || '',
        monthly_compensation: emp.monthly_compensation ? parseFloat(emp.monthly_compensation) : null,
        deduction_sss: emp.deduction_sss ? parseFloat(emp.deduction_sss) : null,
        deduction_philhealth: emp.deduction_philhealth ? parseFloat(emp.deduction_philhealth) : null,
        deduction_hdmf: emp.deduction_hdmf ? parseFloat(emp.deduction_hdmf) : null,
        lost_hour_minutes: normalizeLostHourMinutes(emp.lost_hour_minutes),
    };
}

// ── Helpers ──
function money(val) {
    if (!val && val !== 0) return '—';
    return new Intl.NumberFormat('en-PH', { style: 'currency', currency: 'PHP' }).format(val);
}

// ── Drag ──
const elModal = ref(null);
const dragOffset = ref({ x: 0, y: 0 });
const dragStart = ref(null);

const modalStyle = computed(() => ({
    transform: `translate(${dragOffset.value.x}px, ${dragOffset.value.y}px)`,
}));

function onDragStart(e) {
    if (e.target.closest('button, input, .p-select, .p-inputnumber, .p-toggleswitch, .p-editor, .p-datepicker')) return;
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
