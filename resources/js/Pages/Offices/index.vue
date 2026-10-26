<script setup>
import { ref, reactive, computed, onMounted } from 'vue';
import { Head, usePage } from '@inertiajs/vue3';
import { useActionButton } from '@/Composables/useActionButton';
import ContextMenu from 'primevue/contextmenu';
import { useToast } from 'primevue/usetoast';
import axios from 'axios';
import WorkforceLayout from '@/Layouts/WorkforceLayout.vue';
import OfficeModal from './Modal/OfficeModal.vue';
import DeleteConfirmModal from './Modal/DeleteConfirmModal.vue';

defineOptions({
    layout: WorkforceLayout,
});

const toast = useToast();
const page = usePage();

const offices = ref([]);
const officeHeads = ref([]);
const loading = ref(false);
// Parent-owned morph state for the delete button inside DeleteConfirmModal
// (the request lives here, so the state lives here too). Wrapped in
// reactive() so template prop bindings unwrap the composable's refs.
const deleteAction = reactive(useActionButton({ idleLabel: 'Delete', processingLabel: 'Deleting...', successLabel: 'Deleted' }));
const contextMenuRef = ref(null);
const contextMenuItems = ref([]);
const userPermissions = computed(() => page.props.auth?.user?.permissions ?? []);
const canManageOffices = computed(() => userPermissions.value.includes('offices.manage'));
const canDeleteOffices = computed(() => userPermissions.value.includes('offices.delete'));

// Office Modal
const showOfficeModal = ref(false);
const officeModalMode = ref('create');
const editingOffice = ref(null);
const presetParentOffice = ref(null);

const openOfficeModal = (office = null, parentOffice = null) => {
    editingOffice.value = office ?? null;
    presetParentOffice.value = parentOffice ?? null;
    officeModalMode.value = office ? 'edit' : 'create';
    showOfficeModal.value = true;
};

// Delete Modal
const showDeleteModal = ref(false);
const deleteTarget = ref(null);

const confirmDeleteOffice = (office) => {
    deleteTarget.value = office;
    showDeleteModal.value = true;
};

const showContextMenu = (mouseEvent) => {
    if (typeof contextMenuRef.value?.show === 'function') {
        contextMenuRef.value.show(mouseEvent);
        return;
    }

    contextMenuRef.value?.toggle(mouseEvent);
};

const buildMainOfficeMenuItems = (office) => {
    const items = [];

    if (canManageOffices.value) {
        items.push(
            { label: 'Add Assignment', iconName: 'Plus', command: () => openOfficeModal(null, office) },
            { label: 'Edit Main Office', iconName: 'Pencil', command: () => openOfficeModal(office) },
        );
    }

    if (canDeleteOffices.value) {
        if (items.length > 0) {
            items.push({ separator: true });
        }

        items.push({
            label: 'Delete Main Office',
            iconName: 'Trash2',
            class: 'text-red-500',
            command: () => confirmDeleteOffice(office),
        });
    }

    return items;
};

const buildAssignmentMenuItems = (assignment) => {
    const items = [];

    if (canManageOffices.value) {
        items.push({
            label: 'Edit Assignment',
            iconName: 'Pencil',
            command: () => openOfficeModal(assignment),
        });
    }

    if (canDeleteOffices.value) {
        if (items.length > 0) {
            items.push({ separator: true });
        }

        items.push({
            label: 'Delete Assignment',
            iconName: 'Trash2',
            class: 'text-red-500',
            command: () => confirmDeleteOffice(assignment),
        });
    }

    return items;
};

// Kebab buttons reuse the single shared ContextMenu.
const openMainOfficeKebabMenu = (event, office) => {
    const items = buildMainOfficeMenuItems(office);

    if (items.length === 0) {
        return;
    }

    contextMenuItems.value = items;
    showContextMenu(event);
};

const openAssignmentKebabMenu = (event, assignment) => {
    const items = buildAssignmentMenuItems(assignment);

    if (items.length === 0) {
        return;
    }

    contextMenuItems.value = items;
    showContextMenu(event);
};

const handleDeleteConfirm = async () => {
    if (deleteAction.state !== 'idle') return;
    try {
        await deleteAction.run(async () => {
            try {
                await axios.delete(`/api/offices/${deleteTarget.value.id}`);
                toast.add({ severity: 'success', summary: 'Success', detail: 'Office deleted', life: 3000 });
            } catch (err) {
                toast.add({ severity: 'error', summary: 'Error', detail: err.response?.data?.message ?? 'Failed to delete', life: 5000 });
                throw err;
            }
        });
        await fetchOffices();
        showDeleteModal.value = false;
    } catch {
        // Error toast already shown; modal still closes, as before.
        showDeleteModal.value = false;
    }
};

const fetchOffices = async () => {
    loading.value = true;
    try {
        const response = await axios.get('/api/offices');
        offices.value = Array.isArray(response.data) ? response.data : (response.data.data ?? []);
    } catch (err) {
        toast.add({ severity: 'error', summary: 'Error', detail: err.response?.data?.message ?? 'Failed to load offices', life: 5000 });
    } finally {
        loading.value = false;
    }
};

const fetchOfficeHeads = async () => {
    try {
        const response = await axios.get('/api/signatories');
        const signatories = response.data.data ?? [];
        officeHeads.value = signatories.filter((s) => s.part === 'A');
    } catch {
        officeHeads.value = [];
    }
};

onMounted(() => {
    fetchOffices();
    fetchOfficeHeads();
});
</script>

