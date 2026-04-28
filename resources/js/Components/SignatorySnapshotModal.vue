<template>
    <Dialog :visible="show" @update:visible="val => emit('update:show', val)" modal
        :pt="{ root: { class: 'ios-dialog-root' }, mask: { class: 'ios-dialog-mask' } }">
        <template #container>
            <div ref="elModal" class="ios-modal w-140 max-w-[94vw]" :style="modalStyle">

                <div class="ios-nav-bar" @pointerdown="onDragStart">
                    <button class="ios-nav-btn ios-nav-cancel" @click="emit('update:show', false)">
                        <i class="pi pi-times"></i>
                    </button>
                    <span class="ios-nav-title">{{ modalTitle }}</span>
                    <button class="ios-nav-btn ios-nav-action" :disabled="confirmDisabled" @click="applySnapshot">
                        {{ confirmLabel }}
                    </button>
                </div>

                <div class="ios-body">
                    <p v-if="introText" class="text-sm text-surface-500 mb-4">{{ introText }}</p>

                    <div v-if="!normalizedOfficeHeads.length" class="ios-section pb-4">
                        <div
                            class="ios-card p-4 border border-amber-200 bg-amber-50 dark:border-amber-400/20 dark:bg-amber-950/20">
                            <p class="text-sm font-medium text-amber-900 dark:text-amber-100">{{ emptyStateTitle }}</p>
                            <p class="text-sm text-amber-800/80 dark:text-amber-100/80 mt-1">
                                {{ emptyStateMessage }}
                            </p>
                        </div>
                    </div>

                    <template v-else>
                        <div class="ios-section pb-4">
                            <p class="ios-section-label">{{ selectionSectionTitle }}</p>
                            <div class="ios-card p-4 space-y-3">
                                <div class="ios-form-group">
                                    <label class="ios-label">{{ selectionLabel }} <span
                                            class="text-red-500">*</span></label>
                                    <Select v-model="draft.office_head_id" :options="normalizedOfficeHeads"
                                        optionLabel="name" optionValue="id" :placeholder="selectionPlaceholder"
                                        class="w-full" />
                                </div>
                            </div>
                        </div>

                        <div v-if="allowTitleSelection && availableOfficeHeadTitles.length" class="ios-section pb-4">
                            <p class="ios-section-label">{{ titleSectionTitle }}</p>
                            <div class="ios-card p-4">
                                <div class="space-y-2">
                                    <div v-for="(title, idx) in orderedTitleOptions" :key="title"
                                        class="flex items-center gap-3 rounded-2xl border px-3 py-2 transition"
                                        :class="draggedTitleIndex === idx
                                            ? 'border-sky-300 bg-sky-50/70 dark:border-sky-500/40 dark:bg-sky-950/20 opacity-70'
                                            : dragOverTitleIndex === idx
                                                ? 'border-sky-400 bg-sky-50 dark:border-sky-500/50 dark:bg-sky-950/20'
                                                : isOfficeHeadTitleSelected(title)
                                                    ? 'border-emerald-200  dark:border-emerald-500/30 '
                                                    : 'border-surface-200 bg-white dark:border-surface-700 dark:bg-surface-900'" :draggable="orderedTitleOptions.length > 1"
                                        @dragstart="onTitleDragStart(idx, $event)" @dragend="onTitleDragEnd"
                                        @dragover.prevent="onTitleDragOver(idx, $event)"
                                        @dragenter.prevent="onTitleDragOver(idx, $event)"
                                        @drop.prevent="onTitleDrop(idx, $event)">
                                        <span
                                            class="rounded-lg border border-surface-200 dark:border-surface-700 px-2 py-1 text-surface-400 transition cursor-grab active:cursor-grabbing"
                                            v-tooltip="'Grab this to change order'">
                                            <i class="pi pi-sort text-xs"></i>
                                        </span>

                                        <Checkbox :modelValue="isOfficeHeadTitleSelected(title)" :binary="true"
                                            size="small" severity="success"
                                            @update:modelValue="value => setOfficeHeadTitleSelected(title, value)"
                                            @click.stop />

                                        <div class="min-w-0 flex-1">
                                            <p class="text-sm font-medium text-surface-800 dark:text-surface-100">
                                                {{ title }}
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <p v-if="titleSelectionHint" class="text-xs text-surface-400 mt-2">
                                    {{ titleSelectionHint }}
                                </p>
                            </div>
                        </div>

                        <div v-if="allowDisplayOptions" class="ios-section pb-4">
                            <p class="ios-section-label">{{ displayOptionsSectionTitle }}</p>
                            <div class="ios-card p-4 space-y-4">
                                <div class="flex items-start justify-between gap-4">
                                    <div>
                                        <p class="text-sm font-medium text-surface-800 dark:text-surface-100">Underline
                                        </p>
                                        <p class="text-xs text-surface-500 mt-1">Show an underline below the signatory
                                            name.</p>
                                    </div>
                                    <ToggleSwitch v-model="draft.signatory_name_underline" />
                                </div>

                                <div class="space-y-2">
                                    <div v-for="(lineOption, idx) in orderedVisibleLineRows" :key="lineOption.id"
                                        class="flex items-center gap-3 rounded-2xl border px-3 py-2 transition"
                                        :class="draggedDetailIndex === idx
                                            ? 'border-sky-300 bg-sky-50/70 dark:border-sky-500/40 dark:bg-sky-950/20 opacity-70'
                                            : dragOverDetailIndex === idx
                                                ? 'border-sky-400 bg-sky-50 dark:border-sky-500/50 dark:bg-sky-950/20'
                                                : isVisibleLineSelected(lineOption.id)
                                                    ? 'border-emerald-200 dark:border-emerald-500/30'
                                                    : 'border-surface-200 bg-white dark:border-surface-700 dark:bg-surface-900'" :draggable="orderedVisibleLineRows.length > 1"
                                        @dragstart="onVisibleLineDragStart(idx, $event)" @dragend="onVisibleLineDragEnd"
                                        @dragover.prevent="onVisibleLineDragOver(idx, $event)"
                                        @dragenter.prevent="onVisibleLineDragOver(idx, $event)"
                                        @drop.prevent="onVisibleLineDrop(idx, $event)">
                                        <span
                                            class="rounded-lg border border-surface-200 dark:border-surface-700 px-2 py-1 text-surface-400 transition cursor-grab active:cursor-grabbing"
                                            v-tooltip="'Grab this to change order'">
                                            <i class="pi pi-sort text-xs"></i>
                                        </span>

                                        <Checkbox :modelValue="isVisibleLineSelected(lineOption.id)" :binary="true"
                                            size="small" severity="success"
                                            @update:modelValue="value => setVisibleLineSelected(lineOption.id, value)"
                                            @click.stop />

                                        <div class="min-w-0 flex-1">
                                            <p class="text-sm font-medium text-surface-800 dark:text-surface-100">
                                                {{ lineOption.label }}
                                            </p>
                                            <p class="mt-1 text-[11px] text-surface-400">
                                                {{ isVisibleLineSelected(lineOption.id)
                                                    ? `Selected${visibleLinePosition(lineOption.id) ? ` · Position
                                                ${visibleLinePosition(lineOption.id)}` : ''}`
                                                : 'Not shown in the preview' }}
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <p class="text-xs text-surface-400">
                                    Use the checkboxes to show or hide each line, then drag rows to control the print
                                    order.
                                </p>
                            </div>
                        </div>

                        <div v-if="showPreview" class="ios-section pb-4">
                            <p class="ios-section-label">{{ previewSectionTitle }}</p>
                            <div class="ios-card p-4 space-y-1 text-sm">
                                <p class="font-medium text-surface-800 dark:text-surface-100" :style="draft.signatory_name_underline
                                    ? 'display:inline-block;padding-bottom:2px;border-bottom:1px solid currentColor;'
                                    : ''">
                                    {{ selectedOfficeHead?.name || noSelectionLabel }}
                                </p>
                                <template v-if="selectedOfficeHead">
                                    <p v-for="line in previewLines" :key="line" class="text-surface-500">{{ line }}</p>
                                    <p v-if="!previewLines.length" class="text-surface-400">
                                        {{ previewEmptyText }}
                                    </p>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </template>
    </Dialog>
