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
        <div class="flex items-center gap-2">
            <Button v-if="isAdmin" label="Match Users" severity="secondary" outlined class="rounded"
                @click="showUserMatch = true"><template #icon><AppIcon name="Users" /></template></Button>
            <Button v-if="canManageEmployees" label="New Employee" class="rounded"
                @click="openCreate"><template #icon><AppIcon name="Plus" /></template></Button>
        </div>
    </div>

    <!-- Filter Toolbar -->
    <div class="ios-card flex flex-wrap gap-3 p-4 mb-4">
        <IconField class="flex-1 min-w-50">
            <InputIcon><AppIcon name="Search" /></InputIcon>
            <InputText v-model="filters.search" placeholder="Search name, employee no., office…" class="w-full"
                size="small" @keyup.enter="fetchEmployees(1)" />
        </IconField>
        <Select v-model="filters.employee_type" :options="typeOptions" optionLabel="label" optionValue="value"
            placeholder="All types" class="w-44" size="small" showClear @change="fetchEmployees(1)" />
        <Select v-model="filters.is_active" :options="activeOptions" optionLabel="label" optionValue="value"
            placeholder="All statuses" class="w-36" size="small" showClear @change="fetchEmployees(1)" />
        <Button severity="secondary" size="small" class="rounded" outlined
            v-tooltip="'Reset filters'" @click="resetFilters"><template #icon><AppIcon name="FilterX" /></template></Button>
    </div>

    <!-- DataTable -->
    <div class="overflow-hidden" style="border-radius:1.5rem;border:1px solid var(--p-datatable-border-color);">
        <DataTable v-model:contextMenuSelection="contextMenuEmployee" :value="employees" :loading="loading" contextMenu
            showGridlines stripedRows scrollable lazy @row-contextmenu="openRowContextMenu"
            :totalRecords="pagination.filtered_total" :rows="pagination.per_page" :first="paginatorFirst" paginator
            @page="onPage" :rowsPerPageOptions="[15, 25, 50]"
            paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink RowsPerPageDropdown" :pt="{
                root: { style: 'border-radius:0;border:none;' },
                tableContainer: { style: 'border-radius:0;' },
                paginator: { style: 'border:none;border-top:1px solid var(--p-datatable-border-color);' }
            }">
            <template #empty>
                <div class="text-center py-12 text-surface-400">
                    <AppIcon name="Users" :size="36" class="block mb-3" />
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
                    <div class="flex flex-col gap-1">
                        <span class="font-medium">{{ data.full_name }}</span>
                        <Tag :value="formatType(data.employee_type)" severity="secondary" class="w-fit" />
                    </div>
                </template>
            </Column>

            <Column field="office" header="Office / Designation" style="min-width:200px;">
                <template #body="{ data }">
                    <div class="flex flex-col">
                        <span>{{ data.office || '—' }}</span>
                        <span v-if="data.employee_type === 'contract_of_service' && data.designation"
                            class="text-sm text-surface-500">{{ data.designation }}</span>
                    </div>
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

            <Column field="user" header="Portal User" style="min-width:150px;">
                <template #body="{ data }">
                    <Tag v-if="data.user" :value="`@${data.user.username}`" severity="info" />
                    <span v-else class="text-surface-300 text-xs">Not linked</span>
                </template>
            </Column>

            <Column header="" style="width:60px;min-width:60px;" frozen alignFrozen="right">
                <template #body="{ data }">
                    <Button severity="secondary" text rounded size="small"
                        @click="(e) => openRowMenu(e, data)"><template #icon><AppIcon name="EllipsisVertical" /></template></Button>
                </template>
            </Column>
        </DataTable>
    </div>

    <!-- Row context menu -->
    <ContextMenu ref="rowMenuRef" :model="rowMenuItems" @hide="contextMenuEmployee = null">
        <template #item="{ item, label, props }">
            <a v-bind="props.action">
                <AppIcon v-if="item.iconName" :name="item.iconName" :size="16" class="mr-2 shrink-0" />
                <span v-bind="props.label">{{ label }}</span>
            </a>
        </template>
    </ContextMenu>

    <!-- Modals -->
    <CreateEditModal v-model:show="showCreateEdit" :employee="selectedEmployee" :mode="modalMode" @saved="onSaved" />
    <ViewModal v-model:show="showView" :employee="selectedEmployee" @edit="onEditFromView" />
    <DeleteConfirmModal v-model:show="showDelete" :employee="selectedEmployee ?? ''"
        :action-state="deleteAction.state" :action-label="deleteAction.label"
        :action-morph-class="deleteAction.morphClass" :action-disabled="deleteAction.disabled"
        @confirm-delete="confirmDelete" />
    <UserMatchModal v-model:show="showUserMatch" @matched="fetchEmployees(pagination.current_page)" />