<template>

    <Head title="Offices" />

    <div>
        <!-- Toolbar -->
        <Toolbar class="mb-4 -mt-2 !rounded-4xl !px-8">
            <template #start>
                <div class="flex items-center gap-3">
                    <AppIcon name="Building2" :size="32" class="text-blue-500" />
                    <div>
                        <h1 class="text-2xl font-bold text-gray-700 dark:text-gray-100">Offices</h1>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Manage main offices, their assignments, and
                            each office's assigned head</p>
                    </div>
                </div>
            </template>
            <template #end>
                <Button v-if="canManageOffices" severity="success" rounded outlined
                    @click="openOfficeModal()" v-tooltip.bottom="`Add Office`"><template #icon><AppIcon name="Plus" /></template></Button>
            </template>
        </Toolbar>

        <!-- Loading -->
        <div v-if="loading" class="flex items-center justify-center py-24">
            <AppIcon name="LoaderCircle" :size="36" class="animate-spin text-blue-500" />
        </div>

        <!-- Empty -->
        <Panel v-else-if="offices.length === 0" class="!rounded-4xl overflow-hidden mt-8">
            <div class="flex flex-col items-center justify-center py-16 text-gray-400">
                <AppIcon name="Inbox" :size="48" class="mb-4" />
                <p class="text-base">No offices yet</p>
                <Button v-if="canManageOffices" label="Add Office"
                    severity="secondary" outlined rounded class="mt-4" @click="openOfficeModal()"><template #icon><AppIcon name="Plus" /></template></Button>
            </div>
        </Panel>

        <!-- Main Office Cards -->
        <div v-else class="space-y-4 mt-8">
            <Panel v-for="office in offices" :key="office.id" class="!rounded-4xl overflow-hidden">

                <!-- Main Office Info Bar -->
                <div class="flex items-center justify-between gap-4 mb-4 p-3 bg-gray-50 dark:bg-[#1e242b] rounded-4xl -mt-2">
                    <div class="flex items-center gap-3">
                        <AppIcon name="Building2" :size="20" class="text-blue-500" />
                        <div>
                            <h3 class="font-bold text-gray-700 dark:text-gray-100">{{ office.name }}</h3>
                            <div class="flex gap-3 text-xs text-gray-500 mt-0.5">
                                <span v-if="office.office_head">Head: <span class="font-medium text-gray-700 dark:text-gray-300">{{
                                    office.office_head.name }}</span></span>
                                <span v-else class="text-gray-400">No head assigned</span>
                                <span class="text-gray-400">{{ office.children?.length ?? 0 }} assignment(s)</span>
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <Button v-if="canManageOffices" size="small" severity="success"
                            rounded text @click="openOfficeModal(null, office)" v-tooltip.bottom="`Add Assignment`"><template #icon><AppIcon name="Plus" /></template></Button>
                        <Button v-if="canManageOffices || canDeleteOffices"
                            severity="secondary" text rounded size="small" @click="(e) => openMainOfficeKebabMenu(e, office)"
                            v-tooltip.bottom="`Actions`"><template #icon><AppIcon name="EllipsisVertical" /></template></Button>
                    </div>
                </div>

                <!-- Assignments -->
                <div v-if="office.children && office.children.length > 0" class="space-y-2 ml-6">
                    <div v-for="assignment in office.children" :key="assignment.id"
                        class="flex items-center justify-between gap-4 p-3 rounded-2xl border border-surface-200 dark:border-surface-800">
                        <div class="flex items-center gap-3 min-w-0">
                            <AppIcon name="CornerDownRight" :size="16" class="text-gray-400 shrink-0" />
                            <div class="min-w-0">
                                <p class="font-medium text-gray-800 dark:text-gray-100 truncate">{{ assignment.name }}</p>
                                <p class="text-xs text-gray-500 mt-0.5">
                                    <span v-if="assignment.office_head">Head: <span class="font-medium text-gray-700 dark:text-gray-300">{{
                                        assignment.office_head.name }}</span></span>
                                    <span v-else class="text-gray-400">No head assigned, main office head applies</span>
                                </p>
                            </div>
                        </div>
                        <Button v-if="canManageOffices || canDeleteOffices"
                            severity="secondary" text rounded size="small" @click="(e) => openAssignmentKebabMenu(e, assignment)"
                            v-tooltip.top="`Actions`"><template #icon><AppIcon name="EllipsisVertical" /></template></Button>
                    </div>
                </div>

                <div v-else class="flex flex-col items-center justify-center py-8 text-gray-400">
                    <AppIcon name="Inbox" :size="36" class="mb-2" />
                    <p class="text-sm">No assignments yet</p>
                </div>

            </Panel>
        </div>

        <!-- Office Modal -->
        <OfficeModal v-model:show="showOfficeModal" :office="editingOffice" :mode="officeModalMode"
            :parent-office="presetParentOffice" :main-offices="offices" :office-heads="officeHeads"
            @saved="fetchOffices" />

        <!-- Delete Confirm Modal -->
        <DeleteConfirmModal v-model:show="showDeleteModal" :target-name="deleteTarget?.name ?? ''"
            :is-main-office="deleteTarget ? deleteTarget.parent_id === null : false"
            :assignment-count="deleteTarget?.children?.length ?? 0"
            :action-label="deleteAction.label" :action-state="deleteAction.state"
            :action-loading="deleteAction.loading" :action-disabled="deleteAction.disabled"
            :action-morph-class="deleteAction.morphClass" @confirm="handleDeleteConfirm" />

        <ContextMenu ref="contextMenuRef" :model="contextMenuItems">
            <template #item="{ item, label, props }">
                <a v-bind="props.action">
                    <AppIcon v-if="item.iconName" :name="item.iconName" :size="16" class="mr-2 shrink-0" />
                    <span v-bind="props.label">{{ label }}</span>
                </a>
            </template>
        </ContextMenu>
    </div>
</template>

<style scoped>
:deep(.p-inputtext),
:deep(.p-select) {
    border-radius: 1rem;
}
</style>
