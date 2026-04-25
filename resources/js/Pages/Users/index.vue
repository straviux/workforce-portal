<template>

    <Head title="User Management" />

    <div class="flex items-center justify-between mb-5 gap-4">
        <div>
            <h1 class="text-xl font-bold text-surface-800 dark:text-surface-50">User Management</h1>
            <p class="text-sm text-surface-400 mt-0.5">
                {{ pagination.filtered_total }} account{{ pagination.filtered_total !== 1 ? 's' : '' }}
            </p>
        </div>
        <div v-if="canManageUsers" class="flex flex-wrap items-center justify-end gap-2">
            <Button icon="pi pi-database" label="Import Scholarship Users" severity="secondary" outlined class="rounded"
                @click="showImportDialog = true" />
            <Button icon="pi pi-plus" label="New User" class="rounded" @click="openCreate" />
        </div>
    </div>

    <div class="ios-card flex flex-wrap gap-3 p-4 mb-4">
        <IconField class="flex-1 min-w-60">
            <InputIcon class="pi pi-search" />
            <InputText v-model="filters.search" placeholder="Search name, username, or email" class="w-full"
                size="small" @keyup.enter="fetchUsers(1)" />
        </IconField>
        <Select v-model="filters.role" :options="roleOptions" optionLabel="name" optionValue="name"
            placeholder="All roles" class="w-48" size="small" showClear @change="fetchUsers(1)" />
        <Button icon="pi pi-filter-slash" severity="secondary" size="small" class="rounded" outlined
            v-tooltip="'Reset filters'" @click="resetFilters" />
    </div>

    <div class="overflow-hidden" style="border-radius:1.5rem;border:1px solid var(--p-datatable-border-color);">
        <DataTable :value="users" :loading="loading" showGridlines stripedRows scrollable lazy
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
                    <p class="text-sm">No users found.</p>
                </div>
            </template>

            <Column field="name" header="Name" style="min-width:220px;">
                <template #body="{ data }">
                    <div>
                        <p class="font-medium">{{ data.name }}</p>
                        <p class="text-xs text-surface-400">Created {{ formatDate(data.created_at) }}</p>
                    </div>
                </template>
            </Column>

            <Column field="username" header="Username" style="min-width:160px;">
                <template #body="{ data }">
                    <span class="font-mono text-xs font-semibold">{{ data.username }}</span>
                </template>
            </Column>

            <Column field="email" header="Email" style="min-width:220px;">
                <template #body="{ data }">
                    <span>{{ data.email || '—' }}</span>
                </template>
            </Column>

            <Column field="roles" header="Roles" style="min-width:220px;">
                <template #body="{ data }">
                    <div class="flex flex-wrap gap-2">
                        <Tag v-for="roleName in data.role_names" :key="roleName" :value="humanizeRole(roleName)"
                            :severity="roleName === 'admin' ? 'danger' : 'secondary'" />
                        <span v-if="!data.role_names?.length" class="text-surface-400">—</span>
                    </div>
                </template>
            </Column>

            <Column v-if="canManageUsers" header="" style="width:60px;min-width:60px;" frozen alignFrozen="right">
                <template #body="{ data }">
                    <Button icon="pi pi-ellipsis-v" severity="secondary" text rounded size="small"
                        @click="(event) => openRowMenu(event, data)" />
                </template>
            </Column>
        </DataTable>
    </div>

    <Menu ref="rowMenuRef" :model="rowMenuItems" popup />

    <Dialog v-model:visible="showCreateEdit" modal :header="modalMode === 'create' ? 'New User' : 'Edit User'"
        :style="{ width: '42rem' }" :draggable="false">
        <div class="space-y-4 pt-1">
            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="ios-label">Full Name <span class="text-red-500">*</span></label>
                    <InputText v-model="form.name" class="w-full" size="small" placeholder="Full name" />
                    <InputError :message="firstError('name')" />
                </div>
                <div>
                    <label class="ios-label">Username <span class="text-red-500">*</span></label>
                    <InputText v-model="form.username" class="w-full" size="small" placeholder="Login username" />
                    <InputError :message="firstError('username')" />
                </div>
            </div>

            <div>
                <label class="ios-label">Email</label>
                <InputText v-model="form.email" class="w-full" size="small" placeholder="name@example.com" />
                <InputError :message="firstError('email')" />
            </div>

            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="ios-label">
                        Password <span v-if="modalMode === 'create'" class="text-red-500">*</span>
                    </label>
                    <InputText v-model="form.password" type="password" class="w-full" size="small"
                        :placeholder="modalMode === 'create' ? 'Minimum 8 characters' : 'Leave blank to keep current password'" />
                    <InputError :message="firstError('password')" />
                </div>
                <div>
                    <label class="ios-label">Confirm Password</label>
                    <InputText v-model="form.password_confirmation" type="password" class="w-full" size="small"
                        placeholder="Repeat password" />
                </div>
            </div>

            <div>
                <label class="ios-label">Assigned Roles <span class="text-red-500">*</span></label>
                <div class="grid md:grid-cols-2 gap-3 mt-2">
                    <label v-for="role in roleOptions" :key="role.id"
                        class="flex items-center gap-3 rounded-2xl border border-surface-200 dark:border-surface-700 px-3 py-3 cursor-pointer bg-surface-0 dark:bg-surface-900/40">
                        <Checkbox v-model="form.role_names" :inputId="`role-${role.id}`" :value="role.name" />
                        <div>
                            <p class="text-sm font-medium">{{ humanizeRole(role.name) }}</p>
                            <p class="text-xs text-surface-400">{{ role.name }}</p>
                        </div>
                    </label>
                </div>
                <InputError :message="firstError('role_names')" />
            </div>
        </div>
        <template #footer>
            <Button label="Cancel" severity="secondary" text class="rounded" @click="showCreateEdit = false" />
            <Button :label="modalMode === 'create' ? 'Create User' : 'Save Changes'" icon="pi pi-check" class="rounded"
                :loading="saving" @click="submit" />
        </template>
    </Dialog>

    <Dialog v-model:visible="showDelete" modal header="Delete User" :style="{ width: '28rem' }" :draggable="false">
        <div class="space-y-2">
            <p class="text-sm text-surface-600 dark:text-surface-300">
                Delete <span class="font-semibold">{{ selectedUser?.name }}</span>?
            </p>
            <p class="text-xs text-surface-400">This removes the account and its role assignments.</p>
        </div>
        <template #footer>
            <Button label="Cancel" severity="secondary" text class="rounded" @click="showDelete = false" />
            <Button label="Delete" icon="pi pi-trash" severity="danger" class="rounded" :loading="deleting"
                @click="confirmDelete" />
        </template>
    </Dialog>

    <Dialog v-model:visible="showImportDialog" modal header="Import Scholarship Users" :style="{ width: '34rem' }"
        :draggable="false">
        <div class="space-y-3 text-sm text-surface-600 dark:text-surface-300">
            <p>
                Import all user accounts from the Scholarship system into Workforce Portal.
            </p>
            <div
                class="rounded-2xl border border-surface-200 dark:border-surface-700 bg-surface-50 dark:bg-surface-900/40 p-4 space-y-2">
                <p>Matching usernames will be updated in Workforce Portal.</p>
                <p>Imported accounts default to <span class="font-semibold">Staff</span>.</p>
                <p>The imported <span class="font-semibold">admin</span> account will be assigned the <span
                        class="font-semibold">Admin</span> role.</p>
                <p>Email conflicts are preserved safely by leaving the imported email blank.</p>
            </div>
        </div>
        <template #footer>
            <Button label="Cancel" severity="secondary" text class="rounded" @click="showImportDialog = false" />
            <Button label="Import Users" icon="pi pi-download" class="rounded" :loading="importingScholarship"
                @click="importScholarshipUsers" />
        </template>
    </Dialog>
