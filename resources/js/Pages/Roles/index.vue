<template>

    <Head title="Access Roles" />

    <div class="flex items-center justify-between mb-5 gap-4">
        <div>
            <h1 class="text-xl font-bold text-surface-800 dark:text-surface-50">Access Roles</h1>
            <p class="text-sm text-surface-400 mt-0.5">
                {{ pagination.filtered_total }} role{{ pagination.filtered_total !== 1 ? 's' : '' }} configured
            </p>
        </div>
        <Button v-if="canManageRoles" icon="pi pi-plus" label="New Role" class="rounded" @click="openCreate" />
    </div>

    <div class="ios-card flex flex-wrap gap-3 p-4 mb-4">
        <IconField class="flex-1 min-w-60">
            <InputIcon class="pi pi-search" />
            <InputText v-model="filters.search" placeholder="Search role or permission" class="w-full" size="small"
                @keyup.enter="fetchRoles(1)" />
        </IconField>
        <Button icon="pi pi-filter-slash" severity="secondary" size="small" class="rounded" outlined
            v-tooltip="'Reset filters'" @click="resetFilters" />
    </div>

    <div class="overflow-hidden" style="border-radius:1.5rem;border:1px solid var(--p-datatable-border-color);">
        <DataTable :value="roles" :loading="loading" showGridlines stripedRows scrollable lazy
            :totalRecords="pagination.filtered_total" :rows="pagination.per_page" :first="paginatorFirst" paginator
            @page="onPage" :rowsPerPageOptions="[15, 25, 50]"
            paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink RowsPerPageDropdown" :pt="{
                root: { style: 'border-radius:0;border:none;' },
                tableContainer: { style: 'border-radius:0;' },
                paginator: { style: 'border:none;border-top:1px solid var(--p-datatable-border-color);' }
            }">
            <template #empty>
                <div class="text-center py-12 text-surface-400">
                    <i class="pi pi-shield text-4xl block mb-3"></i>
                    <p class="text-sm">No roles found.</p>
                </div>
            </template>

            <Column field="name" header="Role" style="min-width:200px;">
                <template #body="{ data }">
                    <div>
                        <div class="flex items-center gap-2 flex-wrap">
                            <p class="font-medium">{{ humanizeRole(data.name) }}</p>
                            <Tag v-if="data.is_system" value="System" severity="contrast" />
                        </div>
                        <p class="text-xs text-surface-400">{{ data.name }}</p>
                    </div>
                </template>
            </Column>

            <Column field="permissions" header="Permissions" style="min-width:320px;">
                <template #body="{ data }">
                    <div class="flex flex-wrap gap-2">
                        <Tag v-for="permission in data.permissions" :key="permission"
                            :value="humanizePermission(permission)" severity="secondary" />
                        <span v-if="!data.permissions?.length" class="text-surface-400">No permissions assigned.</span>
                    </div>
                </template>
            </Column>

            <Column field="users_count" header="Users" style="min-width:110px;">
                <template #body="{ data }">
                    <Tag :value="String(data.users_count)" :severity="data.users_count ? 'info' : 'secondary'" />
                </template>
            </Column>

            <Column field="created_at" header="Created" style="min-width:150px;">
                <template #body="{ data }">
                    {{ formatDate(data.created_at) }}
                </template>
            </Column>

            <Column v-if="canManageRoles" header="" style="width:60px;min-width:60px;" frozen alignFrozen="right">
                <template #body="{ data }">
                    <Button icon="pi pi-ellipsis-v" severity="secondary" text rounded size="small"
                        @click="(event) => openRowMenu(event, data)" />
                </template>
            </Column>
        </DataTable>
    </div>

    <Menu ref="rowMenuRef" :model="rowMenuItems" popup />

    <Dialog v-model:visible="showCreateEdit" modal :header="modalMode === 'create' ? 'New Role' : 'Edit Role'"
        :style="{ width: '46rem' }" :draggable="false">
        <div class="space-y-4 pt-1">
            <div>
                <label class="ios-label">Role Name <span class="text-red-500">*</span></label>
                <InputText v-model="form.name" class="w-full" size="small" placeholder="e.g. finance_reviewer"
                    :disabled="isEditingSystemRole" />
                <p v-if="isEditingSystemRole" class="mt-2 text-xs text-surface-400">
                    System role names are locked. You can update permissions, but not rename this role.
                </p>
                <InputError :message="firstError('name')" />
            </div>

            <div>
                <div class="flex items-center justify-between mb-2">
                    <label class="ios-label">Permissions</label>
                    <span class="text-xs text-surface-400">{{ form.permissions.length }} selected</span>
                </div>
                <div class="space-y-4 max-h-[24rem] overflow-y-auto pr-1">
                    <div v-for="group in permissionGroups" :key="group.name"
                        class="rounded-2xl border border-surface-200 dark:border-surface-700 p-4 bg-surface-0 dark:bg-surface-900/40">
                        <p class="text-sm font-semibold mb-3">{{ humanizeGroup(group.name) }}</p>
                        <div class="grid md:grid-cols-2 gap-3">
                            <label v-for="permission in group.permissions" :key="permission.id"
                                class="flex items-start gap-3 cursor-pointer rounded-xl px-3 py-2 hover:bg-surface-50 dark:hover:bg-surface-800/60">
                                <Checkbox v-model="form.permissions" :inputId="`permission-${permission.id}`"
                                    :value="permission.name" />
                                <div>
                                    <p class="text-sm font-medium">{{ humanizePermissionAction(permission.name) }}</p>
                                    <p class="text-xs text-surface-400">{{ permission.name }}</p>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>
                <InputError :message="firstError('permissions')" />
            </div>
        </div>
        <template #footer>
            <Button label="Cancel" severity="secondary" text class="rounded" @click="showCreateEdit = false" />
            <Button :label="modalMode === 'create' ? 'Create Role' : 'Save Changes'" icon="pi pi-check" class="rounded"
                :loading="saving" @click="submit" />
        </template>
    </Dialog>

    <Dialog v-model:visible="showDelete" modal header="Delete Role" :style="{ width: '28rem' }" :draggable="false">
        <div class="space-y-2">
            <p class="text-sm text-surface-600 dark:text-surface-300">
                Delete <span class="font-semibold">{{ humanizeRole(selectedRole?.name ?? '') }}</span>?
            </p>
            <p class="text-xs text-surface-400">Roles assigned to users cannot be deleted until those assignments are
                removed.</p>
        </div>
        <template #footer>
            <Button label="Cancel" severity="secondary" text class="rounded" @click="showDelete = false" />
            <Button label="Delete" icon="pi pi-trash" severity="danger" class="rounded" :loading="deleting"
                @click="confirmDelete" />
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