</template>

<script setup>
import { ref, reactive, computed } from 'vue';
import { Head, usePage } from '@inertiajs/vue3';
import ContextMenu from 'primevue/contextmenu';
import WorkforceLayout from '@/Layouts/WorkforceLayout.vue';
import CreateEditModal from './Modal/CreateEditModal.vue';
import ViewModal from './Modal/ViewModal.vue';
import DeleteConfirmModal from './Modal/DeleteConfirmModal.vue';
import UserMatchModal from './Modal/UserMatchModal.vue';
import { useToast } from 'primevue/usetoast';
import axios from 'axios';
import { useActionButton } from '@/Composables/useActionButton';

defineOptions({ layout: WorkforceLayout });

const toast = useToast();
const page = usePage();

// ── State ────────────────────────────────────────────────
const employees = ref([]);
const loading = ref(false);
// Parent-owned morph state for the delete button inside DeleteConfirmModal
// (the request lives here, so the state lives here too). Wrapped in
// reactive() so template prop bindings unwrap the composable's refs.
const deleteAction = reactive(useActionButton({ idleLabel: 'Delete', processingLabel: 'Deleting...', successLabel: 'Deleted' }));

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
const showUserMatch = ref(false);
const selectedEmployee = ref(null);
const modalMode = ref('create');
const contextMenuEmployee = ref(null);

// ── Row menu ──────────────────────────────────────────────
const rowMenuRef = ref(null);
const rowMenuItems = ref([]);
const userPermissions = computed(() => page.props.auth?.user?.permissions ?? []);
const canManageEmployees = computed(() => userPermissions.value.includes('employees.manage'));
const canDeleteEmployees = computed(() => userPermissions.value.includes('employees.delete'));
const isAdmin = computed(() => (page.props.auth?.user?.roles ?? []).includes('admin'));

function setRowMenuItems(emp) {
    selectedEmployee.value = emp;
    contextMenuEmployee.value = emp;

    const items = [
        { label: 'View Details', iconName: 'Eye', command: () => { showView.value = true; } },
    ];

    if (canManageEmployees.value) {
        items.push(
            { label: 'Edit', iconName: 'Pencil', command: () => openEdit(emp) },
            { separator: true },
            {
                label: emp.is_active ? 'Deactivate' : 'Activate',
                iconName: emp.is_active ? 'Ban' : 'CircleCheck',
                command: () => toggleActive(emp),
            },
        );
    }

    if (canDeleteEmployees.value) {
        if (items[items.length - 1]?.separator !== true) {
            items.push({ separator: true });
        }

        items.push({ label: 'Delete', iconName: 'Trash2', class: 'text-red-500', command: () => { showDelete.value = true; } });
    }

    rowMenuItems.value = items;
}

function showRowMenu(mouseEvent) {
    if (typeof rowMenuRef.value?.show === 'function') {
        rowMenuRef.value.show(mouseEvent);
        return;
    }

    rowMenuRef.value?.toggle(mouseEvent);
}

function openRowMenu(event, emp) {
    setRowMenuItems(emp);
    showRowMenu(event);
}

function openRowContextMenu(event) {
    event.originalEvent.preventDefault();
    setRowMenuItems(event.data);
    showRowMenu(event.originalEvent);
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
    if (deleteAction.state !== 'idle') return;
    try {
        await deleteAction.run(async () => {
            try {
                await axios.delete(`/api/employees/${selectedEmployee.value.id}`);
                toast.add({ severity: 'success', summary: 'Deleted', detail: 'Employee deleted.', life: 3000 });
            } catch (err) {
                toast.add({ severity: 'error', summary: 'Error', detail: 'Could not delete employee.', life: 3500 });
                throw err;
            }
        });
    } catch {
        return;
    }
    // Only after the full morph cycle (run resolves after success shows).
    showDelete.value = false;
    fetchEmployees(1);
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
