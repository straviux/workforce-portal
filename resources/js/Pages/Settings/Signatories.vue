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

            <!-- Document defaults: the single place signatory blocks are
                 configured. Each document type (SWA, DTR, Certification,
                 Fund) takes its signature block from its default here;
                 there are no per-report signatory pickers anymore. -->
            <div class="ios-card p-6">
                <div class="flex items-center justify-between mb-1 flex-wrap gap-2">
                    <p class="text-xs font-semibold text-surface-500 uppercase tracking-wide">
                        Document defaults
                    </p>
                    <Button v-if="canManageSignatories" label="Save defaults" class="rounded" size="small"
                        :loading="defaultsSaving" @click="saveDefaults"><template #icon><AppIcon name="Save" /></template></Button>
                </div>
                <p class="text-sm text-surface-400 mb-4">
                    These defaults print on every new document of that type. Change them here instead of
                    per document.
                </p>

                <div class="space-y-4">
                    <div v-for="docType in defaultTypes" :key="docType.key"
                        class="rounded-2xl border border-surface-200 dark:border-surface-800 p-4">
                        <p class="text-sm font-medium text-surface-800 dark:text-surface-100 mb-3">
                            {{ docType.label }}
                        </p>

                        <p v-if="!savedDefaults[docType.key]"
                            class="text-xs text-surface-400 mb-3">
                            No default saved yet. Reports will use the first office head until a default is saved.
                        </p>
                        <p v-else-if="savedDefaults[docType.key]?.office_head_signatory_id && !officeHeads.some((h) => h.id === savedDefaults[docType.key].office_head_signatory_id)"
                            class="text-xs text-amber-700 dark:text-amber-200 mb-3">
                            The saved default's office head no longer exists. Choose another office head below and save.
                        </p>

                        <div class="space-y-4">
                            <div class="ios-form-group">
                                <label class="ios-label">Default office head</label>
                                <Select v-model="defaultsForm[docType.key].office_head_signatory_id"
                                    :options="officeHeads" optionLabel="name" optionValue="id"
                                    placeholder="Select office head" class="w-full" showClear
                                    :disabled="!canManageSignatories"
                                    @change="onDefaultHeadChange(docType.key)" />
                            </div>

                            <template v-if="defaultsForm[docType.key].office_head_signatory_id">
                                <div>
                                    <p class="ios-label mb-2">Titles / Designations</p>
                                    <div class="space-y-2">
                                        <div v-for="(title, idx) in defaultsForm[docType.key].orderedTitles" :key="title"
                                            class="flex items-center gap-2 rounded-2xl border border-surface-200 dark:border-surface-700 px-3 py-2">
                                            <Checkbox :modelValue="defaultsForm[docType.key].selectedTitles.includes(title)"
                                                :binary="true" size="small" severity="success"
                                                :disabled="!canManageSignatories"
                                                @update:modelValue="(value) => setDefaultTitleSelected(docType.key, title, value)" />
                                            <span class="min-w-0 flex-1 text-sm text-surface-800 dark:text-surface-100">{{ title }}</span>
                                            <Button severity="secondary" text rounded size="small"
                                                :disabled="!canManageSignatories || idx === 0"
                                                @click="moveDefaultTitle(docType.key, idx, -1)"><template #icon><AppIcon name="ChevronUp" /></template></Button>
                                            <Button severity="secondary" text rounded size="small"
                                                :disabled="!canManageSignatories || idx === defaultsForm[docType.key].orderedTitles.length - 1"
                                                @click="moveDefaultTitle(docType.key, idx, 1)"><template #icon><AppIcon name="ChevronDown" /></template></Button>
                                        </div>
                                    </div>
                                    <p class="text-xs text-surface-400 mt-2">
                                        Check the titles to print, and use the arrows to set their order.
                                    </p>
                                </div>

                                <div class="space-y-3">
                                    <div class="flex items-start justify-between gap-4">
                                        <div>
                                            <p class="text-sm font-medium text-surface-800 dark:text-surface-100">Underline</p>
                                            <p class="text-xs text-surface-500 mt-1">Show an underline below the signatory name.</p>
                                        </div>
                                        <ToggleSwitch v-model="defaultsForm[docType.key].signatory_name_underline"
                                            :disabled="!canManageSignatories" />
                                    </div>
                                    <div class="flex items-start justify-between gap-4">
                                        <div>
                                            <p class="text-sm font-medium text-surface-800 dark:text-surface-100">Show designation</p>
                                            <p class="text-xs text-surface-500 mt-1">Print the titles above under the name.</p>
                                        </div>
                                        <ToggleSwitch v-model="defaultsForm[docType.key].signatory_show_designation"
                                            :disabled="!canManageSignatories" />
                                    </div>
                                    <div class="flex items-start justify-between gap-4">
                                        <div>
                                            <p class="text-sm font-medium text-surface-800 dark:text-surface-100">Show office</p>
                                            <p class="text-xs text-surface-500 mt-1">Print the office under the name.</p>
                                        </div>
                                        <ToggleSwitch v-model="defaultsForm[docType.key].signatory_show_office"
                                            :disabled="!canManageSignatories" />
                                    </div>
                                    <div class="ios-form-group">
                                        <label class="ios-label">Information order</label>
                                        <Select v-model="defaultsForm[docType.key].signatory_info_order"
                                            :options="infoOrderOptions" optionLabel="label" optionValue="value"
                                            class="w-full" :disabled="!canManageSignatories" />
                                    </div>
                                </div>

                                <div class="rounded-2xl bg-surface-50 dark:bg-surface-800 p-3 space-y-1 text-sm">
                                    <p class="font-medium text-surface-800 dark:text-surface-100"
                                        :style="defaultsForm[docType.key].signatory_name_underline
                                            ? 'display:inline-block;padding-bottom:2px;border-bottom:1px solid currentColor;'
                                            : ''">
                                        {{ defaultHeadFor(docType.key)?.name || 'No signatory selected' }}
                                    </p>
                                    <p v-for="line in defaultPreviewLines(docType.key)" :key="line" class="text-surface-500">{{ line }}</p>
                                    <p v-if="!defaultPreviewLines(docType.key).length" class="text-surface-400">
                                        Only the signatory name will be printed.
                                    </p>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
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
                <Button :label="ohModal.editId ? `Save` : `Add`"
                    class="rounded" :loading="ohModal.saving" :disabled="!ohForm.name.trim()" @click="submitOhModal"><template #icon><AppIcon :name="ohModal.editId ? 'Check' : 'Plus'" /></template></Button>
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
                <Button label="Save" class="rounded" :loading="bcdModal.saving"
                    :disabled="!bcdForm_modal.name.trim()" @click="submitBcdModal"><template #icon><AppIcon name="Check" /></template></Button>
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
import { SIGNATORY_DEFAULT_TYPES, fetchSignatoryDefaults, toTitleList } from '@/Composables/signatoryDefaults';

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
        await load(true);
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
        await load(true);
    } catch (err) {
        const msg = err.response?.data?.message ?? 'Could not update signatory.';
        toast.add({ severity: 'error', summary: 'Error', detail: msg, life: 3500 });
    } finally {
        bcdModal.saving = false;
    }
}