</template>

<script setup>
import { computed, reactive, ref, watch } from 'vue';

const props = defineProps({
    show: Boolean,
    officeHeads: { type: Array, default: () => [] },
    initialValue: {
        type: Object,
        default: () => ({
            office_head_id: null,
            signatory_titles: null,
            signatory_name_underline: false,
            signatory_show_designation: true,
            signatory_show_office: true,
            signatory_info_order: 'designation_first',
        }),
    },
    modalTitle: { type: String, default: 'Signatory Snapshot' },
    confirmLabel: { type: String, default: 'Save' },
    introText: { type: String, default: '' },
    emptyStateTitle: { type: String, default: 'No office head signatory configured' },
    emptyStateMessage: {
        type: String,
        default: 'Add at least one Part A office head in Signatories before configuring the snapshot.',
    },
    selectionSectionTitle: { type: String, default: 'Source' },
    selectionLabel: { type: String, default: 'Office Head Signatory' },
    selectionPlaceholder: { type: String, default: 'Select office head' },
    allowTitleSelection: { type: Boolean, default: false },
    titleSectionTitle: { type: String, default: 'Titles / Designations' },
    titleSelectionHint: { type: String, default: '' },
    allowDisplayOptions: { type: Boolean, default: false },
    displayOptionsSectionTitle: { type: String, default: 'Display Options' },
    showPreview: { type: Boolean, default: true },
    previewSectionTitle: { type: String, default: 'Preview' },
    previewEmptyText: { type: String, default: 'Only the signatory name will be printed.' },
    noSelectionLabel: { type: String, default: 'No signatory selected' },
});

