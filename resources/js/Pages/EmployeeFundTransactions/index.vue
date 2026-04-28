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
                <InputText v-model="filters.search" placeholder="Search ID, payee, DV no…" class="w-full"
                    @keyup.enter="fetchTransactions(1)" />
            </IconField>
            <Select v-model="filters.status" :options="statusOptions" optionLabel="label" optionValue="value"
                placeholder="All statuses" class="w-40" showClear @change="fetchTransactions(1)" />
            <Select v-model="filters.employee_type" :options="employeeTypeOptions" optionLabel="label"
                optionValue="value" placeholder="All types" class="w-44" showClear @change="fetchTransactions(1)" />
            <InputText v-model="filters.fiscal_year" placeholder="Fiscal year" class="w-28"
                @keyup.enter="fetchTransactions(1)" />
            <Select v-model="filters.obr_type" :options="obrTypeOptions" optionLabel="label" optionValue="value"
                placeholder="OBR Type" class="w-44" showClear @change="fetchTransactions(1)" />
            <Button icon="pi pi-filter-slash" severity="secondary" class="rounded" outlined v-tooltip="'Reset filters'"
                @click="resetFilters" />
        </div>

        <!-- DataTable -->
        <div class="overflow-auto" style="border-radius:1.5rem;border:1px solid var(--p-datatable-border-color);">
            <DataTable v-model:contextMenuSelection="contextMenuTransaction" :value="transactions" :loading="loading"
                contextMenu showGridlines stripedRows scrollable lazy @row-contextmenu="openRowContextMenu"
                :totalRecords="pagination.filtered_total" :rows="pagination.per_page" :first="paginatorFirst" paginator
                @page="onPage" :rowsPerPageOptions="[10, 25, 50]"
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
                        <div class="flex flex-col gap-1">
                            <span class="font-mono text-[10px] ">{{ data.transaction_id }}</span>
                            <span class="font-mono text-[11px] font-semibold text-surface-400">OBR: {{ data.obr_no ||
                                '—' }}</span>
                        </div>
                    </template>
                </Column>

                <Column field="employee_type" header="Type" style="min-width:150px;">
                    <template #body="{ data }">
                        <span :class="['text-xs font-medium', typeTextClass(data.employee_type)]">
                            {{ formatType(data.employee_type) }}
                        </span>
                    </template>
                </Column>

                <Column field="payee_name" header="Payee" style="min-width:180px;">
                    <template #body="{ data }">
                        <span class="text-xs">
                            {{ data.payee_name || '—' }}
                        </span>
                    </template>
                </Column>

                <Column field="office" header="Office" style="min-width:140px;">
                    <template #body="{ data }">
                        <span class="text-xs">
                            {{ data.office || '—' }}
                        </span>
                    </template>
                </Column>

                <Column field="amount" header="Amount" style="min-width:120px;">
                    <template #body="{ data }">
                        <span class="font-mono text-xs">{{ money(data.amount) }}</span>
                    </template>
                </Column>

                <Column field="transaction_status" header="Status" style="min-width:110px;">
                    <template #body="{ data }">
                        <span
                            :class="['text-xs font-medium', textClassForSeverity(statusSeverity(data.transaction_status))]">
                            {{ formatTransactionStatus(data.transaction_status) }}
                        </span>
                    </template>
                </Column>

                <Column header="Created By" style="min-width:140px;">
                    <template #body="{ data }">
                        <span class="text-xs">{{ data.creator?.name || '—' }}</span>
                    </template>
                </Column>

                <Column header="Date" style="min-width:110px;">
                    <template #body="{ data }">
                        <span class="text-xs">{{ formatDate(data.created_at) }}</span>
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
        <ContextMenu ref="contextMenuRef" :model="menuItems" @hide="contextMenuTransaction = null" />

        <!-- ── Modals ── -->
        <ViewTransactionModal v-model:show="modals.view" :transaction="selectedTransaction" />

        <DeleteConfirmModal v-model:show="modals.delete" :transaction-id="selectedTransaction?.transaction_id"
            :payee-name="selectedTransaction?.payee_name"
            :date="selectedTransaction ? formatDate(selectedTransaction.created_at) : ''" :is-deleting="isDeleting"
            @confirm-delete="handleDelete" />

        <RemarksModal v-model:show="modals.remarks" :model-value="selectedTransaction" :is-saving="isSaving"
            @save="handleSaveRemarks" />

        <StatusModal v-model:show="modals.status" :model-value="selectedTransaction" :is-saving="isSaving"
            :live-tracking-data="liveObrTrackingData" :is-tracking-loading="liveObrTrackingLoading"
            :tracking-error="liveObrTrackingError" @save="handleSaveStatus" />

        <TrackingHistoryModal v-model:show="modals.tracking" :transaction="selectedTransaction"
            :tracking-data="trackingHistoryData" :is-loading="trackingHistoryLoading" />

        <!-- ── Create / Edit Wizard ── -->
        <TransactionWizard v-model:show="wizardShow" :mode="wizardMode" :transaction="wizardTransaction"
            @saved="onWizardSaved" />

        <PdfPreviewModal v-model:show="pdfPreview.show" :html-doc="pdfPreview.html" :paper-size="pdfPreview.size"
            :title="pdfPreview.title" />

        <OfficeHeadSelectModal v-model:show="modals.officeHeadSelect"
            :options="pendingPrint.signatories.filter(s => s.part === 'A')" @select="onOfficeHeadSelected" />

    </WorkforceLayout>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue';