// ── Document defaults (central signatory config) ──
const defaultTypes = SIGNATORY_DEFAULT_TYPES;
const infoOrderOptions = [
    { label: 'Designation first', value: 'designation_first' },
    { label: 'Office first', value: 'office_first' },
];
const savedDefaults = ref({ swa: null, dtr: null, certification: null, fund: null });
const defaultsSaving = ref(false);

function emptyDefaultForm(underline = false) {
    return {
        office_head_signatory_id: null,
        orderedTitles: [],
        selectedTitles: [],
        signatory_name_underline: underline,
        signatory_show_designation: true,
        signatory_show_office: true,
        signatory_info_order: 'designation_first',
    };
}

// Fresh-form underline mirrors each type's previous workspace default.
const defaultsForm = reactive({
    swa: emptyDefaultForm(true),
    dtr: emptyDefaultForm(true),
    certification: emptyDefaultForm(false),
    fund: emptyDefaultForm(false),
});

function defaultHeadFor(type) {
    return officeHeads.value.find((head) => head.id === defaultsForm[type].office_head_signatory_id) ?? null;
}

function applyDefaultToForm(type, def) {
    const form = defaultsForm[type];
    const head = def?.office_head_signatory_id
        ? officeHeads.value.find((entry) => entry.id === def.office_head_signatory_id) ?? null
        : null;

    if (!head) {
        Object.assign(form, emptyDefaultForm(type === 'swa' || type === 'dtr'));
        return;
    }

    const headTitles = [...(head.titles ?? [])];
    const selected = Array.isArray(def.signatory_titles)
        ? toTitleList(def.signatory_titles).filter((title) => headTitles.includes(title))
        : [...headTitles];
    const selectedSet = new Set(selected);

    form.office_head_signatory_id = head.id;
    form.orderedTitles = [...selected, ...headTitles.filter((title) => !selectedSet.has(title))];
    form.selectedTitles = [...selected];
    form.signatory_name_underline = def.signatory_name_underline === true;
    form.signatory_show_designation = def.signatory_show_designation !== false;
    form.signatory_show_office = def.signatory_show_office !== false;
    form.signatory_info_order = def.signatory_info_order === 'office_first' ? 'office_first' : 'designation_first';
}