</template>

<script setup>
import { computed, reactive, ref } from 'vue';
import { Head, usePage } from '@inertiajs/vue3';
import axios from 'axios';
import { useToast } from 'primevue/usetoast';
import WorkforceLayout from '@/Layouts/WorkforceLayout.vue';
import InputError from '@/Components/ui/inputs/InputError.vue';

defineOptions({ layout: WorkforceLayout });

const toast = useToast();
const page = usePage();

const users = ref([]);
const roleOptions = ref([]);
const loading = ref(false);
const saving = ref(false);
const deleting = ref(false);
const importingScholarship = ref(false);

const pagination = reactive({
    filtered_total: 0,
    per_page: 15,
    current_page: 1,
    last_page: 1,
});

const filters = reactive({
    search: '',
    role: null,
});

const showCreateEdit = ref(false);
const showDelete = ref(false);
const showImportDialog = ref(false);
const modalMode = ref('create');
const selectedUser = ref(null);
const rowMenuRef = ref(null);
const rowMenuItems = ref([]);
const formErrors = ref({});

const defaultForm = () => ({
    name: '',
    username: '',
    email: '',
    password: '',
    password_confirmation: '',
    role_names: [],
});

const form = reactive(defaultForm());

const paginatorFirst = computed(() => (pagination.current_page - 1) * pagination.per_page);
const currentPermissions = computed(() => page.props.auth?.user?.permissions ?? []);
const canManageUsers = computed(() => currentPermissions.value.includes('users.manage'));

function resetForm() {
    Object.assign(form, defaultForm());
    formErrors.value = {};
}

function normalizeErrors(errors = {}) {
    return Object.fromEntries(
        Object.entries(errors).map(([field, messages]) => [field, Array.isArray(messages) ? messages[0] : messages]),
    );
}

function firstError(field) {
    return formErrors.value[field] ?? '';
}

function humanizeRole(roleName) {
    return roleName
        .split(/[_-]/g)
        .map((segment) => segment.charAt(0).toUpperCase() + segment.slice(1))
        .join(' ');
}

