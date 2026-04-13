<template>
    <WorkforceLayout>
        <Head title="Employee Fund Transactions" />

        <!-- Page Header -->
        <div class="flex items-center justify-between mb-5">
            <div>
                <h1 class="text-xl font-bold text-surface-800 dark:text-surface-50">Employee Fund Transactions</h1>
                <p class="text-sm text-surface-400 mt-0.5">
                    {{ pagination.filtered_total }} record{{ pagination.filtered_total !== 1 ? 's' : '' }}
                    <span v-if="myCount > 0" class="ml-2">· {{ myCount }} created by you</span>
                </p>
            </div>
            <Button icon="pi pi-plus" label="New Transaction" class="rounded" @click="openCreate" />
        </div>

        <!-- Filter Toolbar -->
        <div class="ios-card flex flex-wrap gap-3 p-4 mb-4">
            <IconField class="flex-1 min-w-50">
                <InputIcon class="pi pi-search" />
                <InputText v-model="filters.search" placeholder="Search ID, payee, DV no…"
                    class="w-full" @keyup.enter="fetchTransactions(1)" />
            </IconField>
            <Select v-model="filters.status" :options="statusOptions" optionLabel="label" optionValue="value"
                placeholder="All statuses" class="w-40" showClear @change="fetchTransactions(1)" />
            <Select v-model="filters.employee_type" :options="employeeTypeOptions" optionLabel="label" optionValue="value"
                placeholder="All types" class="w-44" showClear @change="fetchTransactions(1)" />
            <InputText v-model="filters.fiscal_year" placeholder="Fiscal year" class="w-28"
                @keyup.enter="fetchTransactions(1)" />
            <Button icon="pi pi-filter-slash" severity="secondary" class="rounded" outlined
                v-tooltip="'Reset filters'" @click="resetFilters" />
        </div>

        <!-- DataTable -->
        <div class="overflow-hidden" style="border-radius:1.5rem;border:1px solid var(--p-datatable-border-color);">
            <DataTable :value="transactions" :loading="loading" showGridlines stripedRows scrollable
                lazy :totalRecords="pagination.filtered_total" :rows="pagination.per_page"
                :first="paginatorFirst" paginator @page="onPage"
                :rowsPerPageOptions="[10, 25, 50]"
                paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink RowsPerPageDropdown"
                :pt="{
                    root: { style: 'border-radius:0;border:none;' },
                    tableContainer: { style: 'border-radius:0;' },
                    paginator: { style: 'border:none;border-top:1px solid var(--p-datatable-border-color);' }
                }">
                <template #empty>
                    <div class="text-center py-12 text-surface-400">
                        <i class="pi pi-inbox text-4xl block mb-3"></i>
                        <p class="text-sm">No transactions found.</p>
                    </div>
                </template>

                <Column field="transaction_id" header="Transaction ID" style="min-width:160px;">
                    <template #body="{ data }">
                        <span class="font-mono text-xs font-semibold">{{ data.transaction_id }}</span>
                    </template>
                </Column>

                <Column field="employee_type" header="Type" style="min-width:150px;">
                    <template #body="{ data }">
                        <Tag :value="formatType(data.employee_type)" severity="secondary" />
                    </template>
                </Column>

                <Column field="payee_name" header="Payee Name" style="min-width:180px;" />

                <Column field="office" header="Office" style="min-width:140px;">
                    <template #body="{ data }">
                        {{ data.office || '—' }}
                    </template>
                </Column>

                <Column field="amount" header="Amount" style="min-width:120px;">
                    <template #body="{ data }">
                        <span class="font-mono text-xs">{{ money(data.amount) }}</span>
                    </template>
                </Column>

                <Column field="transaction_status" header="Status" style="min-width:110px;">
                    <template #body="{ data }">
                        <Tag :value="data.transaction_status" :severity="statusSeverity(data.transaction_status)"
                            class="capitalize" />
                    </template>
                </Column>

                <Column header="Created By" style="min-width:140px;">
                    <template #body="{ data }">
                        {{ data.creator?.name || '—' }}
                    </template>
                </Column>

                <Column header="Date" style="min-width:110px;">
                    <template #body="{ data }">
                        {{ formatDate(data.created_at) }}
                    </template>
                </Column>

                <Column header="" style="width:60px;min-width:60px;" frozen alignFrozen="right">
                    <template #body="{ data }">
                        <Button icon="pi pi-ellipsis-v" severity="secondary" text rounded size="small"
                            @click="openMenu($event, data)" />
                    </template>
                </Column>
            </DataTable>
        </div>

        <!-- Context Menu -->
        <Menu ref="contextMenuRef" :model="menuItems" popup />

        <!-- ── Modals ── -->
        <ViewTransactionModal v-model:show="modals.view" :transaction="selectedTransaction" />

        <DeleteConfirmModal v-model:show="modals.delete"
            :transaction-id="selectedTransaction?.transaction_id"
            :payee-name="selectedTransaction?.payee_name"
            :date="selectedTransaction ? formatDate(selectedTransaction.created_at) : ''"
            :is-deleting="isDeleting"
            @confirm-delete="handleDelete" />

        <RemarksModal v-model:show="modals.remarks"
            :model-value="selectedTransaction"
            :is-saving="isSaving"
            @save="handleSaveRemarks" />

        <StatusModal v-model:show="modals.status"
            :model-value="selectedTransaction"
            :is-saving="isSaving"
            @save="handleSaveStatus" />

        <QrCodeModal v-model:show="modals.qrCode"
            :model-value="qrData"
            :countdown="qrCountdown" />

        <TrackingHistoryModal v-model:show="modals.tracking"
            :tracking-data="selectedTransaction" />

        <!-- ── Create / Edit Drawer ── -->
        <FloatingDrawer v-model:visible="drawerVisible" position="right" :style="{ width: '32rem' }"
            @hide="resetForm">
            <template #header>
                <span class="font-semibold text-base">
                    {{ editingId ? 'Edit Transaction' : 'New Transaction' }}
                </span>
            </template>

            <div class="space-y-4 p-1">

                <!-- Employee Type -->
                <div>
                    <label class="block text-xs font-medium text-surface-400 uppercase tracking-wide mb-1">
                        Employee Type <span class="text-red-400">*</span>
                    </label>
                    <Select v-model="form.employee_type" :options="employeeTypeOptions"
                        optionLabel="label" optionValue="value" class="w-full" />
                </div>

                <!-- Basic Info -->
                <div class="grid grid-cols-2 gap-3">
                    <div class="col-span-2">
                        <label class="block text-xs font-medium text-surface-400 uppercase tracking-wide mb-1">
                            Payee Name <span class="text-red-400">*</span>
                        </label>
                        <InputText v-model="form.payee_name" class="w-full" placeholder="Full name" />
                    </div>
                    <div class="col-span-2">
                        <label class="block text-xs font-medium text-surface-400 uppercase tracking-wide mb-1">
                            Payee Address <span class="text-red-400">*</span>
                        </label>
                        <InputText v-model="form.payee_address" class="w-full" placeholder="Address" />
                    </div>
                    <div class="col-span-2">
                        <label class="block text-xs font-medium text-surface-400 uppercase tracking-wide mb-1">
                            Office / Unit <span class="text-red-400">*</span>
                        </label>
                        <InputText v-model="form.office" class="w-full" placeholder="Office or unit" />
                    </div>
                </div>

                <!-- Responsibility Center + Particulars -->
                <div>
                    <label class="block text-xs font-medium text-surface-400 uppercase tracking-wide mb-1">
                        Responsibility Center <span class="text-red-400">*</span>
                    </label>
                    <ResponsibilityCenterSelect v-model="form.responsibility_center"
                        :fiscal-year="form.fiscal_year"
                        @change="onRcChange" />
                </div>
                <div>
                    <label class="block text-xs font-medium text-surface-400 uppercase tracking-wide mb-1">
                        Particular
                    </label>
                    <ParticularsSelect v-model="form.particulars_id"
                        :responsibility-center-id="form.responsibility_center"
                        @change="onParticularChange" />
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-surface-400 uppercase tracking-wide mb-1">
                            Account Code
                        </label>
                        <InputText v-model="form.account_code" class="w-full" placeholder="Auto-filled" />
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-surface-400 uppercase tracking-wide mb-1">
                            Particulars Name
                        </label>
                        <InputText v-model="form.particulars_name" class="w-full" />
                    </div>
                </div>

                <!-- Particulars Description -->
                <div>
                    <label class="block text-xs font-medium text-surface-400 uppercase tracking-wide mb-1">
                        Particulars Description
                    </label>
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

                <!-- Amount + Fiscal Year -->
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-surface-400 uppercase tracking-wide mb-1">
                            Amount <span class="text-red-400">*</span>
                        </label>
                        <InputNumber v-model="form.amount" mode="currency" currency="PHP" locale="en-PH"
                            class="w-full" :min="0" />
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-surface-400 uppercase tracking-wide mb-1">
                            Fiscal Year
                        </label>
                        <InputText v-model="form.fiscal_year" class="w-full" placeholder="e.g. 2024" />
                    </div>
                </div>

                <!-- OBR / DV Info -->
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-surface-400 uppercase tracking-wide mb-1">
                            Disbursement Type
                        </label>
                        <InputText v-model="form.disbursement_type" class="w-full" />
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-surface-400 uppercase tracking-wide mb-1">
                            OBR Type
                        </label>
                        <InputText v-model="form.obr_type" class="w-full" />
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-surface-400 uppercase tracking-wide mb-1">
                            OBR No.
                        </label>
                        <InputText v-model="form.obr_no" class="w-full" />
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-surface-400 uppercase tracking-wide mb-1">
                            DV No.
                        </label>
                        <InputText v-model="form.dv_no" class="w-full" />
                    </div>
                    <div class="col-span-2">
                        <label class="block text-xs font-medium text-surface-400 uppercase tracking-wide mb-1">
                            Date Obligated
                        </label>
                        <DatePicker v-model="form.date_obligated" class="w-full" dateFormat="yy-mm-dd" showIcon iconDisplay="input" />
                    </div>
                </div>

                <!-- Explanation -->
                <div>
                    <label class="block text-xs font-medium text-surface-400 uppercase tracking-wide mb-1">
                        Explanation
                    </label>
                    <Textarea v-model="form.explanation" rows="2" class="w-full" autoResize />
                </div>

                <!-- ── COS Fields ── -->
                <template v-if="form.employee_type === 'contract_of_service'">
                    <div class="border-t border-surface-200 dark:border-surface-700 pt-4">
                        <p class="text-xs font-semibold uppercase tracking-wide text-primary-400 mb-3">
                            Contract of Service Details
                        </p>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-surface-400 uppercase tracking-wide mb-1">
                                Employee ID <span class="text-red-400">*</span>
                            </label>
                            <InputText v-model="form.employee_id" class="w-full" />
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-surface-400 uppercase tracking-wide mb-1">
                                Contract Ref No.
                            </label>
                            <InputText v-model="form.contract_ref_no" class="w-full" />
                        </div>
                        <div class="col-span-2">
                            <label class="block text-xs font-medium text-surface-400 uppercase tracking-wide mb-1">
                                ATM Account No.
                            </label>
                            <InputText v-model="form.atm_account_no" class="w-full" />
                        </div>
                        <div class="col-span-2">
                            <label class="block text-xs font-medium text-surface-400 uppercase tracking-wide mb-1">
                                Monthly Compensation <span class="text-red-400">*</span>
                            </label>
                            <InputNumber v-model="form.monthly_compensation" mode="currency" currency="PHP"
                                locale="en-PH" class="w-full" :min="0" />
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-surface-400 uppercase tracking-wide mb-1">
                                SSS Deduction
                            </label>
                            <InputNumber v-model="form.deduction_sss" mode="currency" currency="PHP"
                                locale="en-PH" class="w-full" :min="0" />
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-surface-400 uppercase tracking-wide mb-1">
                                PhilHealth Deduction
                            </label>
                            <InputNumber v-model="form.deduction_philhealth" mode="currency" currency="PHP"
                                locale="en-PH" class="w-full" :min="0" />
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-surface-400 uppercase tracking-wide mb-1">
                                HDMF Deduction
                            </label>
                            <InputNumber v-model="form.deduction_hdmf" mode="currency" currency="PHP"
                                locale="en-PH" class="w-full" :min="0" />
                        </div>
                        <div class="flex items-center gap-3 pt-1">
                            <ToggleSwitch v-model="form.swa" size="small" inputId="swa-toggle" />
                            <label for="swa-toggle" class="text-sm cursor-pointer">SWA</label>
                        </div>
                    </div>
                </template>

            </div>

            <!-- Drawer Footer -->
            <template #footer>
                <div class="flex gap-3">
                    <Button label="Cancel" severity="secondary" class="flex-1 rounded" outlined
                        :disabled="isSaving" @click="drawerVisible = false" />
                    <Button :label="editingId ? 'Update' : 'Save'" icon="pi pi-check" class="flex-1 rounded"
                        :loading="isSaving" :disabled="isSaving" @click="handleSaveForm" />
                </div>
            </template>
        </FloatingDrawer>

    </WorkforceLayout>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue';