function onDefaultHeadChange(type) {
    const form = defaultsForm[type];
    const head = defaultHeadFor(type);

    if (!head) {
        form.orderedTitles = [];
        form.selectedTitles = [];
        return;
    }

    form.orderedTitles = [...(head.titles ?? [])];
    form.selectedTitles = [...(head.titles ?? [])];
}

function setDefaultTitleSelected(type, title, selected) {
    const form = defaultsForm[type];
    const selectedSet = new Set(form.selectedTitles);

    if (selected) selectedSet.add(title);
    else selectedSet.delete(title);

    form.selectedTitles = form.orderedTitles.filter((value) => selectedSet.has(value));
}

function moveDefaultTitle(type, index, direction) {
    const form = defaultsForm[type];
    const target = index + direction;

    if (target < 0 || target >= form.orderedTitles.length) return;

    const reordered = [...form.orderedTitles];
    const [moved] = reordered.splice(index, 1);
    reordered.splice(target, 0, moved);
    form.orderedTitles = reordered;
    form.selectedTitles = reordered.filter((title) => form.selectedTitles.includes(title));
}

function defaultPreviewLines(type) {
    const form = defaultsForm[type];
    const head = defaultHeadFor(type);

    if (!head) return [];

    const designationLines = form.signatory_show_designation ? form.selectedTitles : [];
    const officeLines = form.signatory_show_office && head.office ? [head.office] : [];
    const seen = new Set();

    return (form.signatory_info_order === 'office_first'
        ? [...officeLines, ...designationLines]
        : [...designationLines, ...officeLines])
        .map((line) => String(line ?? '').trim())
        .filter((line) => {
            if (!line) return false;
            const key = line.toLowerCase();
            if (seen.has(key)) return false;
            seen.add(key);
            return true;
        });
}

async function loadDefaults(silent = false) {
    try {
        const defaults = await fetchSignatoryDefaults();
        savedDefaults.value = defaults;
        for (const docType of defaultTypes) {
            applyDefaultToForm(docType.key, defaults[docType.key]);
        }
    } catch {
        if (!silent) {
            toast.add({ severity: 'error', summary: 'Error', detail: 'Could not load document defaults.', life: 3000 });
        }
    }
}

async function saveDefaults() {
    defaultsSaving.value = true;

    const defaults = {};
    for (const docType of defaultTypes) {
        const form = defaultsForm[docType.key];
        const head = defaultHeadFor(docType.key);
        defaults[docType.key] = head
            ? {
                office_head_signatory_id: head.id,
                signatory_titles: form.orderedTitles.filter((title) => form.selectedTitles.includes(title)),
                signatory_name_underline: form.signatory_name_underline,
                signatory_show_designation: form.signatory_show_designation,
                signatory_show_office: form.signatory_show_office,
                signatory_info_order: form.signatory_info_order,
            }
            : {
                office_head_signatory_id: null,
                signatory_titles: null,
                signatory_name_underline: form.signatory_name_underline,
                signatory_show_designation: form.signatory_show_designation,
                signatory_show_office: form.signatory_show_office,
                signatory_info_order: form.signatory_info_order,
            };
    }

    try {
        await axios.put('/api/signatory-defaults', { defaults });
        toast.add({ severity: 'success', summary: 'Saved', detail: 'Document defaults saved.', life: 3000 });
        await loadDefaults(true);
    } catch (err) {
        const msg = err.response?.data?.message ?? 'Could not save document defaults.';
        toast.add({ severity: 'error', summary: 'Error', detail: msg, life: 3500 });
    } finally {
        defaultsSaving.value = false;
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
        await loadDefaults(silent);
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