const emit = defineEmits(['update:show', 'apply']);

const draft = reactive({
    office_head_id: null,
    signatory_titles: [],
    signatory_name_underline: false,
    signatory_show_designation: true,
    signatory_show_office: true,
    signatory_info_order: 'designation_first',
});

const visibleLineOptions = {
    designation: { id: 'designation', label: 'Titles / Designations' },
    office: { id: 'office', label: 'Office' },
};

const isHydrating = ref(false);
const orderedOfficeHeadTitles = ref([]);
const orderedVisibleLineOptions = ref([]);
const draggedTitleIndex = ref(null);
const dragOverTitleIndex = ref(null);
const draggedDetailIndex = ref(null);
const dragOverDetailIndex = ref(null);
const normalizedOfficeHeads = computed(() => (props.officeHeads ?? []).map(normalizeOfficeHead).filter((officeHead) => officeHead.id != null));
const selectedOfficeHead = computed(() => normalizedOfficeHeads.value.find((officeHead) => officeHead.id === draft.office_head_id) ?? null);
const availableOfficeHeadTitles = computed(() => selectedOfficeHead.value?.titles ?? []);
const orderedTitleOptions = computed(() => orderedOfficeHeadTitles.value.length
    ? orderedOfficeHeadTitles.value
    : availableOfficeHeadTitles.value);
const orderedVisibleLineRows = computed(() => orderedVisibleLineOptions.value
    .map((id) => visibleLineOptions[id])
    .filter(Boolean));
const previewLines = computed(() => buildPreviewLines(selectedOfficeHead.value, draft, props.allowTitleSelection, props.allowDisplayOptions));
const confirmDisabled = computed(() => !normalizedOfficeHeads.value.length || !draft.office_head_id);