function formatDate(value) {
    if (!value) return '—';
    return new Date(value).toLocaleDateString('en-PH', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
}

async function fetchUsers(pageNumber = pagination.current_page) {
    loading.value = true;

    try {
        const params = { page: pageNumber, per_page: pagination.per_page };
        if (filters.search) params.search = filters.search;
        if (filters.role) params.role = filters.role;

        const { data } = await axios.get('/api/users', { params });
        users.value = data.data;
        roleOptions.value = data.role_options ?? [];
        pagination.filtered_total = data.filtered_total;
        pagination.per_page = data.per_page;
        pagination.current_page = data.current_page;
        pagination.last_page = data.last_page;
    } catch {
        toast.add({ severity: 'error', summary: 'Error', detail: 'Could not load users.', life: 3500 });
    } finally {
        loading.value = false;
    }
}

function onPage(event) {
    pagination.per_page = event.rows;
    fetchUsers(event.page + 1);
}

function resetFilters() {
    filters.search = '';
    filters.role = null;
    fetchUsers(1);
}

function openCreate() {
    selectedUser.value = null;
    modalMode.value = 'create';
    resetForm();
    showCreateEdit.value = true;
}

function openEdit(user) {
    selectedUser.value = user;
    modalMode.value = 'edit';
    resetForm();
    Object.assign(form, {
        name: user.name,
        username: user.username,
        email: user.email ?? '',
        password: '',
        password_confirmation: '',
        role_names: [...(user.role_names ?? [])],
    });
    showCreateEdit.value = true;
}

function openRowMenu(event, user) {
    selectedUser.value = user;
    rowMenuItems.value = [
        { label: 'Edit', icon: 'pi pi-pencil', command: () => openEdit(user) },
        { separator: true },
        { label: 'Delete', icon: 'pi pi-trash', class: 'text-red-500', command: () => { showDelete.value = true; } },
    ];
    rowMenuRef.value.toggle(event);
}

function formatImportSummary(summary = {}) {
    const parts = [
        `${summary.imported ?? 0} imported`,
        `${summary.created ?? 0} created`,
        `${summary.updated ?? 0} updated`,
        `${summary.staff_assigned ?? 0} staff`,
        `${summary.admin_assigned ?? 0} admin`,
    ];

    if (summary.email_conflicts) {
        parts.push(`${summary.email_conflicts} email conflict${summary.email_conflicts === 1 ? '' : 's'} cleared`);
    }

    return parts.join(' • ');
}

async function importScholarshipUsers() {
    importingScholarship.value = true;

    try {
        const { data } = await axios.post('/api/users/import-scholarship');
        showImportDialog.value = false;
        toast.add({
            severity: 'success',
            summary: 'Import complete',
            detail: formatImportSummary(data.summary),
            life: 4500,
        });
        await fetchUsers(1);
    } catch (error) {
        toast.add({
            severity: 'error',
            summary: 'Import failed',
            detail: error.response?.data?.message || 'Could not import scholarship users.',
            life: 4500,
        });
    } finally {
        importingScholarship.value = false;
    }
}

async function submit() {
    saving.value = true;
    formErrors.value = {};

    try {
        const payload = {
            name: form.name,
            username: form.username,
            email: form.email || null,
            password: form.password,
            password_confirmation: form.password_confirmation,
            role_names: form.role_names,
        };

        if (modalMode.value === 'edit' && !payload.password) {
            delete payload.password;
            delete payload.password_confirmation;
        }

        if (modalMode.value === 'edit') {
            await axios.put(`/api/users/${selectedUser.value.id}`, payload);
        } else {
            await axios.post('/api/users', payload);
        }

        toast.add({
            severity: 'success',
            summary: modalMode.value === 'edit' ? 'Updated' : 'Created',
            detail: modalMode.value === 'edit' ? 'User updated.' : 'User created.',
            life: 3000,
        });

        showCreateEdit.value = false;
        fetchUsers(modalMode.value === 'create' ? 1 : pagination.current_page);
    } catch (error) {
        if (error.response?.status === 422) {
            formErrors.value = normalizeErrors(error.response.data.errors);
            toast.add({ severity: 'warn', summary: 'Validation Error', detail: firstError('message') || error.response.data.message || 'Please check the form.', life: 3500 });
        } else {
            toast.add({ severity: 'error', summary: 'Error', detail: error.response?.data?.message || 'Could not save user.', life: 3500 });
        }
    } finally {
        saving.value = false;
    }
}

async function confirmDelete() {
    deleting.value = true;

    try {
        await axios.delete(`/api/users/${selectedUser.value.id}`);
        toast.add({ severity: 'success', summary: 'Deleted', detail: 'User deleted.', life: 3000 });
        showDelete.value = false;
        fetchUsers(1);
    } catch (error) {
        toast.add({ severity: 'error', summary: 'Error', detail: error.response?.data?.message || 'Could not delete user.', life: 3500 });
    } finally {
        deleting.value = false;
    }
}

fetchUsers(1);
</script>