import { Head } from '@inertiajs/vue3';
import ContextMenu from 'primevue/contextmenu';
import { useToast } from 'primevue/usetoast';
import axios from 'axios';

import WorkforceLayout from '@/Layouts/WorkforceLayout.vue';
import TransactionWizard from './Modal/TransactionWizard.vue';
import PdfPreviewModal from './Modal/PdfPreviewModal.vue';

import ViewTransactionModal from './Modal/ViewTransactionModal.vue';
import DeleteConfirmModal from './Modal/DeleteConfirmModal.vue';
import RemarksModal from './Modal/RemarksModal.vue';
import StatusModal from './Modal/StatusModal.vue';
import TrackingHistoryModal from './Modal/TrackingHistoryModal.vue';
import OfficeHeadSelectModal from './Modal/OfficeHeadSelectModal.vue';

import { renderVueTemplate, usePdfPrint } from '@/composables/usePdfPrint';
import ObrTemplate from './Pdf/ObrTemplate.vue';
import CwaTemplate from './Pdf/CwaTemplate.vue';
import PayrollTemplate from './Pdf/PayrollTemplate.vue';

// ── Auth ──
const props = defineProps({
    auth: Object,
});

const toast = useToast();
const { printHtml, buildHtmlDoc } = usePdfPrint();

// ── State ──
const transactions = ref([]);
const loading = ref(false);
const isSaving = ref(false);
const isDeleting = ref(false);
const trackingHistoryLoading = ref(false);
const liveObrTrackingLoading = ref(false);
const selectedTransaction = ref(null);
const trackingHistoryData = ref(null);
const liveObrTrackingData = ref(null);
const liveObrTrackingError = ref('');
const wizardShow = ref(false);
const wizardMode = ref('create');
const wizardTransaction = ref(null);
const contextMenuRef = ref(null);
const contextMenuTransaction = ref(null);
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
    obr_type: null,
});

const modals = reactive({
    view: false,
    delete: false,
    remarks: false,
    status: false,
    tracking: false,
    officeHeadSelect: false,
});

const pendingPrint = reactive({ type: '', signatories: [] });
const pdfPreview = reactive({ show: false, html: '', title: '', size: 'a4' });



// ── Options ──
const statusOptions = [
    { label: 'On Process', value: 'on_process' },
    { label: 'Claimed', value: 'claimed' },
    { label: 'Cancelled', value: 'cancelled' },
    { label: 'Suspended', value: 'suspended' },
];

const employeeTypeOptions = [
    { label: 'Contract of Service', value: 'contract_of_service' },
    { label: 'Project-Based', value: 'project_based' },
];

const obrTypeOptions = [
    { label: 'Regular', value: 'REGULAR' },
    { label: 'Financial Assistance', value: 'FINANCIAL ASSISTANCE' },
    { label: 'Reimbursement', value: 'REIMBURSEMENT' },
];