watch(() => props.show, (visible) => {
    if (!visible) return;

    hydrateDraft();
    dragOffset.value = { x: 0, y: 0 };
}, { immediate: true });

watch(() => draft.office_head_id, (officeHeadId, previousOfficeHeadId) => {
    if (!props.allowTitleSelection || isHydrating.value || officeHeadId === previousOfficeHeadId) {
        return;
    }

    const nextTitles = officeHeadTitlesForId(officeHeadId);

    orderedOfficeHeadTitles.value = [...nextTitles];
    draft.signatory_titles = [...nextTitles];
});

watch(availableOfficeHeadTitles, (titles) => {
    if (!props.allowTitleSelection || isHydrating.value) {
        return;
    }

    orderedOfficeHeadTitles.value = buildOrderedTitleOptions(titles, draft.signatory_titles);
    syncSelectedTitlesToCurrentOrder();
}, { deep: true });

function hydrateDraft() {
    isHydrating.value = true;

    const resolvedOfficeHeadId = normalizedOfficeHeads.value.some((officeHead) => officeHead.id === props.initialValue?.office_head_id)
        ? props.initialValue?.office_head_id
        : normalizedOfficeHeads.value[0]?.id ?? null;
    const availableTitles = officeHeadTitlesForId(resolvedOfficeHeadId);

    draft.office_head_id = resolvedOfficeHeadId;
    draft.signatory_titles = props.allowTitleSelection
        ? sanitizeSelectedTitles(props.initialValue?.signatory_titles, availableTitles)
        : [...availableTitles];
    orderedOfficeHeadTitles.value = buildOrderedTitleOptions(availableTitles, draft.signatory_titles);
    draft.signatory_name_underline = props.initialValue?.signatory_name_underline === true;
    draft.signatory_show_designation = props.initialValue?.signatory_show_designation ?? true;
    draft.signatory_show_office = props.initialValue?.signatory_show_office ?? true;
    draft.signatory_info_order = props.initialValue?.signatory_info_order === 'office_first'
        ? 'office_first'
        : 'designation_first';
    orderedVisibleLineOptions.value = buildOrderedVisibleLineOptions(draft.signatory_info_order);

    isHydrating.value = false;
}

function applySnapshot() {
    if (!selectedOfficeHead.value) {
        return;
    }

    const signatoryTitles = props.allowTitleSelection
        ? sanitizeSelectedTitles(draft.signatory_titles, orderedTitleOptions.value)
        : [...availableOfficeHeadTitles.value];

    emit('apply', {
        office_head_id: draft.office_head_id,
        office_head: {
            ...selectedOfficeHead.value,
            titles: signatoryTitles,
            title: signatoryTitles,
        },
        signatory_titles: signatoryTitles,
        signatory_name_underline: props.allowDisplayOptions ? Boolean(draft.signatory_name_underline) : false,
        signatory_show_designation: props.allowDisplayOptions ? Boolean(draft.signatory_show_designation) : true,
        signatory_show_office: props.allowDisplayOptions ? Boolean(draft.signatory_show_office) : true,
        signatory_info_order: props.allowDisplayOptions && draft.signatory_info_order === 'office_first'
            ? 'office_first'
            : 'designation_first',
    });
    emit('update:show', false);
}

function setOfficeHeadTitleSelected(title, selected) {
    const normalizedTitle = normalizeText(title);

    if (!normalizedTitle || !orderedTitleOptions.value.includes(normalizedTitle)) {
        return;
    }

    const selectedSet = new Set(draft.signatory_titles);

    if (selected) {
        selectedSet.add(normalizedTitle);
    } else {
        selectedSet.delete(normalizedTitle);
    }

    draft.signatory_titles = orderedTitleOptions.value.filter((value) => selectedSet.has(value));
}

function isOfficeHeadTitleSelected(title) {
    return draft.signatory_titles.includes(normalizeText(title));
}

function selectedTitlePosition(title) {
    const index = draft.signatory_titles.indexOf(normalizeText(title));

    return index >= 0 ? index + 1 : null;
}

