<script setup>
import { ref, computed, onMounted } from 'vue';
import { Head } from '@inertiajs/vue3';
import { useToast } from 'primevue/usetoast';
import axios from 'axios';
import WorkforceLayout from '@/Layouts/WorkforceLayout.vue';
import RCModal from './Modal/RCModal.vue';
import ParticularModal from './Modal/ParticularModal.vue';
import DeleteConfirmModal from './Modal/DeleteConfirmModal.vue';

defineOptions({
    layout: WorkforceLayout,
});

const toast = useToast();

const responsibilityCenters = ref([]);
const loading = ref(false);
const deleting = ref(false);

// RC Modal
const showRCModal = ref(false);
const rcModalMode = ref('create');
const editingRC = ref(null);

const openRCModal = (rc = null) => {
    editingRC.value = rc ?? null;
    rcModalMode.value = rc ? 'edit' : 'create';
    showRCModal.value = true;
};

// Particular Modal
const showParticularModal = ref(false);
const particularModalMode = ref('create');
const particularRC = ref(null);
const editingParticular = ref(null);

const openParticularsModal = (rc, particular = null) => {
    particularRC.value = rc;
    editingParticular.value = particular ?? null;
    particularModalMode.value = particular ? 'edit' : 'create';
    showParticularModal.value = true;
};

// Delete Modal
const showDeleteModal = ref(false);
const deleteType = ref('rc');
const deleteTargetId = ref(null);
const deleteTargetRCId = ref(null);
const deleteTargetName = ref('');

const deleteRC = (rc) => {
    deleteType.value = 'rc';
    deleteTargetId.value = rc.id;
    deleteTargetName.value = rc.name;
    showDeleteModal.value = true;
};

const confirmDeleteParticular = (rcId, particular) => {
    deleteType.value = 'particular';
    deleteTargetRCId.value = rcId;
    deleteTargetId.value = particular.id;
    deleteTargetName.value = particular.name;
    showDeleteModal.value = true;
};

const handleDeleteConfirm = async () => {
    deleting.value = true;
    try {
        if (deleteType.value === 'rc') {
            await axios.delete(`/api/responsibility-centers/${deleteTargetId.value}`);
            toast.add({ severity: 'success', summary: 'Success', detail: 'Responsibility Center deleted', life: 3000 });
        } else {
            await axios.delete(`/api/responsibility-centers/${deleteTargetRCId.value}/particulars/${deleteTargetId.value}`);
            toast.add({ severity: 'success', summary: 'Success', detail: 'Particular deleted', life: 3000 });
        }
        await fetchResponsibilityCenters();
        showDeleteModal.value = false;
    } catch (err) {
        toast.add({ severity: 'error', summary: 'Error', detail: err.response?.data?.message ?? 'Failed to delete', life: 5000 });
        showDeleteModal.value = false;
    } finally {
        deleting.value = false;
    }
};

const fetchResponsibilityCenters = async () => {
    loading.value = true;
    try {
        const response = await axios.get('/api/responsibility-centers');
        responsibilityCenters.value = Array.isArray(response.data) ? response.data : (response.data.data ?? []);
    } catch (err) {
        toast.add({ severity: 'error', summary: 'Error', detail: err.response?.data?.message ?? 'Failed to load responsibility centers', life: 5000 });
    } finally {
        loading.value = false;
    }
};

onMounted(fetchResponsibilityCenters);
</script>

