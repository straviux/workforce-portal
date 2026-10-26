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
            <!-- Completeness indicator: fund documents cannot print until
                 every part is configured (see the print gate in
                 EmployeeFundTransactions/index.vue). -->
            <div v-if="configuration && !configuration.complete"
                class="ios-card p-4 border border-amber-200 bg-amber-50 dark:border-amber-400/20 dark:bg-amber-950/20">
                <p class="text-sm font-medium text-amber-900 dark:text-amber-100">Signatory configuration incomplete</p>
                <p class="text-sm text-amber-800/80 dark:text-amber-100/80 mt-1">
                    {{ configurationMessage }}
                </p>
            </div>
            <div v-else-if="configuration?.complete"
                class="ios-card p-4 border border-emerald-200 bg-emerald-50 dark:border-emerald-500/20 dark:bg-emerald-950/20">
                <p class="text-sm text-emerald-900 dark:text-emerald-100">Signatory configuration complete. Fund documents can be printed.</p>
            </div>

            <!-- Office Heads (multiple) -->
            <div class="ios-card p-6">
                <div class="flex items-center justify-between mb-4">
                    <p class="text-xs font-semibold text-surface-500 uppercase tracking-wide">
                        {{ partLabels.A }}
                    </p>
                    <Button v-if="canManageSignatories" label="Add" class="rounded" size="small"
                        @click="openAddOh"><template #icon><AppIcon name="Plus" /></template></Button>
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
                        <Button v-if="canManageSignatories" severity="secondary" text rounded size="small"
                            :loading="deletingId === oh.id" @click="(e) => openOhMenu(e, oh)"><template #icon><AppIcon name="EllipsisVertical" /></template></Button>
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
                        <Button v-if="canManageSignatories" severity="secondary" text rounded size="small"
                            @click="openEditPart(row)"><template #icon><AppIcon name="Pencil" /></template></Button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Office-head row menu -->
        <ContextMenu ref="ohMenuRef" :model="ohMenuItems">
            <template #item="{ item, label, props }">
                <a v-bind="props.action">
                    <AppIcon v-if="item.iconName" :name="item.iconName" :size="16" class="mr-2 shrink-0" />
                    <span v-bind="props.label">{{ label }}</span>
                </a>
            </template>
        </ContextMenu>

        <!-- Office Head Modal (Add & Edit) -->
        <Dialog v-model:visible="ohModal.show" modal :header="ohModal.editId ? `Edit Office Head` : `Add Office Head`"
            :style="{ width: '32rem' }" :draggable="false">
            <div class="space-y-4 pt-1">
                <div class="grid grid-cols-2 gap-3">
                    <div class="ios-form-group">
                        <label class="ios-label">Full Name <span class="text-red-500">*</span></label>
                        <InputText v-model="ohForm.name" placeholder="Enter full name" class="w-full"
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
                            <Button severity="danger" text rounded size="small"
                                :disabled="ohForm.titles.length === 1" @click="ohForm.titles.splice(i, 1)"><template #icon><AppIcon name="X" /></template></Button>
                        </div>
                        <Button label="Add title" text size="small" class="rounded !pl-0"
                            @click="ohForm.titles.push('')"><template #icon><AppIcon name="Plus" /></template></Button>
                    </div>
                </div>
            </div>
            <template #footer>
                <Button label="Cancel" severity="secondary" text class="rounded" @click="ohModal.show = false" />
                <Button :label="ohModalAction.label"
                    class="rounded" :loading="ohModalAction.loading" :disabled="ohModalAction.disabled || !ohForm.name.trim()"
                    :class="ohModalAction.morphClass" @click="submitOhModal"><template #icon><AppIcon v-if="ohModalAction.state === 'success'" name="Check" /><AppIcon v-else :name="ohModal.editId ? 'Check' : 'Plus'" /></template></Button>
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
                            placeholder="Enter full name" class="w-full"
                            size="small" />
                    </div>
                    <div class="ios-form-group">
                        <label class="ios-label">Office</label>
                        <InputText v-model="bcdForm_modal.office"
                            placeholder="Enter office" class="w-full"
                            size="small" />
                    </div>
                </div>
                <div class="ios-form-group">
                    <label class="ios-label">Titles / Designations</label>
                    <div class="space-y-2">
                        <div v-for="(t, i) in bcdForm_modal.titles" :key="i" class="flex gap-2 items-center">
                            <InputText v-model="bcdForm_modal.titles[i]"
                                placeholder="Enter title or designation" class="flex-1"
                                size="small" />
                            <Button severity="danger" text rounded size="small"
                                :disabled="bcdForm_modal.titles.length === 1"
                                @click="bcdForm_modal.titles.splice(i, 1)"><template #icon><AppIcon name="X" /></template></Button>
                        </div>
                        <Button label="Add title" text size="small" class="rounded !pl-0"
                            @click="bcdForm_modal.titles.push('')"><template #icon><AppIcon name="Plus" /></template></Button>
                    </div>
                </div>
            </div>
            <template #footer>
                <Button label="Cancel" severity="secondary" text class="rounded" @click="bcdModal.show = false" />
                <Button :label="bcdAction.label" class="rounded" :loading="bcdAction.loading"
                    :disabled="bcdAction.disabled || !bcdForm_modal.name.trim()" :class="bcdAction.morphClass"
                    @click="submitBcdModal"><template #icon><AppIcon name="Check" /></template></Button>
            </template>
        </Dialog>

    </WorkforceLayout>