function onTitleDragStart(index, event) {
    if (orderedTitleOptions.value.length < 2) {
        event.preventDefault();
        return;
    }

    if (event.target?.closest('input, .p-checkbox, .p-checkbox-box, label')) {
        event.preventDefault();
        return;
    }

    draggedTitleIndex.value = index;
    dragOverTitleIndex.value = index;

    if (event.dataTransfer) {
        event.dataTransfer.effectAllowed = 'move';
        event.dataTransfer.setData('text/plain', String(index));
    }
}

function onTitleDragOver(index, event) {
    if (draggedTitleIndex.value === null) {
        return;
    }

    event.dataTransfer.dropEffect = 'move';
    dragOverTitleIndex.value = index;
}

function onTitleDrop(index, event) {
    event.preventDefault();

    if (draggedTitleIndex.value === null || draggedTitleIndex.value === index) {
        onTitleDragEnd();
        return;
    }

    const reordered = [...orderedTitleOptions.value];
    const [movedTitle] = reordered.splice(draggedTitleIndex.value, 1);

    reordered.splice(index, 0, movedTitle);
    orderedOfficeHeadTitles.value = reordered;
    syncSelectedTitlesToCurrentOrder();
    onTitleDragEnd();
}

function onTitleDragEnd() {
    draggedTitleIndex.value = null;
    dragOverTitleIndex.value = null;
}

function setVisibleLineSelected(lineId, selected) {
    if (lineId === 'designation') {
        draft.signatory_show_designation = Boolean(selected);
        return;
    }

    if (lineId === 'office') {
        draft.signatory_show_office = Boolean(selected);
    }
}

function isVisibleLineSelected(lineId) {
    if (lineId === 'designation') {
        return Boolean(draft.signatory_show_designation);
    }

    if (lineId === 'office') {
        return Boolean(draft.signatory_show_office);
    }

    return false;
}

function visibleLinePosition(lineId) {
    const selectedLineIds = orderedVisibleLineOptions.value.filter((id) => isVisibleLineSelected(id));
    const index = selectedLineIds.indexOf(lineId);

    return index >= 0 ? index + 1 : null;
}

function onVisibleLineDragStart(index, event) {
    if (orderedVisibleLineRows.value.length < 2) {
        event.preventDefault();
        return;
    }

    if (event.target?.closest('input, .p-checkbox, .p-checkbox-box, label')) {
        event.preventDefault();
        return;
    }

    draggedDetailIndex.value = index;
    dragOverDetailIndex.value = index;

    if (event.dataTransfer) {
        event.dataTransfer.effectAllowed = 'move';
        event.dataTransfer.setData('text/plain', String(index));
    }
}

function onVisibleLineDragOver(index, event) {
    if (draggedDetailIndex.value === null) {
        return;
    }

    event.dataTransfer.dropEffect = 'move';
    dragOverDetailIndex.value = index;
}

function onVisibleLineDrop(index, event) {
    event.preventDefault();

    if (draggedDetailIndex.value === null || draggedDetailIndex.value === index) {
        onVisibleLineDragEnd();
        return;
    }

    const reordered = [...orderedVisibleLineOptions.value];
    const [movedLineId] = reordered.splice(draggedDetailIndex.value, 1);

    reordered.splice(index, 0, movedLineId);
    orderedVisibleLineOptions.value = reordered;
    syncVisibleLineOrderToDraft();
    onVisibleLineDragEnd();
}

function onVisibleLineDragEnd() {
    draggedDetailIndex.value = null;
    dragOverDetailIndex.value = null;
}

function officeHeadTitlesForId(officeHeadId) {
    return normalizedOfficeHeads.value.find((officeHead) => officeHead.id === officeHeadId)?.titles ?? [];
}

function buildOrderedVisibleLineOptions(detailOrder) {
    return detailOrder === 'office_first'
        ? ['office', 'designation']
        : ['designation', 'office'];
}

