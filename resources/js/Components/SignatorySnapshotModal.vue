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
                                    <label class="ios-label">{{ selectionLabel }} <span class="text-red-500">*</span></label>
                                    <Select v-model="draft.office_head_id" :options="normalizedOfficeHeads" optionLabel="name"
                                        optionValue="id" :placeholder="selectionPlaceholder" class="w-full" />
                                </div>
                            </div>
                        </div>

                        <div v-if="allowTitleSelection && availableOfficeHeadTitles.length" class="ios-section pb-4">
                            <p class="ios-section-label">{{ titleSectionTitle }}</p>
                            <div class="ios-card p-4">
                                <div class="flex flex-wrap gap-2">
                                    <button v-for="title in availableOfficeHeadTitles" :key="title" type="button"
                                        class="rounded-full border px-3 py-1.5 text-xs font-medium transition cursor-pointer"
                                        :class="draft.signatory_titles.includes(title)
                                            ? 'border-emerald-500 bg-emerald-500 text-white'
                                            : 'border-surface-200 bg-white text-surface-500 dark:border-surface-700 dark:bg-surface-900 dark:text-surface-300'"
                                        @click="toggleOfficeHeadTitle(title)">
                                        {{ title }}
                                    </button>
                                </div>

                                <p v-if="titleSelectionHint" class="text-xs text-surface-400 mt-2">
                                    {{ titleSelectionHint }}
                                </p>
                            </div>
                        </div>

                        <div v-if="allowDisplayOptions" class="ios-section pb-4">
                            <p class="ios-section-label">{{ displayOptionsSectionTitle }}</p>
                            <div class="ios-card p-4 space-y-4">
                                <div class="flex items-center justify-between gap-4">
                                    <div>
                                        <p class="text-sm font-medium text-surface-800 dark:text-surface-100">Show designation</p>
                                        <p class="text-xs text-surface-500">Print the saved signatory titles under the name.</p>
                                    </div>
                                    <ToggleSwitch v-model="draft.signatory_show_designation" />
                                </div>

                                <div class="flex items-center justify-between gap-4">
                                    <div>
                                        <p class="text-sm font-medium text-surface-800 dark:text-surface-100">Show office</p>
                                        <p class="text-xs text-surface-500">Print the office line in the signature block.</p>
                                    </div>
                                    <ToggleSwitch v-model="draft.signatory_show_office" />
                                </div>

                                <div class="ios-form-group">
                                    <label class="ios-label">Detail order</label>
                                    <Select v-model="draft.signatory_info_order" :options="orderOptions" optionLabel="label"
                                        optionValue="value" class="w-full"
                                        :disabled="!draft.signatory_show_designation || !draft.signatory_show_office" />
                                    <p class="text-xs text-surface-400 mt-2">
                                        Only affects the print order when both designation and office are shown.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div v-if="showPreview" class="ios-section pb-4">
                            <p class="ios-section-label">{{ previewSectionTitle }}</p>
                            <div class="ios-card p-4 space-y-1 text-sm">
                                <p class="font-medium text-surface-800 dark:text-surface-100">
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

const orderOptions = [
    { label: 'Designation then Office', value: 'designation_first' },
    { label: 'Office then Designation', value: 'office_first' },
];

const draft = reactive({
    office_head_id: null,
    signatory_titles: [],
    signatory_show_designation: true,
    signatory_show_office: true,
    signatory_info_order: 'designation_first',
});

const isHydrating = ref(false);
const normalizedOfficeHeads = computed(() => (props.officeHeads ?? []).map(normalizeOfficeHead).filter((officeHead) => officeHead.id != null));
const selectedOfficeHead = computed(() => normalizedOfficeHeads.value.find((officeHead) => officeHead.id === draft.office_head_id) ?? null);
const availableOfficeHeadTitles = computed(() => selectedOfficeHead.value?.titles ?? []);
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

    draft.signatory_titles = [...officeHeadTitlesForId(officeHeadId)];
});

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
    draft.signatory_show_designation = props.initialValue?.signatory_show_designation ?? true;
    draft.signatory_show_office = props.initialValue?.signatory_show_office ?? true;
    draft.signatory_info_order = props.initialValue?.signatory_info_order === 'office_first'
        ? 'office_first'
        : 'designation_first';

    isHydrating.value = false;
}

function applySnapshot() {
    if (!selectedOfficeHead.value) {
        return;
    }

    const signatoryTitles = props.allowTitleSelection
        ? sanitizeSelectedTitles(draft.signatory_titles, availableOfficeHeadTitles.value)
        : [...availableOfficeHeadTitles.value];

    emit('apply', {
        office_head_id: draft.office_head_id,
        office_head: {
            ...selectedOfficeHead.value,
            titles: signatoryTitles,
            title: signatoryTitles,
        },
        signatory_titles: signatoryTitles,
        signatory_show_designation: props.allowDisplayOptions ? Boolean(draft.signatory_show_designation) : true,
        signatory_show_office: props.allowDisplayOptions ? Boolean(draft.signatory_show_office) : true,
        signatory_info_order: props.allowDisplayOptions && draft.signatory_info_order === 'office_first'
            ? 'office_first'
            : 'designation_first',
    });
    emit('update:show', false);
}

function toggleOfficeHeadTitle(title) {
    const normalizedTitle = normalizeText(title);

    if (!normalizedTitle || !availableOfficeHeadTitles.value.includes(normalizedTitle)) {
        return;
    }

    const exists = draft.signatory_titles.includes(normalizedTitle);
    const nextTitles = exists
        ? draft.signatory_titles.filter((value) => value !== normalizedTitle)
        : [...draft.signatory_titles, normalizedTitle];

    draft.signatory_titles = availableOfficeHeadTitles.value.filter((value) => nextTitles.includes(value));
}

function officeHeadTitlesForId(officeHeadId) {
    return normalizedOfficeHeads.value.find((officeHead) => officeHead.id === officeHeadId)?.titles ?? [];
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