import { Head } from '@inertiajs/vue3';
import { useToast } from 'primevue/usetoast';
import axios from 'axios';

import WorkforceLayout from '@/Layouts/WorkforceLayout.vue';
import FloatingDrawer from '@/Components/FloatingDrawer.vue';
import ResponsibilityCenterSelect from '@/Components/selects/ResponsibilityCenterSelect.vue';
import ParticularsSelect from '@/Components/selects/ParticularsSelect.vue';

import ViewTransactionModal from './Modal/ViewTransactionModal.vue';
import DeleteConfirmModal from './Modal/DeleteConfirmModal.vue';
import FileUploadModal from './Modal/FileUploadModal.vue';
import RemarksModal from './Modal/RemarksModal.vue';
import StatusModal from './Modal/StatusModal.vue';
import QrCodeModal from './Modal/QrCodeModal.vue';
import TrackingHistoryModal from './Modal/TrackingHistoryModal.vue';

import { renderVueTemplate, usePdfPrint } from '@/composables/usePdfPrint';
import ObrTemplate from './Pdf/ObrTemplate.vue';
import DvTemplate from './Pdf/DvTemplate.vue';
import PayrollTemplate from './Pdf/PayrollTemplate.vue';

// ── Auth ──
const props = defineProps({
    auth: Object,
});

