<template>
    <WorkforceLayout>

        <Head title="Signatories" />

        <!-- Page Header -->
        <div class="flex items-center justify-between mb-5">
            <div>
                <h1 class="text-xl font-bold text-surface-800 dark:text-surface-50">Signatories</h1>
                <p class="text-sm text-surface-400 mt-0.5">Names printed in the signature sections of CWA and Payroll
                    documents.</p>
            </div>
        </div>

        <div class="space-y-4 max-w-3xl">
            <!-- Part A — Office Heads (multiple) -->
            <div class="ios-card p-6">
                <div class="flex items-center justify-between mb-4">
                    <p class="text-xs font-semibold text-surface-500 uppercase tracking-wide">
                        Part A — Office Head (Prepared by / Verified)
                    </p>
                    <Button icon="pi pi-plus" label="Add" class="rounded" size="small" @click="openAddOh" />
                </div>

                <div v-if="officeHeads.length" class="space-y-2">
                    <div v-for="oh in officeHeads" :key="oh.id"
                        class="flex items-start gap-3 p-3 rounded-2xl bg-surface-50 dark:bg-surface-800">
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-surface-800 dark:text-surface-100">{{ oh.name }}</p>
                            <p v-if="oh.office" class="text-xs text-surface-500">{{ oh.office }}</p>
                            <div v-if="oh.titles?.length" class="mt-0.5">
                                <span v-for="(t, i) in oh.titles" :key="i" class="block text-xs text-surface-400">{{ t
                                    }}</span>
                            </div>
                        </div>
                        <Button icon="pi pi-pencil" severity="secondary" text rounded size="small"
                            @click="openEditOh(oh)" />
                        <Button icon="pi pi-trash" severity="danger" text rounded size="small"
                            :loading="deletingId === oh.id" @click="removeOfficeHead(oh)" />
                    </div>
                </div>
                <p v-else class="text-sm text-surface-400">No office heads added yet.</p>
            </div>

            <!-- Parts B / C / D -->
            <div class="ios-card p-6">
                <div class="space-y-4">
                    <div v-for="row in bcdForm" :key="row.part"
                        class="flex items-start gap-3 p-3 rounded-2xl bg-surface-50 dark:bg-surface-800">
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-semibold text-surface-500 uppercase tracking-wide mb-1">
                                {{ partLabels[row.part] }}
                            </p>
                            <p class="text-sm font-medium text-surface-800 dark:text-surface-100">
                                {{ row.name || `—` }}
                            </p>
                            <p v-if="row.office" class="text-xs text-surface-500">{{ row.office }}</p>
                            <div v-if="row.titles?.length" class="mt-0.5">
                                <span v-for="(t, i) in row.titles" :key="i" class="block text-xs text-surface-400">{{ t
                                    }}</span>
                            </div>
                        </div>
                        <Button icon="pi pi-pencil" severity="secondary" text rounded size="small"
                            @click="openEditPart(row)" />
                    </div>
                </div>
            </div>
        </div>

        <!-- Office Head Modal (Add & Edit) -->
        <Dialog v-model:visible="ohModal.show" modal :header="ohModal.editId ? `Edit Office Head` : `Add Office Head`"
            :style="{ width: '32rem' }" :draggable="false">
            <div class="space-y-4 pt-1">
                <div class="grid grid-cols-2 gap-3">
                    <div class="ios-form-group">
                        <label class="ios-label">Full Name <span class="text-red-500">*</span></label>
                        <InputText v-model="ohForm.name" placeholder="e.g. Juan dela Cruz" class="w-full"
                            size="small" />
                    </div>
                    <div class="ios-form-group">
                        <label class="ios-label">Office</label>
                        <InputText v-model="ohForm.office" placeholder="e.g. HRMO" class="w-full" size="small" />
                    </div>
                </div>
                <div class="ios-form-group">
                    <label class="ios-label">Titles / Designations</label>
                    <div class="space-y-2">
                        <div v-for="(t, i) in ohForm.titles" :key="i" class="flex gap-2 items-center">
                            <InputText v-model="ohForm.titles[i]" placeholder="e.g. Political Affairs Officer III"
                                class="flex-1" size="small" />
                            <Button icon="pi pi-times" severity="danger" text rounded size="small"
                                :disabled="ohForm.titles.length === 1" @click="ohForm.titles.splice(i, 1)" />
                        </div>
                        <Button icon="pi pi-plus" label="Add title" text size="small" class="rounded !pl-0"
                            @click="ohForm.titles.push('')" />
                    </div>
                </div>
            </div>
            <template #footer>
                <Button label="Cancel" severity="secondary" text class="rounded" @click="ohModal.show = false" />
                <Button :label="ohModal.editId ? `Save` : `Add`" :icon="ohModal.editId ? `pi pi-check` : `pi pi-plus`"
                    class="rounded" :loading="ohModal.saving" :disabled="!ohForm.name.trim()" @click="submitOhModal" />
            </template>
        </Dialog>

        <!-- BCD Part Modal -->
        <Dialog v-model:visible="bcdModal.show" modal
            :header="bcdModal.part ? `Edit — ${partLabels[bcdModal.part]}` : ``" :style="{ width: '32rem' }"
            :draggable="false">
            <div class="space-y-4 pt-1">
                <div class="grid grid-cols-2 gap-3">
                    <div class="ios-form-group">
                        <label class="ios-label">Full Name <span class="text-red-500">*</span></label>
                        <InputText v-model="bcdForm_modal.name"
                            :placeholder="`e.g. ${partPlaceholders[bcdModal.part]?.name}`" class="w-full"
                            size="small" />
                    </div>
                    <div class="ios-form-group">
                        <label class="ios-label">Office</label>
                        <InputText v-model="bcdForm_modal.office"
                            :placeholder="`e.g. ${partPlaceholders[bcdModal.part]?.office}`" class="w-full"
                            size="small" />
                    </div>
                </div>
                <div class="ios-form-group">
                    <label class="ios-label">Titles / Designations</label>
                    <div class="space-y-2">
                        <div v-for="(t, i) in bcdForm_modal.titles" :key="i" class="flex gap-2 items-center">
                            <InputText v-model="bcdForm_modal.titles[i]"
                                :placeholder="`e.g. ${partPlaceholders[bcdModal.part]?.title}`" class="flex-1"
                                size="small" />
                            <Button icon="pi pi-times" severity="danger" text rounded size="small"
                                :disabled="bcdForm_modal.titles.length === 1"
                                @click="bcdForm_modal.titles.splice(i, 1)" />
                        </div>
                        <Button icon="pi pi-plus" label="Add title" text size="small" class="rounded !pl-0"
                            @click="bcdForm_modal.titles.push('')" />
                    </div>
                </div>
            </div>
            <template #footer>
                <Button label="Cancel" severity="secondary" text class="rounded" @click="bcdModal.show = false" />
                <Button label="Save" icon="pi pi-check" class="rounded" :loading="bcdModal.saving"
                    :disabled="!bcdForm_modal.name.trim()" @click="submitBcdModal" />
            </template>
        </Dialog>

    </WorkforceLayout>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import { Head } from '@inertiajs/vue3';