</template>

<script setup>
import { computed, ref, reactive, onMounted } from 'vue';
import { Head, usePage } from '@inertiajs/vue3';
import { useToast } from 'primevue/usetoast';
import ContextMenu from 'primevue/contextmenu';
import axios from 'axios';
import WorkforceLayout from '@/Layouts/WorkforceLayout.vue';
import { signatoryConfigurationStatus } from '@/Composables/signatorySnapshot';
import { useActionButton } from '@/Composables/useActionButton';

const toast = useToast();
const page = usePage();
const deletingId = ref(null);
const userPermissions = computed(() => page.props.auth?.user?.permissions ?? []);
const canManageSignatories = computed(() => userPermissions.value.includes('signatories.manage'));

// ── Office-head row menu (kebab, same pattern as Employees/index.vue) ──
const ohMenuRef = ref(null);
const ohMenuItems = ref([]);

function setOhMenuItems(oh) {
    ohMenuItems.value = [
        { label: 'Edit', iconName: 'Pencil', command: () => openEditOh(oh) },
        { separator: true },
        { label: 'Delete', iconName: 'Trash2', class: 'text-red-500', command: () => removeOfficeHead(oh) },
    ];
}

function openOhMenu(event, oh) {
    setOhMenuItems(oh);
    if (typeof ohMenuRef.value?.show === 'function') {
        ohMenuRef.value.show(event);
        return;
    }

    ohMenuRef.value?.toggle(event);
}

// Part labels come from the backend (Signatory::$partLabels via the
// signatories API response) — the single source of truth, never hardcoded
// here. Empty until the first load succeeds.
const partLabels = ref({});
const configuration = ref(null);
const configurationMessage = computed(() => {
    const missing = configuration.value?.missing ?? [];
    if (!missing.length) return '';
    const names = missing.map((part) => partLabels.value[part] ?? `Part ${part}`);
    return `Fund documents cannot be printed until each of these is configured: ${names.join('; ')}.`;
});

function toTitles(title) {
    if (Array.isArray(title)) return title.length ? title : [''];
    if (title) return [title];
    return [''];
}