// ── Context Menu ──
const menuItems = computed(() => {
    const t = selectedTransaction.value;
    const isAdmin = props.auth?.user?.roles?.some(r =>
        typeof r === 'string' ? r === 'admin' : r?.name === 'admin'
    );
    const canPrintCwa = t?.employee_type === 'contract_of_service';
    const canPrintPayroll = t?.employee_type === 'project_based';

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
            label: 'Update Status / OBR',
            icon: 'pi pi-tag',
            command: () => openStatusModal(t),
        },
        {
            label: 'Add Remarks',
            icon: 'pi pi-comment',
            command: () => { modals.remarks = true; },
        },
        { separator: true },
        {
            label: 'Print OBR',
            icon: 'pi pi-print',
            command: () => printPdf('obr'),
        },
        ...(canPrintCwa ? [{
            label: 'Print CWA',
            icon: 'pi pi-print',
            command: () => printPdf('cwa'),
        }] : []),
        ...(canPrintPayroll ? [{
            label: 'Print Payroll',
            icon: 'pi pi-print',
            command: () => printPdf('payroll'),
        }] : []),
        { separator: true },
        {
            label: 'Tracking History',
            icon: 'pi pi-history',
            command: () => openTrackingHistory(t),
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
        if (filters.obr_type) params.obr_type = filters.obr_type;

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
    filters.obr_type = null;
    fetchTransactions(1);
}

// ── Open / Close ──
function openCreate() {
    wizardMode.value = 'create';
    wizardTransaction.value = null;
    wizardShow.value = true;
}

function openEdit(transaction) {
    wizardMode.value = 'edit';
    wizardTransaction.value = transaction;
    wizardShow.value = true;
}

function showContextMenu(mouseEvent) {
    if (typeof contextMenuRef.value?.show === 'function') {
        contextMenuRef.value.show(mouseEvent);
        return;
    }

    contextMenuRef.value?.toggle(mouseEvent);
}

function openMenu(event, transaction) {
    selectedTransaction.value = transaction;
    contextMenuTransaction.value = transaction;
    showContextMenu(event);
}

function openRowContextMenu(event) {
    event.originalEvent.preventDefault();
    selectedTransaction.value = event.data;
    contextMenuTransaction.value = event.data;
    showContextMenu(event.originalEvent);
}

// ── Wizard saved ──
function onWizardSaved() {
    wizardShow.value = false;
    fetchTransactions(wizardMode.value === 'edit' ? pagination.current_page : 1);
}

async function fetchObrTrackingData(transaction) {
    const res = await axios.get('/api/obr-tracking-info', {
        params: {
            fiscal_year: transaction.fiscal_year,
            obr_no: transaction.obr_no,
            dv_no: transaction.dv_no || '',
            type: transaction.obr_type || '',
        },
    });

    if (!res.data.success) {
        throw new Error(res.data.message || 'Failed to load tracking history.');
    }

    return res.data.data;
}

async function openStatusModal(transaction) {
    selectedTransaction.value = transaction;
    liveObrTrackingData.value = null;
    liveObrTrackingError.value = '';
    modals.status = true;

    if (!transaction?.fiscal_year || !transaction?.obr_no) {
        return;
    }

    liveObrTrackingLoading.value = true;

    try {
        liveObrTrackingData.value = await fetchObrTrackingData(transaction);
    } catch {
        liveObrTrackingError.value = 'Unable to check live OBR status.';
    } finally {
        liveObrTrackingLoading.value = false;
    }
}

async function openTrackingHistory(transaction) {
    if (!transaction?.fiscal_year || !transaction?.obr_no) {
        toast.add({
            severity: 'warn',
            summary: 'Incomplete OBR Data',
            detail: 'Fiscal year and OBR number are required before tracking can be viewed.',
            life: 4000,
        });
        return;
    }

    selectedTransaction.value = transaction;
    trackingHistoryData.value = null;
    trackingHistoryLoading.value = true;
    modals.tracking = true;

    try {
        trackingHistoryData.value = await fetchObrTrackingData(transaction);
    } catch {
        modals.tracking = false;
        toast.add({ severity: 'error', summary: 'Error', detail: 'Failed to load tracking history.', life: 3000 });
    } finally {
        trackingHistoryLoading.value = false;
    }
}

// ── Modal Saves ──
async function handleSaveRemarks(remarks) {
    isSaving.value = true;
    try {
        await axios.patch(`/api/employee-fund-transactions/${selectedTransaction.value.id}/update-status`, {
            transaction_status: normalizeTransactionStatus(selectedTransaction.value.transaction_status),
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

async function handleSaveStatus(data) {
    isSaving.value = true;
    try {
        const payload = typeof data === 'object' && data !== null
            ? {
                ...data,
                transaction_status: normalizeTransactionStatus(data.transaction_status),
            }
            : {
                transaction_status: normalizeTransactionStatus(data),
            };

        await axios.patch(`/api/employee-fund-transactions/${selectedTransaction.value.id}/update-status`, {
            ...payload,
        });
        toast.add({ severity: 'success', summary: 'Updated', detail: 'Tracking updated.', life: 3000 });
        modals.status = false;
        fetchTransactions();
    } catch {
        toast.add({ severity: 'error', summary: 'Error', detail: 'Failed to update tracking.', life: 3000 });
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



// ── PDF ──
function renderAndShow(type, voucher, signatories) {
    let html, title, size;
    if (type === 'obr') {
        html = renderVueTemplate(ObrTemplate, { voucher });
        title = `OBR-${voucher.obr_no || voucher.transaction_id}`;
        size = 'a4';
    } else if (type === 'cwa') {
        html = renderVueTemplate(CwaTemplate, { voucher, employees: voucher.employees ?? [], signatories });
        title = `CWA-${voucher.fiscal_year || voucher.transaction_id}`;
        size = 'landscape';
    } else {
        html = renderVueTemplate(PayrollTemplate, { voucher, employees: voucher.employees ?? [], signatories });
        title = `Payroll-${voucher.payee_name || voucher.transaction_id}`;
        size = 'landscape';
    }
    pdfPreview.html = buildHtmlDoc(html, title, size);
    pdfPreview.title = title;
    pdfPreview.size = size;
    pdfPreview.show = true;
}

async function printPdf(type) {
    const voucher = selectedTransaction.value;
    if (!voucher) return;

    if (type === 'cwa' && voucher.employee_type !== 'contract_of_service') {
        toast.add({ severity: 'warn', summary: 'Unavailable', detail: 'CWA is only available for COS transactions.', life: 3000 });
        return;
    }

    if (type === 'payroll' && voucher.employee_type !== 'project_based') {
        toast.add({ severity: 'warn', summary: 'Unavailable', detail: 'Payroll is only available for project-based transactions.', life: 3000 });
        return;
    }

    let signatories = [];
    try {
        const res = await axios.get('/api/signatories');
        signatories = res.data.data ?? [];
    } catch { /* non-critical — fall back to empty */ }

    if (type !== 'obr') {
        const partA = signatories.filter((s) => s.part === 'A');
        if (partA.length > 1) {
            pendingPrint.type = type;
            pendingPrint.signatories = signatories;
            modals.officeHeadSelect = true;
            return;
        }
    }

    renderAndShow(type, voucher, signatories);
}

function onOfficeHeadSelected(chosen) {
    const voucher = selectedTransaction.value;
    if (!voucher) return;
    const signatories = [
        ...pendingPrint.signatories.filter((s) => s.part !== 'A'),
        { ...chosen, part: 'A' },
    ];
    renderAndShow(pendingPrint.type, voucher, signatories);
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
    return {
        on_process: 'warn',
        claimed: 'success',
        approved: 'success',
        active: 'info',
        cancelled: 'danger',
        denied: 'danger',
        suspended: 'secondary',
    }[status] || 'secondary';
}

function textClassForSeverity(severity) {
    return {
        warn: 'text-yellow-700 dark:text-yellow-300',
        success: 'text-emerald-700 dark:text-emerald-300',
        info: 'text-sky-700 dark:text-sky-300',
        danger: 'text-red-700 dark:text-red-300',
        secondary: 'text-surface-600 dark:text-surface-300',
    }[severity] || 'text-surface-600 dark:text-surface-300';
}

function typeTextClass(type) {
    return type === 'project_based'
        ? 'text-amber-600 dark:text-amber-300'
        : 'text-purple-600 dark:text-purple-300';
}

function formatTransactionStatus(status) {
    return {
        on_process: 'On Process',
        claimed: 'Claimed',
        approved: 'Approved',
        active: 'Active',
        cancelled: 'Cancelled',
        denied: 'Denied',
        suspended: 'Suspended',
    }[status] || status || '—';
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

function formatType(type) {
    if (!type) return '—';
    return type === 'contract_of_service' ? 'COS' : 'Project-Based';
}

onMounted(() => {
    fetchTransactions(1);
});
</script>
