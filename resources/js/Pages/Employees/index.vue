<template>

    <Head title="Employees" />

    <!-- Page Header -->
    <div class="flex items-center justify-between mb-5">
        <div>
            <h1 class="text-xl font-bold text-surface-800 dark:text-surface-50">Employee Records</h1>
            <p class="text-sm text-surface-400 mt-0.5">
                {{ pagination.filtered_total }} record{{ pagination.filtered_total !== 1 ? 's' : '' }}
            </p>
        </div>
        <Button icon="pi pi-plus" label="New Employee" class="rounded" @click="openCreate" />
    </div>

    <!-- Filter Toolbar -->
    <div class="ios-card flex flex-wrap gap-3 p-4 mb-4">
        <IconField class="flex-1 min-w-50">
            <InputIcon class="pi pi-search" />
            <InputText v-model="filters.search" placeholder="Search name, employee no., office…" class="w-full"
                size="small" @keyup.enter="fetchEmployees(1)" />
        </IconField>
        <Select v-model="filters.employee_type" :options="typeOptions" optionLabel="label" optionValue="value"
            placeholder="All types" class="w-44" size="small" showClear @change="fetchEmployees(1)" />
        <Select v-model="filters.is_active" :options="activeOptions" optionLabel="label" optionValue="value"
            placeholder="All statuses" class="w-36" size="small" showClear @change="fetchEmployees(1)" />
        <Button icon="pi pi-filter-slash" severity="secondary" size="small" class="rounded" outlined
            v-tooltip="'Reset filters'" @click="resetFilters" />
    </div>

    <!-- DataTable -->
    <div class="overflow-hidden" style="border-radius:1.5rem;border:1px solid var(--p-datatable-border-color);">
        <DataTable :value="employees" :loading="loading" showGridlines stripedRows scrollable lazy
            :totalRecords="pagination.filtered_total" :rows="pagination.per_page" :first="paginatorFirst" paginator
            @page="onPage" :rowsPerPageOptions="[15, 25, 50]"
            paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink RowsPerPageDropdown" :pt="{
                root: { style: 'border-radius:0;border:none;' },
                tableContainer: { style: 'border-radius:0;' },
                paginator: { style: 'border:none;border-top:1px solid var(--p-datatable-border-color);' }
            }">
            <template #empty>
                <div class="text-center py-12 text-surface-400">
                    <i class="pi pi-users text-4xl block mb-3"></i>
                    <p class="text-sm">No employees found.</p>
                </div>
            </template>

            <Column field="employee_no" header="Employee No." style="min-width:130px;">
                <template #body="{ data }">
                    <span class="font-mono text-xs font-semibold">{{ data.employee_no || '—' }}</span>
                </template>
            </Column>

            <Column field="name" header="Name" style="min-width:200px;">
                <template #body="{ data }">
                    <span class="font-medium">{{ data.full_name }}</span>
                </template>
            </Column>

            <Column field="employee_type" header="Type" style="min-width:160px;">
                <template #body="{ data }">
                    <Tag :value="formatType(data.employee_type)" severity="secondary" />
                </template>
            </Column>

            <Column field="office" header="Office" style="min-width:160px;">
                <template #body="{ data }">
                    {{ data.office || '—' }}
                </template>
            </Column>

            <Column field="designation" header="Designation" style="min-width:180px;">
                <template #body="{ data }">
                    <span v-if="data.employee_type === 'contract_of_service'">{{ data.designation || '—' }}</span>
                    <span v-else class="text-surface-300">—</span>
                </template>
            </Column>

            <Column field="agency" header="Agency" style="min-width:160px;">
                <template #body="{ data }">
                    <span v-if="data.employee_type === 'project_based'">{{ data.agency || '—' }}</span>
                    <span v-else class="text-surface-300">—</span>
                </template>
            </Column>

            <Column field="amount" header="Amount" style="min-width:130px;">
                <template #body="{ data }">
                    <span v-if="data.employee_type === 'project_based' && data.amount" class="font-mono text-xs">
                        {{ money(data.amount) }}
                    </span>
                    <span v-else class="text-surface-300">—</span>
                </template>
            </Column>

            <Column field="monthly_compensation" header="Monthly Comp." style="min-width:140px;">
                <template #body="{ data }">
                    <span v-if="data.monthly_compensation" class="font-mono text-xs">
                        {{ money(data.monthly_compensation) }}
                    </span>
                    <span v-else class="text-surface-400">—</span>
                </template>
            </Column>

            <Column field="is_active" header="Status" style="min-width:100px;">
                <template #body="{ data }">
                    <Tag :value="data.is_active ? 'Active' : 'Inactive'"
                        :severity="data.is_active ? 'success' : 'secondary'" />
                </template>
            </Column>

            <Column header="" style="width:60px;min-width:60px;" frozen alignFrozen="right">
                <template #body="{ data }">
                    <Button icon="pi pi-ellipsis-v" severity="secondary" text rounded size="small"
                        @click="(e) => openRowMenu(e, data)" />
                </template>
            </Column>
        </DataTable>
    </div>

    <!-- Row context menu -->
    <Menu ref="rowMenuRef" :model="rowMenuItems" popup />

    <!-- Modals -->
    <CreateEditModal v-model:show="showCreateEdit" :employee="selectedEmployee" :mode="modalMode" @saved="onSaved" />
    <ViewModal v-model:show="showView" :employee="selectedEmployee" @edit="onEditFromView" />
    <DeleteConfirmModal v-model:show="showDelete" :employee-name="selectedEmployee?.name" :is-deleting="isDeleting"
        @confirm-delete="confirmDelete" />