const roles = ref([]);
const permissionOptions = ref([]);
const loading = ref(false);
const saving = ref(false);
const deleting = ref(false);

const pagination = reactive({
    filtered_total: 0,
    per_page: 15,
    current_page: 1,
    last_page: 1,
});

const filters = reactive({ search: '' });

const showCreateEdit = ref(false);
const showDelete = ref(false);
const modalMode = ref('create');
const selectedRole = ref(null);
const rowMenuRef = ref(null);
const rowMenuItems = ref([]);
const formErrors = ref({});

const defaultForm = () => ({
    name: '',
    permissions: [],
});

const form = reactive(defaultForm());

const paginatorFirst = computed(() => (pagination.current_page - 1) * pagination.per_page);
const currentPermissions = computed(() => page.props.auth?.user?.permissions ?? []);
const canManageRoles = computed(() => currentPermissions.value.includes('roles.manage'));
const isEditingSystemRole = computed(() => modalMode.value === 'edit' && Boolean(selectedRole.value?.is_system));
const permissionGroups = computed(() => {
    const groups = permissionOptions.value.reduce((collection, permission) => {
        const [groupName = 'general'] = permission.name.split('.');
        if (!collection[groupName]) {
            collection[groupName] = [];
        }

        collection[groupName].push(permission);
        return collection;
    }, {});

    return Object.entries(groups).map(([name, permissions]) => ({ name, permissions }));
});

function normalizeErrors(errors = {}) {
    return Object.fromEntries(
        Object.entries(errors).map(([field, messages]) => [field, Array.isArray(messages) ? messages[0] : messages]),
    );
}

function firstError(field) {
    return formErrors.value[field] ?? '';
}

function resetForm() {
    Object.assign(form, defaultForm());
    formErrors.value = {};
}