import { useToast } from 'primevue/usetoast';
import axios from 'axios';
import WorkforceLayout from '@/Layouts/WorkforceLayout.vue';

const toast = useToast();
const deletingId = ref(null);

const partLabels = {
    B: 'Part B — Accountant (Certified correct)',
    C: 'Part C — Treasurer (Funds available)',
    D: 'Part D — Governor (Approved for payment)',
};

const partPlaceholders = {
    B: { name: 'Maria Santos', office: 'Provincial Accounting Office', title: 'Provincial Accountant' },
    C: { name: 'Pedro Reyes', office: 'Provincial Treasury Office', title: 'Provincial Treasurer' },
    D: { name: 'Ana Garcia', office: 'Office of the Governor', title: 'Governor' },
};

function toTitles(title) {
    if (Array.isArray(title)) return title.length ? title : [''];
    if (title) return [title];
    return [''];
}

// ── Office Head modal state ──
const ohModal = reactive({ show: false, editId: null, saving: false });
const ohForm = reactive({ name: '', office: '', titles: [''] });

function openAddOh() {
    ohModal.editId = null;
    ohForm.name = '';
    ohForm.office = '';
    ohForm.titles = [''];
    ohModal.show = true;
}

function openEditOh(oh) {
    ohModal.editId = oh.id;
    ohForm.name = oh.name;
    ohForm.office = oh.office ?? '';
    ohForm.titles = [...oh.titles];
    ohModal.show = true;
}