// ── Office Head modal state ──
const ohModal = reactive({ show: false, editId: null });
const ohForm = reactive({ name: '', office: '', titles: [''] });
// The footer button's verb flips with the mode, so each mode gets its
// own morph action and the live one is the mode open at click time
// (the mode never changes mid-flight, and the modal only closes after
// the full run() cycle, so the success label is actually seen).
const ohAddAction = useActionButton({
    idleLabel: 'Add',
    processingLabel: 'Adding...',
    successLabel: 'Added',
});
const ohEditAction = useActionButton({
    idleLabel: 'Save',
    processingLabel: 'Saving...',
    successLabel: 'Saved',
});
const ohModalAction = computed(() => (ohModal.editId ? ohEditAction : ohAddAction));

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
    const payload = {
        name: ohForm.name,
        office: ohForm.office,
        title: ohForm.titles.filter((t) => t.trim()),
    };
    try {
        await ohModalAction.value.run(async () => {
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
        });

        // After the full cycle only: closing here keeps "Saved"/"Added"
        // visible in the footer instead of vanishing with the modal.
        ohModal.show = false;
        await load(true);
    } catch (err) {
        const msg = err.response?.data?.message ?? 'Could not save office head.';
        toast.add({ severity: 'error', summary: 'Error', detail: msg, life: 3500 });
    }
}

// ── BCD modal state ──
const bcdModal = reactive({ show: false, part: null });
const bcdForm_modal = reactive({ name: '', office: '', titles: [''] });
const bcdAction = useActionButton({
    idleLabel: 'Save',
    processingLabel: 'Saving...',
    successLabel: 'Saved',
});

function openEditPart(row) {
    bcdModal.part = row.part;
    bcdForm_modal.name = row.name;
    bcdForm_modal.office = row.office ?? '';
    bcdForm_modal.titles = [...row.titles];
    bcdModal.show = true;
}

async function submitBcdModal() {
    const payload = {
        signatories: [{
            part: bcdModal.part,
            name: bcdForm_modal.name,
            office: bcdForm_modal.office,
            title: bcdForm_modal.titles.filter((t) => t.trim()),
        }],
    };
    try {
        await bcdAction.run(async () => {
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
            toast.add({ severity: 'success', summary: 'Saved', detail: 'Signatory updated.', life: 3000 });
        });

        // After the full cycle only: closing here keeps "Saved"
        // visible in the footer instead of vanishing with the modal.
        bcdModal.show = false;
        await load(true);
    } catch (err) {
        const msg = err.response?.data?.message ?? 'Could not update signatory.';
        toast.add({ severity: 'error', summary: 'Error', detail: msg, life: 3500 });
    }
}

// ── Data ──
const officeHeads = ref([]);
const bcdForm = ref([
    { part: 'B', name: '', office: '', titles: [''] },
    { part: 'C', name: '', office: '', titles: [''] },
    { part: 'D', name: '', office: '', titles: [''] },
]);

async function load(silent = false) {
    try {
        const res = await axios.get('/api/signatories');
        const data = res.data.data ?? [];
        if (res.data.part_labels) partLabels.value = res.data.part_labels;
        configuration.value = res.data.configuration ?? signatoryConfigurationStatus(data);
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
        if (!silent) {
            toast.add({ severity: 'error', summary: 'Error', detail: 'Could not load signatories.', life: 3000 });
        }
    }
}

async function removeOfficeHead(oh) {
    deletingId.value = oh.id;
    try {
        await axios.delete(`/api/signatories/office-heads/${oh.id}`);
        officeHeads.value = officeHeads.value.filter((r) => r.id !== oh.id);
        toast.add({ severity: 'success', summary: 'Removed', detail: 'Office head removed.', life: 3000 });
        await load(true);
    } catch (err) {
        // The backend refuses (422) when documents still reference this
        // office head; surface that reason instead of a generic failure.
        const msg = err.response?.data?.message ?? 'Could not remove office head.';
        toast.add({ severity: 'error', summary: 'Cannot remove', detail: msg, life: 5000 });
    } finally {
        deletingId.value = null;
    }
}

onMounted(load);
</script>