const toast = useToast();
const { printHtml } = usePdfPrint();

// ── State ──
const transactions = ref([]);
const loading = ref(false);
const isSaving = ref(false);
const isDeleting = ref(false);
const selectedTransaction = ref(null);
const editingId = ref(null);
const drawerVisible = ref(false);
const contextMenuRef = ref(null);
const paginatorFirst = ref(0);
const myCount = ref(0);

const pagination = reactive({
    filtered_total: 0,
    per_page: 10,
    current_page: 1,
    last_page: 1,
});

const filters = reactive({
    search: '',
    status: null,
    employee_type: null,
    fiscal_year: '',
});

const modals = reactive({
    view: false,
    delete: false,
    remarks: false,
    status: false,
    qrCode: false,
    tracking: false,
    upload: false,
});

const qrData = ref(null);
const qrCountdown = ref('');

// ── Form ──
const defaultForm = () => ({
    employee_type: 'contract_of_service',
    payee_name: '',
    payee_address: '',
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
    obr_type: '',
    obr_no: '',
    dv_no: '',
    date_obligated: null,
    // COS
    employee_id: '',
    contract_ref_no: '',
    swa: false,
    atm_account_no: '',
    monthly_compensation: null,
    deduction_sss: null,
    deduction_philhealth: null,
    deduction_hdmf: null,
});