<template>

    <Head title="Responsibility Centers" />

    <div>
        <!-- Toolbar -->
        <Toolbar class="mb-4 -mt-2 !rounded-4xl !px-8">
            <template #start>
                <div class="flex items-center gap-3">
                    <i class="pi pi-building text-blue-500 text-[2rem]"></i>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-700 dark:text-gray-100">Responsibility Centers</h1>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Manage responsibility centers and their
                            particulars</p>
                    </div>
                </div>
            </template>
            <template #end>
                <Button icon="pi pi-plus" severity="success" rounded outlined @click="openRCModal()"
                    v-tooltip.bottom="`Add Responsibility Center`" />
            </template>
        </Toolbar>

        <!-- Loading -->
        <div v-if="loading" class="flex items-center justify-center py-24">
            <i class="pi pi-spin pi-spinner text-3xl text-blue-500"></i>
        </div>

        <!-- Empty -->
        <Panel v-else-if="responsibilityCenters.length === 0" class="!rounded-4xl overflow-hidden mt-8">
            <div class="flex flex-col items-center justify-center py-16 text-gray-400">
                <i class="pi pi-inbox text-5xl mb-4"></i>
                <p class="text-base">No responsibility centers yet</p>
                <Button icon="pi pi-plus" label="Add Responsibility Center" severity="secondary" outlined rounded
                    class="mt-4" @click="openRCModal()" />
            </div>
        </Panel>

        <!-- RC Cards -->
        <div v-else class="space-y-4 mt-8">
            <Panel v-for="rc in responsibilityCenters" :key="rc.id" class="!rounded-4xl overflow-hidden">

                <!-- RC Info Bar -->
                <div
                    class="flex items-center justify-between gap-4 mb-4 p-3 bg-gray-50 dark:bg-[#1e242b] rounded-4xl -mt-2">
                    <div class="flex items-center gap-3">
                        <i class="pi pi-building text-blue-500 text-xl"></i>
                        <div>
                            <h3 class="font-bold text-gray-700 dark:text-gray-100">{{ rc.name }}</h3>
                            <div class="flex gap-3 text-xs text-gray-500 mt-0.5">
                                <span>Code: <span class="font-mono font-semibold text-gray-700 dark:text-gray-300">{{
                                        rc.code
                                        }}</span></span>
                                <span v-if="rc.fiscal_year">FY: <span class="font-medium">{{ rc.fiscal_year
                                        }}</span></span>
                                <span class="text-gray-400">{{ rc.particulars?.length ?? 0 }} particular(s)</span>
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <Button icon="pi pi-plus" size="small" severity="success" rounded text
                            @click="openParticularsModal(rc)" v-tooltip.bottom="`Add Particular`" />
                        <Button icon="pi pi-pencil" severity="secondary" text rounded size="small"
                            @click="openRCModal(rc)" v-tooltip.bottom="`Edit RC`" />
                        <Button icon="pi pi-trash" severity="danger" text rounded size="small" @click="deleteRC(rc)"
                            v-tooltip.bottom="`Delete RC`" />
                    </div>
                </div>

                <!-- Particulars DataTable -->
                <DataTable v-if="rc.particulars && rc.particulars.length > 0" :value="rc.particulars" showGridlines
                    stripedRows scrollable class="text-sm">
                    <Column field="name" header="Particular" style="min-width: 200px">
                        <template #body="{ data }">
                            <span class="font-medium text-gray-800 dark:text-gray-100">{{ data.name }}</span>
                        </template>
                    </Column>
                    <Column field="account_code" header="Account Code" style="min-width: 140px">
                        <template #body="{ data }">
                            <span class="font-mono text-xs bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded-lg">{{
                                data.account_code }}</span>
                        </template>
                    </Column>
                    <Column header="Allotment" style="min-width: 140px">
                        <template #body="{ data }">
                            <span v-if="data.allotment" class="font-semibold text-green-700 dark:text-green-400">
                                ₱{{ parseFloat(data.allotment).toLocaleString('en-US', { minimumFractionDigits: 2 }) }}
                            </span>
                            <span v-else class="text-xs text-gray-400">—</span>
                        </template>
                    </Column>
                    <Column header="Date Approved" style="min-width: 140px">
                        <template #body="{ data }">
                            <span v-if="data.date_approved" class="text-xs text-gray-600 dark:text-gray-400">{{
                                data.date_approved }}</span>
                            <span v-else class="text-xs text-gray-400">—</span>
                        </template>
                    </Column>
                    <Column header="Date Expired" style="min-width: 140px">
                        <template #body="{ data }">
                            <span v-if="data.date_expired" class="text-xs text-gray-600 dark:text-gray-400">{{
                                data.date_expired }}</span>
                            <span v-else class="text-xs text-gray-400">—</span>
                        </template>
                    </Column>
                    <Column header="" style="width: 90px">
                        <template #body="{ data }">
                            <div class="flex items-center gap-1 justify-end">
                                <Button icon="pi pi-pencil" severity="secondary" text rounded size="small"
                                    @click="openParticularsModal(rc, data)" v-tooltip.top="`Edit`" />
                                <Button icon="pi pi-trash" severity="danger" text rounded size="small"
                                    @click="confirmDeleteParticular(rc.id, data)" v-tooltip.top="`Delete`" />
                            </div>
                        </template>
                    </Column>
                </DataTable>

                <div v-else class="flex flex-col items-center justify-center py-8 text-gray-400">
                    <i class="pi pi-inbox text-3xl mb-2"></i>
                    <p class="text-sm">No particulars yet</p>
                </div>

            </Panel>
        </div>

        <!-- RC Modal -->
        <RCModal v-model:show="showRCModal" :rc="editingRC" :mode="rcModalMode" @saved="fetchResponsibilityCenters" />

        <!-- Particular Modal -->
        <ParticularModal v-model:show="showParticularModal" :rc="particularRC" :particular="editingParticular"
            :mode="particularModalMode" @saved="fetchResponsibilityCenters" />

        <!-- Delete Confirm Modal -->
        <DeleteConfirmModal v-model:show="showDeleteModal" :type="deleteType" :target-name="deleteTargetName"
            :deleting="deleting" @confirm="handleDeleteConfirm" />
    </div>
</template>

<style scoped>
:deep(.p-datatable) {
    border-radius: 1.5rem;
    overflow: hidden;
    border: 1px solid var(--p-datatable-border-color);
}

:deep(.p-datatable-table-container) {
    border-radius: 0;
    overflow: hidden;
}

:deep(.p-datatable .p-datatable-tbody > tr:last-child > td:first-child) {
    border-bottom-left-radius: 0;
}

:deep(.p-datatable .p-datatable-tbody > tr:last-child > td:last-child) {
    border-bottom-right-radius: 0;
}

:deep(.p-paginator) {
    border: none;
    border-top: 1px solid var(--p-datatable-border-color);
}

:deep(.p-inputtext),
:deep(.p-select),
:deep(.p-datepicker .p-inputtext) {
    border-radius: 1rem;
}
</style>