function buildOrderedTitleOptions(availableTitles, selectedTitles = []) {
    const normalizedAvailableTitles = uniqueTextLines(availableTitles);
    const normalizedSelectedTitles = sanitizeSelectedTitles(selectedTitles, normalizedAvailableTitles);
    const selectedSet = new Set(normalizedSelectedTitles);

    return [
        ...normalizedSelectedTitles,
        ...normalizedAvailableTitles.filter((title) => !selectedSet.has(title)),
    ];
}

function syncSelectedTitlesToCurrentOrder() {
    const selectedSet = new Set(draft.signatory_titles);
    draft.signatory_titles = orderedTitleOptions.value.filter((title) => selectedSet.has(title));
}

function syncVisibleLineOrderToDraft() {
    draft.signatory_info_order = orderedVisibleLineOptions.value[0] === 'office'
        ? 'office_first'
        : 'designation_first';
}

function sanitizeSelectedTitles(selectedTitles, availableTitles) {
    if (!Array.isArray(selectedTitles)) {
        return [...availableTitles];
    }

    return selectedTitles
        .map((title) => normalizeText(title))
        .filter((title) => availableTitles.includes(title));
}

function normalizeOfficeHead(officeHead) {
    const titles = Array.isArray(officeHead?.titles)
        ? officeHead.titles
        : Array.isArray(officeHead?.title)
            ? officeHead.title
            : normalizeText(officeHead?.title)
                ? [normalizeText(officeHead?.title)]
                : [];

    return {
        ...officeHead,
        name: normalizeText(officeHead?.name),
        office: normalizeText(officeHead?.office) || null,
        titles: uniqueTextLines(titles),
    };
}

function buildPreviewLines(officeHead, config, allowTitleSelection, allowDisplayOptions) {
    if (!officeHead) {
        return [];
    }

    const designationLines = (allowTitleSelection ? config.signatory_titles : officeHead.titles) ?? [];
    const showDesignation = allowDisplayOptions ? config.signatory_show_designation : true;
    const showOffice = allowDisplayOptions ? config.signatory_show_office : true;
    const order = allowDisplayOptions && config.signatory_info_order === 'office_first'
        ? 'office_first'
        : 'designation_first';
    const officeLines = showOffice && officeHead.office ? [officeHead.office] : [];
    const titleLines = showDesignation ? designationLines : [];

    return uniqueTextLines(order === 'office_first'
        ? [...officeLines, ...titleLines]
        : [...titleLines, ...officeLines]);
}

function uniqueTextLines(lines) {
    const seen = new Set();

    return (lines ?? [])
        .map((line) => normalizeText(line))
        .filter((line) => {
            if (!line) {
                return false;
            }

            const key = line.toLowerCase();

            if (seen.has(key)) {
                return false;
            }

            seen.add(key);
            return true;
        });
}

function normalizeText(value) {
    return typeof value === 'string' ? value.trim() : '';
}

const elModal = ref(null);
const dragOffset = ref({ x: 0, y: 0 });
const dragStart = ref(null);

const modalStyle = computed(() => ({
    transform: `translate(${dragOffset.value.x}px, ${dragOffset.value.y}px)`,
}));

function onDragStart(event) {
    if (event.target.closest('button, input, textarea, .p-inputtext, .p-select, .p-toggleswitch')) return;
    dragStart.value = { x: event.clientX - dragOffset.value.x, y: event.clientY - dragOffset.value.y };
    window.addEventListener('pointermove', onDragMove);
    window.addEventListener('pointerup', onDragEnd);
}

function onDragMove(event) {
    if (!dragStart.value) return;
    dragOffset.value = { x: event.clientX - dragStart.value.x, y: event.clientY - dragStart.value.y };
}

function onDragEnd() {
    dragStart.value = null;
    window.removeEventListener('pointermove', onDragMove);
    window.removeEventListener('pointerup', onDragEnd);
}
</script>