async function submitOhModal() {
    ohModal.saving = true;
    const payload = {
        name: ohForm.name,
        office: ohForm.office,
        title: ohForm.titles.filter((t) => t.trim()),
    };
    try {
        if (ohModal.editId) {
            const res = await axios.patch(`/api/signatories/office-heads/${ohModal.editId}`, payload);
            const idx = officeHeads.value.findIndex((r) => r.id === ohModal.editId);
            if (idx !== -1) officeHeads.value[idx] = { ...res.data.data, titles: toTitles(res.data.data.title) };
            toast.add({ severity: 'success', summary: 'Saved', detail: 'Office head updated.', life: 3000 });
        } else {
            const res = await axios.post('/api/signatories/office-heads', payload);
            officeHeads.value.push({ ...res.data.data, titles: toTitles(res.data.data.title) });
            toast.add({ severity: 'success', summary: 'Added', detail: 'Office head added.', life: 3000 });
        }
        ohModal.show = false;
    } catch (err) {
        const msg = err.response?.data?.message ?? 'Could not save office head.';
        toast.add({ severity: 'error', summary: 'Error', detail: msg, life: 3500 });
    } finally {
        ohModal.saving = false;
    }
}

// ── BCD modal state ──
const bcdModal = reactive({ show: false, part: null, saving: false });
const bcdForm_modal = reactive({ name: '', office: '', titles: [''] });

function openEditPart(row) {
    bcdModal.part = row.part;
    bcdForm_modal.name = row.name;
    bcdForm_modal.office = row.office ?? '';
    bcdForm_modal.titles = [...row.titles];
    bcdModal.show = true;
}

async function submitBcdModal() {
    bcdModal.saving = true;
    const payload = {
        signatories: [{
            part: bcdModal.part,
            name: bcdForm_modal.name,
            office: bcdForm_modal.office,
            title: bcdForm_modal.titles.filter((t) => t.trim()),
        }],
    };
    try {
        await axios.post('/api/signatories', payload);
        const idx = bcdForm.value.findIndex((r) => r.part === bcdModal.part);
        if (idx !== -1) {
            bcdForm.value[idx] = {
                part: bcdModal.part,
                name: bcdForm_modal.name,
                office: bcdForm_modal.office,
                titles: bcdForm_modal.titles.filter((t) => t.trim()).length
                    ? [...bcdForm_modal.titles]
                    : [''],
            };
        }
        bcdModal.show = false;
        toast.add({ severity: 'success', summary: 'Saved', detail: 'Signatory updated.', life: 3000 });
    } catch (err) {
        const msg = err.response?.data?.message ?? 'Could not update signatory.';
        toast.add({ severity: 'error', summary: 'Error', detail: msg, life: 3500 });
    } finally {
        bcdModal.saving = false;
    }
}

// ── Data ──
const officeHeads = ref([]);
const bcdForm = ref([
    { part: 'B', name: '', office: '', titles: [''] },
    { part: 'C', name: '', office: '', titles: [''] },
    { part: 'D', name: '', office: '', titles: [''] },
]);

async function load() {
    try {
        const res = await axios.get('/api/signatories');
        const data = res.data.data ?? [];
        officeHeads.value = data
            .filter((r) => r.part === 'A')
            .map((r) => ({ ...r, titles: toTitles(r.title) }));
        bcdForm.value = ['B', 'C', 'D'].map((part) => {
            const found = data.find((r) => r.part === part);
            return {
                part,
                name: found?.name ?? '',
                office: found?.office ?? '',
                titles: toTitles(found?.title),
            };
        });
    } catch {
        toast.add({ severity: 'error', summary: 'Error', detail: 'Could not load signatories.', life: 3000 });
    }
}

async function removeOfficeHead(oh) {
    deletingId.value = oh.id;
    try {
        await axios.delete(`/api/signatories/office-heads/${oh.id}`);
        officeHeads.value = officeHeads.value.filter((r) => r.id !== oh.id);
        toast.add({ severity: 'success', summary: 'Removed', detail: 'Office head removed.', life: 3000 });
    } catch {
        toast.add({ severity: 'error', summary: 'Error', detail: 'Could not remove office head.', life: 3000 });
    } finally {
        deletingId.value = null;
    }
}

onMounted(load);
</script>