const form = ref(defaultForm());

// ── Options ──
const statusOptions = [
    { label: 'Pending', value: 'pending' },
    { label: 'Approved', value: 'approved' },
    { label: 'Active', value: 'active' },
    { label: 'Denied', value: 'denied' },
    { label: 'Suspended', value: 'suspended' },
];

const employeeTypeOptions = [
    { label: 'Contract of Service', value: 'contract_of_service' },
    { label: 'Project-Based', value: 'project_based' },
];

// ── Context Menu ──
const menuItems = computed(() => {
    const t = selectedTransaction.value;
    const isAdmin = props.auth?.user?.roles?.some(r =>
        typeof r === 'string' ? r === 'admin' : r?.name === 'admin'
    );

    return [
        {
            label: 'View Details',
            icon: 'pi pi-eye',
            command: () => { modals.view = true; },
        },
        {
            label: 'Edit',
            icon: 'pi pi-pencil',
            command: () => openEdit(t),
        },
        { separator: true },
        {
            label: 'Update Status',
            icon: 'pi pi-tag',
            command: () => { modals.status = true; },
        },
        {
            label: 'Add Remarks',
            icon: 'pi pi-comment',
            command: () => { modals.remarks = true; },
        },
        {
            label: 'Upload Files',
            icon: 'pi pi-upload',
            command: () => { modals.upload = true; },
        },
        { separator: true },
        {
            label: 'Print OBR',
            icon: 'pi pi-print',
            command: () => printPdf('obr'),
        },
        {
            label: 'Print DV',
            icon: 'pi pi-print',
            command: () => printPdf('dv'),
        },
        {
            label: 'Print Payroll',
            icon: 'pi pi-print',
            command: () => printPdf('payroll'),
        },
        { separator: true },
        {
            label: 'QR Code',
            icon: 'pi pi-qrcode',
            command: () => openQrCode(t),
        },
        {
            label: 'Tracking History',
            icon: 'pi pi-history',
            command: () => { modals.tracking = true; },
        },
        ...(isAdmin ? [
            { separator: true },
            {
                label: 'Delete',
                icon: 'pi pi-trash',
                class: 'text-red-500',
                command: () => { modals.delete = true; },
            },
        ] : []),
    ];
});