</template>

<script setup>
import { ref, reactive, computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import WorkforceLayout from '@/Layouts/WorkforceLayout.vue';
import CreateEditModal from './Modal/CreateEditModal.vue';
import ViewModal from './Modal/ViewModal.vue';
import DeleteConfirmModal from './Modal/DeleteConfirmModal.vue';
import { useToast } from 'primevue/usetoast';
import axios from 'axios';

defineOptions({ layout: WorkforceLayout });

const toast = useToast();

// ── State ────────────────────────────────────────────────
const employees = ref([]);
const loading = ref(false);
const isDeleting = ref(false);

const pagination = reactive({
    filtered_total: 0,
    per_page: 15,
    current_page: 1,
    last_page: 1,
});

const paginatorFirst = computed(() => (pagination.current_page - 1) * pagination.per_page);

const filters = reactive({ search: '', employee_type: null, is_active: null });

// ── Options ───────────────────────────────────────────────
const typeOptions = [
    { label: 'Contract of Service', value: 'contract_of_service' },
    { label: 'Project-Based', value: 'project_based' },
];
const activeOptions = [
    { label: 'Active', value: '1' },
    { label: 'Inactive', value: '0' },
];

// ── Modal state ───────────────────────────────────────────
const showCreateEdit = ref(false);
const showView = ref(false);
const showDelete = ref(false);
const selectedEmployee = ref(null);
const modalMode = ref('create');

// ── Row menu ──────────────────────────────────────────────
const rowMenuRef = ref(null);
const rowMenuItems = ref([]);

function openRowMenu(event, emp) {
    selectedEmployee.value = emp;
    rowMenuItems.value = [
        { label: 'View Details', icon: 'pi pi-eye', command: () => { showView.value = true; } },
        { label: 'Edit', icon: 'pi pi-pencil', command: () => openEdit(emp) },
        { separator: true },
        {
            label: emp.is_active ? 'Deactivate' : 'Activate',
            icon: emp.is_active ? 'pi pi-ban' : 'pi pi-check-circle',
            command: () => toggleActive(emp),
        },
        { separator: true },
        { label: 'Delete', icon: 'pi pi-trash', class: 'text-red-500', command: () => { showDelete.value = true; } },
    ];
    rowMenuRef.value.toggle(event);
}

// ── Fetch ─────────────────────────────────────────────────
async function fetchEmployees(page = pagination.current_page) {
    loading.value = true;
    try {
        const params = { page, per_page: pagination.per_page };
        if (filters.search) params.search = filters.search;
        if (filters.employee_type) params.employee_type = filters.employee_type;
        if (filters.is_active !== null && filters.is_active !== '') params.is_active = filters.is_active;

        const { data } = await axios.get('/api/employees', { params });
        employees.value = data.data;
        pagination.filtered_total = data.filtered_total;
        pagination.per_page = data.per_page;
        pagination.current_page = data.current_page;
        pagination.last_page = data.last_page;
    } catch {
        toast.add({ severity: 'error', summary: 'Error', detail: 'Could not load employees.', life: 3500 });
    } finally {
        loading.value = false;
    }
}

function onPage(event) {
    pagination.per_page = event.rows;
    fetchEmployees(event.page + 1);
}

function resetFilters() {
    filters.search = '';
    filters.employee_type = null;
    filters.is_active = null;
    fetchEmployees(1);
}

// ── CRUD helpers ──────────────────────────────────────────
function openCreate() {
    selectedEmployee.value = null;
    modalMode.value = 'create';
    showCreateEdit.value = true;
}

function openEdit(emp) {
    selectedEmployee.value = emp;
    modalMode.value = 'edit';
    showCreateEdit.value = true;
}

function onEditFromView(emp) {
    showView.value = false;
    openEdit(emp);
}

function onSaved() {
    showCreateEdit.value = false;
    fetchEmployees(pagination.current_page);
}

async function toggleActive(emp) {
    try {
        const { data } = await axios.patch(`/api/employees/${emp.id}/toggle-active`);
        toast.add({ severity: 'success', summary: 'Updated', detail: data.message, life: 3000 });
        fetchEmployees(pagination.current_page);
    } catch {
        toast.add({ severity: 'error', summary: 'Error', detail: 'Could not update status.', life: 3500 });
    }
}

async function confirmDelete() {
    isDeleting.value = true;
    try {
        await axios.delete(`/api/employees/${selectedEmployee.value.id}`);
        toast.add({ severity: 'success', summary: 'Deleted', detail: 'Employee deleted.', life: 3000 });
        showDelete.value = false;
        fetchEmployees(1);
    } catch {
        toast.add({ severity: 'error', summary: 'Error', detail: 'Could not delete employee.', life: 3500 });
    } finally {
        isDeleting.value = false;
    }
}

// ── Formatters ────────────────────────────────────────────
function formatType(val) {
    return val === 'contract_of_service' ? 'Contract of Service' : 'Project-Based';
}

function money(val) {
    if (!val && val !== 0) return '—';
    return new Intl.NumberFormat('en-PH', { style: 'currency', currency: 'PHP' }).format(val);
}

// ── Init ──────────────────────────────────────────────────
fetchEmployees(1);
</script>