function humanizeRole(roleName) {
    return roleName
        .split(/[_-]/g)
        .map((segment) => segment.charAt(0).toUpperCase() + segment.slice(1))
        .join(' ');
}

function humanizeGroup(groupName) {
    return groupName
        .split(/[_-]/g)
        .map((segment) => segment.charAt(0).toUpperCase() + segment.slice(1))
        .join(' ');
}

function humanizePermission(permissionName) {
    const [groupName = permissionName, action = permissionName] = permissionName.split('.');

    return `${humanizeGroup(groupName)}: ${humanizePermissionAction(action)}`;
}

function humanizePermissionAction(permissionName) {
    const action = permissionName.includes('.')
        ? permissionName.split('.').slice(1).join('.')
        : permissionName;

    return action
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

async function fetchRoles(pageNumber = pagination.current_page) {
    loading.value = true;

    try {
        const params = { page: pageNumber, per_page: pagination.per_page };
        if (filters.search) params.search = filters.search;

        const { data } = await axios.get('/api/roles', { params });
        roles.value = data.data;
        permissionOptions.value = data.permission_options ?? [];
        pagination.filtered_total = data.filtered_total;
        pagination.per_page = data.per_page;
        pagination.current_page = data.current_page;
        pagination.last_page = data.last_page;
    } catch {
        toast.add({ severity: 'error', summary: 'Error', detail: 'Could not load roles.', life: 3500 });
    } finally {
        loading.value = false;
    }
}

function onPage(event) {
    pagination.per_page = event.rows;
    fetchRoles(event.page + 1);
}

function resetFilters() {
    filters.search = '';
    fetchRoles(1);
}

function openCreate() {
    selectedRole.value = null;
    modalMode.value = 'create';
    resetForm();
    showCreateEdit.value = true;
}

function openEdit(role) {
    selectedRole.value = role;
    modalMode.value = 'edit';
    resetForm();
    Object.assign(form, {
        name: role.name,
        permissions: [...(role.permissions ?? [])],
    });
    showCreateEdit.value = true;
}

function openRowMenu(event, role) {
    selectedRole.value = role;
    rowMenuItems.value = role.is_system
        ? [
            { label: 'Edit permissions', icon: 'pi pi-pencil', command: () => openEdit(role) },
            { separator: true },
            { label: 'System role cannot be renamed or deleted', icon: 'pi pi-lock', disabled: true },
        ]
        : [
            { label: 'Edit', icon: 'pi pi-pencil', command: () => openEdit(role) },
            { separator: true },
            { label: 'Delete', icon: 'pi pi-trash', class: 'text-red-500', command: () => { showDelete.value = true; } },
        ];
    rowMenuRef.value.toggle(event);
}

async function submit() {
    saving.value = true;
    formErrors.value = {};

    try {
        const payload = {
            name: form.name,
            permissions: form.permissions,
        };

        if (modalMode.value === 'edit') {
            await axios.put(`/api/roles/${selectedRole.value.id}`, payload);
        } else {
            await axios.post('/api/roles', payload);
        }

        toast.add({
            severity: 'success',
            summary: modalMode.value === 'edit' ? 'Updated' : 'Created',
            detail: modalMode.value === 'edit' ? 'Role updated.' : 'Role created.',
            life: 3000,
        });

        showCreateEdit.value = false;
        fetchRoles(modalMode.value === 'create' ? 1 : pagination.current_page);
    } catch (error) {
        if (error.response?.status === 422) {
            formErrors.value = normalizeErrors(error.response.data.errors);
            toast.add({ severity: 'warn', summary: 'Validation Error', detail: error.response.data.message || 'Please check the form.', life: 3500 });
        } else {
            toast.add({ severity: 'error', summary: 'Error', detail: error.response?.data?.message || 'Could not save role.', life: 3500 });
        }
    } finally {
        saving.value = false;
    }
}

async function confirmDelete() {
    deleting.value = true;

    try {
        await axios.delete(`/api/roles/${selectedRole.value.id}`);
        toast.add({ severity: 'success', summary: 'Deleted', detail: 'Role deleted.', life: 3000 });
        showDelete.value = false;
        fetchRoles(1);
    } catch (error) {
        toast.add({ severity: 'error', summary: 'Error', detail: error.response?.data?.message || 'Could not delete role.', life: 3500 });
    } finally {
        deleting.value = false;
    }
}

fetchRoles(1);
</script>