// ── Fetch ──
async function fetchTransactions(page = pagination.current_page) {
    loading.value = true;
    try {
        const params = {
            page,
            per_page: pagination.per_page,
        };
        if (filters.search) params.search = filters.search;
        if (filters.status) params.status = filters.status;
        if (filters.employee_type) params.employee_type = filters.employee_type;
        if (filters.fiscal_year) params.fiscal_year = filters.fiscal_year;

        const res = await axios.get('/api/employee-fund-transactions', { params });
        transactions.value = res.data.data;
        pagination.filtered_total = res.data.filtered_total;
        pagination.per_page = res.data.per_page;
        pagination.current_page = res.data.current_page;
        pagination.last_page = res.data.last_page;
        paginatorFirst.value = (res.data.current_page - 1) * res.data.per_page;
        myCount.value = res.data.my_records_count ?? 0;
    } catch (err) {
        toast.add({ severity: 'error', summary: 'Error', detail: 'Failed to load transactions.', life: 3000 });
    } finally {
        loading.value = false;
    }
}

function onPage(event) {
    pagination.per_page = event.rows;
    fetchTransactions(event.page + 1);
}

function resetFilters() {
    filters.search = '';
    filters.status = null;
    filters.employee_type = null;
    filters.fiscal_year = '';
    fetchTransactions(1);
}

// ── Open / Close ──
function openCreate() {
    editingId.value = null;
    form.value = defaultForm();
    drawerVisible.value = true;
}

function openEdit(transaction) {
    editingId.value = transaction.id;
    form.value = {
        employee_type: transaction.employee_type,
        payee_name: transaction.payee_name,
        payee_address: transaction.payee_address,
        office: transaction.office,
        responsibility_center: transaction.responsibility_center,
        particulars_id: null,
        account_code: transaction.account_code || '',
        particulars_name: transaction.particulars_name || '',
        particulars_description: transaction.particulars_description || '',
        amount: transaction.amount ? Number(transaction.amount) : null,
        fiscal_year: transaction.fiscal_year || '',
        disbursement_type: transaction.disbursement_type || '',
        explanation: transaction.explanation || '',
        obr_type: transaction.obr_type || '',
        obr_no: transaction.obr_no || '',
        dv_no: transaction.dv_no || '',
        date_obligated: transaction.date_obligated ? new Date(transaction.date_obligated) : null,
        employee_id: transaction.employee_id || '',
        contract_ref_no: transaction.contract_ref_no || '',
        swa: transaction.swa ?? false,
        atm_account_no: transaction.atm_account_no || '',
        monthly_compensation: transaction.monthly_compensation ? Number(transaction.monthly_compensation) : null,
        deduction_sss: transaction.deduction_sss ? Number(transaction.deduction_sss) : null,
        deduction_philhealth: transaction.deduction_philhealth ? Number(transaction.deduction_philhealth) : null,
        deduction_hdmf: transaction.deduction_hdmf ? Number(transaction.deduction_hdmf) : null,
    };
    drawerVisible.value = true;
}

function resetForm() {
    editingId.value = null;
    form.value = defaultForm();
}

function openMenu(event, transaction) {
    selectedTransaction.value = transaction;
    contextMenuRef.value.toggle(event);
}

// ── Select callbacks ──
function onRcChange(rc) {
    // Reset particular when RC changes
    form.value.particulars_id = null;
    form.value.account_code = '';
    form.value.particulars_name = '';
}

function onParticularChange(particular) {
    if (particular) {
        form.value.account_code = particular.account_code || '';
        form.value.particulars_name = particular.name || '';
    }
}

// ── Save Form ──
async function handleSaveForm() {
    isSaving.value = true;
    try {
        const payload = { ...form.value };
        // Remove internal UI-only field
        delete payload.particulars_id;

        // Format date if Date object
        if (payload.date_obligated instanceof Date) {
            payload.date_obligated = payload.date_obligated.toISOString().slice(0, 10);
        }

        if (editingId.value) {
            await axios.put(`/api/employee-fund-transactions/${editingId.value}`, payload);
            toast.add({ severity: 'success', summary: 'Updated', detail: 'Transaction updated.', life: 3000 });
        } else {
            await axios.post('/api/employee-fund-transactions', payload);
            toast.add({ severity: 'success', summary: 'Created', detail: 'Transaction created.', life: 3000 });
        }

        drawerVisible.value = false;
        fetchTransactions(editingId.value ? pagination.current_page : 1);
    } catch (err) {
        const detail = err.response?.data?.message || 'Failed to save transaction.';
        toast.add({ severity: 'error', summary: 'Error', detail, life: 5000 });
    } finally {
        isSaving.value = false;
    }
}

// ── Modal Saves ──
async function handleSaveRemarks(remarks) {
    isSaving.value = true;
    try {
        await axios.patch(`/api/employee-fund-transactions/${selectedTransaction.value.id}/update-status`, {
            transaction_status: selectedTransaction.value.transaction_status,
            remarks,
        });
        toast.add({ severity: 'success', summary: 'Saved', detail: 'Remarks updated.', life: 3000 });
        modals.remarks = false;
        fetchTransactions();
    } catch {
        toast.add({ severity: 'error', summary: 'Error', detail: 'Failed to save remarks.', life: 3000 });
    } finally {
        isSaving.value = false;
    }
}

async function handleSaveStatus(status) {
    isSaving.value = true;
    try {
        await axios.patch(`/api/employee-fund-transactions/${selectedTransaction.value.id}/update-status`, {
            transaction_status: status,
        });
        toast.add({ severity: 'success', summary: 'Updated', detail: 'Status updated.', life: 3000 });
        modals.status = false;
        fetchTransactions();
    } catch {
        toast.add({ severity: 'error', summary: 'Error', detail: 'Failed to update status.', life: 3000 });
    } finally {
        isSaving.value = false;
    }
}

async function handleDelete() {
    isDeleting.value = true;
    try {
        await axios.delete(`/api/employee-fund-transactions/${selectedTransaction.value.id}`);
        toast.add({ severity: 'success', summary: 'Deleted', detail: 'Transaction deleted.', life: 3000 });
        modals.delete = false;
        fetchTransactions(1);
    } catch {
        toast.add({ severity: 'error', summary: 'Error', detail: 'Failed to delete transaction.', life: 3000 });
    } finally {
        isDeleting.value = false;
    }
}

// ── QR Code ──
async function openQrCode(transaction) {
    qrData.value = null;
    qrCountdown.value = '';
    modals.qrCode = true;
    try {
        const res = await axios.get(`/api/employee-fund-transactions/${transaction.id}/obr-pdf`);
        qrData.value = res.data?.data ?? res.data;
    } catch {
        toast.add({ severity: 'error', summary: 'Error', detail: 'Could not load QR code.', life: 3000 });
        modals.qrCode = false;
    }
}

// ── PDF ──
function printPdf(type) {
    const voucher = selectedTransaction.value;
    if (!voucher) return;

    let html, title, size;
    if (type === 'obr') {
        html = renderVueTemplate(ObrTemplate, { voucher });
        title = `OBR-${voucher.obr_no || voucher.transaction_id}`;
        size = 'a4';
    } else if (type === 'dv') {
        html = renderVueTemplate(DvTemplate, { voucher });
        title = `DV-${voucher.dv_no || voucher.transaction_id}`;
        size = 'a4';
    } else {
        html = renderVueTemplate(PayrollTemplate, { voucher });
        title = `Payroll-${voucher.payee_name || voucher.transaction_id}`;
        size = 'landscape';
    }
    printHtml(html, title, size);
}

// ── Helpers ──
function formatDate(val) {
    if (!val) return '—';
    return new Date(val).toLocaleDateString('en-PH', { year: 'numeric', month: 'short', day: 'numeric' });
}

function money(val) {
    if (!val && val !== 0) return '—';
    return new Intl.NumberFormat('en-PH', { style: 'currency', currency: 'PHP' }).format(Number(val));
}

function statusSeverity(status) {
    return { pending: 'warn', approved: 'success', active: 'info', denied: 'danger', suspended: 'secondary' }[status] || 'secondary';
}

function formatType(type) {
    if (!type) return '—';
    return type === 'contract_of_service' ? 'COS' : 'Project-Based';
}

onMounted(() => {
    fetchTransactions(1);
});
</script>
