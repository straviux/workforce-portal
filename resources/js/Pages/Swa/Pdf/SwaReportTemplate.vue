<template>
    <div class="swa-sheet" style="margin-top:0 !important">
        <div class="swa-sheet__header">
            <div class="swa-sheet__header-logos"
                :class="{ 'swa-sheet__header-logos--stacked': !isScholarshipProgramOffice }">
                <img :src="pgpLogoUrl" alt="PGP Logo" class="swa-sheet__logo swa-sheet__logo--left" />
                <div class="swa-sheet__header-text">
                    <p class="swa-sheet__line">Republic of the Philippines</p>
                    <p class="swa-sheet__line">Provincial Government of Palawan</p>
                    <p v-if="preparedByOffice" class="swa-sheet__line swa-sheet__line--program">{{ preparedByOffice }}</p>
                    <p class="swa-sheet__line swa-sheet__line--title">STATEMENT OF WORK ACCOMPLISHED</p>
                    <p class="swa-sheet__line swa-sheet__line--name">{{ preparedByName }}</p>
                    <p class="swa-sheet__line swa-sheet__line--designation">{{ preparedByTitle }}</p>
                    <p class="swa-sheet__line swa-sheet__line--period">For the Period: {{ documentPeriodLabel }}</p>
                </div>
                <img v-if="isScholarshipProgramOffice" :src="yakapLogoUrl" alt="YAKAP Logo"
                    class="swa-sheet__logo swa-sheet__logo--right" />
            </div>
        </div>

        <table class="swa-layout-table">
            <thead>
                <tr class="swa-header-row"
                    :style="{ '--swa-header-height': `${headerHeightPx}px`, height: `${headerHeightPx}px` }">
                    <th class="swa-date-header-cell">
                        <span class="swa-date-header-cell__text">DATE</span>
                    </th>
                    <th class="swa-task-header">
                        <span class="swa-task-header__divider" aria-hidden="true"></span>
                        <span class="swa-task-header__text">{{ task1?.task_name }}</span>
                    </th>
                    <th class="swa-task-header">
                        <span class="swa-task-header__divider" aria-hidden="true"></span>
                        <span class="swa-task-header__text">{{ task2?.task_name }}</span>
                    </th>
                    <th class="swa-task-header">
                        <span class="swa-task-header__divider" aria-hidden="true"></span>
                        <span class="swa-task-header__text">{{ task3?.task_name }}</span>
                    </th>
                    <th class="swa-task-header">
                        <span class="swa-task-header__divider" aria-hidden="true"></span>
                        <span class="swa-task-header__text">{{ task4?.task_name }}</span>
                    </th>
                    <th class="swa-task-header">
                        <span class="swa-task-header__divider" aria-hidden="true"></span>
                        <span class="swa-task-header__text">{{ task5?.task_name }}</span>
                    </th>
                    <th class="swa-task-header swa-task-header--trailing">
                        <span class="swa-task-header__divider" aria-hidden="true"></span>
                    </th>
                </tr>
            </thead>

            <tbody>
                <tr v-for="documentRow in documentRows" :key="documentRow.key">
                    <td class="swa-date-cell" style="width: 40pt;">{{ documentRow.day_number }}</td>

                    <td v-if="documentRow.kind === 'special'" class="swa-special-row" :colspan="draftRows.length + 1">
                        HOLIDAY
                    </td>
                    <td v-else-if="documentRow.kind === 'work_suspension'" class="swa-special-row"
                        :colspan="draftRows.length + 1">
                        WORK SUSPENSION
                    </td>
                    <td v-else-if="documentRow.kind === 'offday'" class="swa-special-row"
                        :colspan="draftRows.length + 1">
                        {{ documentRow.label }}
                    </td>

                    <template v-else>
                        <td style="text-align: center;">
                            <input v-if="taskValueAt(documentRow, 1)?.task_type === 'countable' && editable"
                                v-model.number="taskValueAt(documentRow, 1).cell.numeric_value" v-keyfilter.int
                                style="width: 100%; min-width: 56px; border: none; background: transparent; text-align: center; font-size: 11px; outline: none; color: #111827; opacity: 1;"
                                :disabled="!canManage || isSavingReport" />
                            <span v-else-if="taskValueAt(documentRow, 1)?.task_type === 'countable'"
                                style="display: inline-flex; align-items: center; justify-content: center; width: 100%; min-height: 20px; font-size: 11px; font-weight: 600;">
                                {{ formatNumericValue(taskValueAt(documentRow, 1)?.cell.numeric_value) }}
                            </span>
                            <button v-else-if="editable" type="button"
                                style="width: 100%; min-height: 20px; border: none; background: transparent; font-size: 14px; font-weight: 700; line-height: 1; color: #111827; opacity: 1;"
                                :disabled="!canManage || isSavingReport"
                                @click="toggleMarkValue(taskValueAt(documentRow, 1).cell)">
                                {{ taskValueAt(documentRow, 1)?.cell.mark_value === 'check' ? '✓' : '-' }}
                            </button>
                            <span v-else
                                style="display: inline-flex; align-items: center; justify-content: center; width: 100%; min-height: 20px; font-size: 11px; font-weight: 600;">{{
                                taskValueAt(documentRow, 1)?.cell.mark_value === 'check' ? '✓' : '-' }}</span>
                        </td>

                        <td style="text-align: center;">
                            <input v-if="taskValueAt(documentRow, 2)?.task_type === 'countable' && editable"
                                v-model.number="taskValueAt(documentRow, 2).cell.numeric_value" v-keyfilter.int
                                style="width: 100%; min-width: 56px; border: none; background: transparent; text-align: center; font-size: 11px; outline: none; color: #111827; opacity: 1;"
                                :disabled="!canManage || isSavingReport" />
                            <span v-else-if="taskValueAt(documentRow, 2)?.task_type === 'countable'"
                                style="display: inline-flex; align-items: center; justify-content: center; width: 100%; min-height: 20px; font-size: 11px; font-weight: 600;">
                                {{ formatNumericValue(taskValueAt(documentRow, 2)?.cell.numeric_value) }}
                            </span>
                            <button v-else-if="editable" type="button"
                                style="width: 100%; min-height: 20px; border: none; background: transparent; font-size: 14px; font-weight: 700; line-height: 1; color: #111827; opacity: 1;"
                                :disabled="!canManage || isSavingReport"
                                @click="toggleMarkValue(taskValueAt(documentRow, 2).cell)">
                                {{ taskValueAt(documentRow, 2)?.cell.mark_value === 'check' ? '✓' : '-' }}
                            </button>
                            <span v-else
                                style="display: inline-flex; align-items: center; justify-content: center; width: 100%; min-height: 20px; font-size: 11px; font-weight: 600;">{{
                                taskValueAt(documentRow, 2)?.cell.mark_value === 'check' ? '✓' : '-' }}</span>
                        </td>

                        <td style="text-align: center;">
                            <input v-if="taskValueAt(documentRow, 3)?.task_type === 'countable' && editable"
                                v-model.number="taskValueAt(documentRow, 3).cell.numeric_value" v-keyfilter.int
                                style="width: 100%; min-width: 56px; border: none; background: transparent; text-align: center; font-size: 11px; outline: none; color: #111827; opacity: 1;"
                                :disabled="!canManage || isSavingReport" />
                            <span v-else-if="taskValueAt(documentRow, 3)?.task_type === 'countable'"
                                style="display: inline-flex; align-items: center; justify-content: center; width: 100%; min-height: 20px; font-size: 11px; font-weight: 600;">
                                {{ formatNumericValue(taskValueAt(documentRow, 3)?.cell.numeric_value) }}
                            </span>
                            <button v-else-if="editable" type="button"
                                style="width: 100%; min-height: 20px; border: none; background: transparent; font-size: 14px; font-weight: 700; line-height: 1; color: #111827; opacity: 1;"
                                :disabled="!canManage || isSavingReport"
                                @click="toggleMarkValue(taskValueAt(documentRow, 3).cell)">
                                {{ taskValueAt(documentRow, 3)?.cell.mark_value === 'check' ? '✓' : '-' }}
                            </button>
                            <span v-else
                                style="display: inline-flex; align-items: center; justify-content: center; width: 100%; min-height: 20px; font-size: 11px; font-weight: 600;">{{
                                taskValueAt(documentRow, 3)?.cell.mark_value === 'check' ? '✓' : '-' }}</span>
                        </td>

                        <td style="text-align: center;">
                            <input v-if="taskValueAt(documentRow, 4)?.task_type === 'countable' && editable"
                                v-model.number="taskValueAt(documentRow, 4).cell.numeric_value" v-keyfilter.int
                                style="width: 100%; min-width: 56px; border: none; background: transparent; text-align: center; font-size: 11px; outline: none; color: #111827; opacity: 1;"
                                :disabled="!canManage || isSavingReport" />
                            <span v-else-if="taskValueAt(documentRow, 4)?.task_type === 'countable'"
                                style="display: inline-flex; align-items: center; justify-content: center; width: 100%; min-height: 20px; font-size: 11px; font-weight: 600;">
                                {{ formatNumericValue(taskValueAt(documentRow, 4)?.cell.numeric_value) }}
                            </span>
                            <button v-else-if="editable" type="button"
                                style="width: 100%; min-height: 20px; border: none; background: transparent; font-size: 14px; font-weight: 700; line-height: 1; color: #111827; opacity: 1;"
                                :disabled="!canManage || isSavingReport"
                                @click="toggleMarkValue(taskValueAt(documentRow, 4).cell)">
                                {{ taskValueAt(documentRow, 4)?.cell.mark_value === 'check' ? '✓' : '-' }}
                            </button>
                            <span v-else
                                style="display: inline-flex; align-items: center; justify-content: center; width: 100%; min-height: 20px; font-size: 11px; font-weight: 600;">{{
                                taskValueAt(documentRow, 4)?.cell.mark_value === 'check' ? '✓' : '-' }}</span>
                        </td>

                        <td style="text-align: center;">
                            <input v-if="taskValueAt(documentRow, 5)?.task_type === 'countable' && editable"
                                v-model.number="taskValueAt(documentRow, 5).cell.numeric_value" v-keyfilter.int
                                style="width: 100%; min-width: 56px; border: none; background: transparent; text-align: center; font-size: 11px; outline: none; color: #111827; opacity: 1;"
                                :disabled="!canManage || isSavingReport" />
                            <span v-else-if="taskValueAt(documentRow, 5)?.task_type === 'countable'"
                                style="display: inline-flex; align-items: center; justify-content: center; width: 100%; min-height: 20px; font-size: 11px; font-weight: 600;">
                                {{ formatNumericValue(taskValueAt(documentRow, 5)?.cell.numeric_value) }}
                            </span>
                            <button v-else-if="editable" type="button"
                                style="width: 100%; min-height: 20px; border: none; background: transparent; font-size: 14px; font-weight: 700; line-height: 1; color: #111827; opacity: 1;"
                                :disabled="!canManage || isSavingReport"
                                @click="toggleMarkValue(taskValueAt(documentRow, 5).cell)">
                                {{ taskValueAt(documentRow, 5)?.cell.mark_value === 'check' ? '✓' : '-' }}
                            </button>
                            <span v-else
                                style="display: inline-flex; align-items: center; justify-content: center; width: 100%; min-height: 20px; font-size: 11px; font-weight: 600;">{{
                                taskValueAt(documentRow, 5)?.cell.mark_value === 'check' ? '✓' : '-' }}</span>
                        </td>

                        <td class="swa-value-cell"></td>
                    </template>

                </tr>
            </tbody>
        </table>

        <p class="swa-attestation">
            I hereby attest, on my honor, that the foregoing information is true, correct, and complete to the best of
            my knowledge and belief, based on authentic records and/or verified facts. I further affirm that this
            attestation is made in good faith, without any intention to mislead, falsify, or conceal material
            information, and in compliance with applicable laws, rules, and regulations.
        </p>

        <div class="swa-signatures">
            <div>
                <p class="swa-signature-label">Prepared by:</p>
                <p class="swa-signature-line">{{ preparedByName }}</p>
                <p class="swa-signature-title">{{ preparedByTitle }}</p>
            </div>

            <div>
                <p class="swa-signature-label">Verified and Approved:</p>
                <p :class="['swa-signature-line', { 'swa-signature-line--plain': !reviewerNameUnderline }]">{{
                    reviewerName }}
                </p>
                <p v-for="line in reviewerDetailLines" :key="line" class="swa-signature-title">{{ line }}</p>
            </div>
        </div>

        <div class="revision">
            <table class="swa-revision-table">
                <tbody>
                    <tr>
                        <td>Revision No.:</td>
                        <td>01</td>
                        <td>Effective Date:</td>
                        <td>01 July 2025</td>
                        <td>Page 1 of 1</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    draftRows: { type: Array, default: () => [] },
    documentRows: { type: Array, default: () => [] },
    preparedByOffice: { type: String, default: '' },
    preparedByName: { type: String, default: '______________________________' },
    preparedByTitle: { type: String, default: '______________________________' },
    reviewerName: { type: String, default: '______________________________' },
    reviewerTitles: { type: Array, default: () => ['PROGRAM MANAGER'] },
    reviewerOffice: { type: String, default: '' },
    reviewerNameUnderline: { type: Boolean, default: false },
    reviewerShowDesignation: { type: Boolean, default: true },
    reviewerShowOffice: { type: Boolean, default: true },
    reviewerInfoOrder: { type: String, default: 'designation_first' },
    documentPeriodLabel: { type: String, default: '—' },
    editable: { type: Boolean, default: true },
    canManage: { type: Boolean, default: false },
    isSavingReport: { type: Boolean, default: false },
});

const pgpLogoUrl = '/images/pgp-logo.svg';
const yakapLogoUrl = '/images/yakap-logo.png';

// The YAKAP (scholarship program) logo only applies to Scholarship Program reports.
// Other offices show just the PGP logo, stacked above the header text instead of beside it.
// An empty office (personal subject with none set) matches nothing, so no program logo is implied.
const isScholarshipProgramOffice = computed(
    () => (props.preparedByOffice ?? '').trim().toUpperCase() === 'SCHOLARSHIP PROGRAM',
);

// Every SWA report has exactly 5 tasks (sort_order 1-5) — loaded individually
// instead of via v-for so each column's header markup can be styled independently.
const task1 = computed(() => findTaskBySortOrder(1));
const task2 = computed(() => findTaskBySortOrder(2));
const task3 = computed(() => findTaskBySortOrder(3));
const task4 = computed(() => findTaskBySortOrder(4));
const task5 = computed(() => findTaskBySortOrder(5));

function findTaskBySortOrder(sortOrder) {
    return props.draftRows.find((task) => task.sort_order === sortOrder) ?? null;
}

// Slanted-header sizing (45 degrees, same geometry as the CSS below).
// This template is rendered to a static HTML string for printing, so the
// header height cannot be measured from the DOM: it is estimated from the
// title text instead, using deliberately conservative metrics (fewer chars
// per line and a wider average char than 7pt bold Arial really needs) so a
// tall title yields a taller header rather than text spilling past the frame.
// A title rotated -45deg occupies a vertical extent of
// (block width + block height) * sin(45deg); the tallest of the 5 titles wins.
const TASK_HEADER_TITLE_MAX_WIDTH_PX = 106;
const TASK_HEADER_CHARS_PER_LINE = 19;
const TASK_HEADER_AVG_CHAR_WIDTH_PX = 5.5;
const TASK_HEADER_LINE_HEIGHT_PX = 11;
const TASK_HEADER_CLEARANCE_PX = 10;
const TASK_HEADER_MIN_HEIGHT_PX = 76;

const headerHeightPx = computed(() => {
    const titles = [task1.value, task2.value, task3.value, task4.value, task5.value]
        .map((task) => task?.task_name ?? '');

    return Math.max(TASK_HEADER_MIN_HEIGHT_PX, ...titles.map(estimateSlantedTitleHeight));
});

function estimateSlantedTitleHeight(title) {
    const lines = wrapSlantedTitle(String(title ?? '').trim());

    if (!lines.length) {
        return TASK_HEADER_CLEARANCE_PX;
    }

    const blockWidthPx = Math.min(
        Math.max(...lines.map((line) => line.length)) * TASK_HEADER_AVG_CHAR_WIDTH_PX,
        TASK_HEADER_TITLE_MAX_WIDTH_PX,
    );
    const blockHeightPx = lines.length * TASK_HEADER_LINE_HEIGHT_PX;

    return Math.ceil((blockWidthPx + blockHeightPx) * Math.SQRT1_2 + TASK_HEADER_CLEARANCE_PX);
}

// Greedy word wrap mirroring the CSS text box (106px wide, hyphenation and
// anywhere-breaking can only shorten lines, so this overestimates, never under).
function wrapSlantedTitle(text) {
    if (!text) {
        return [];
    }

    const lines = [];
    let current = '';

    const pushLine = (line) => {
        if (line) {
            lines.push(line);
        }
    };

    for (const word of text.split(/\s+/u)) {
        let rest = word;

        while (rest.length > TASK_HEADER_CHARS_PER_LINE) {
            pushLine(current);
            current = '';
            pushLine(rest.slice(0, TASK_HEADER_CHARS_PER_LINE));
            rest = rest.slice(TASK_HEADER_CHARS_PER_LINE);
        }

        if (!current) {
            current = rest;
        } else if (current.length + 1 + rest.length <= TASK_HEADER_CHARS_PER_LINE) {
            current += ` ${rest}`;
        } else {
            pushLine(current);
            current = rest;
        }
    }

    pushLine(current);

    return lines;
}

function taskValueAt(documentRow, sortOrder) {
    return (documentRow.task_values ?? []).find((taskValue) => taskValue.sort_order === sortOrder) ?? null;
}

const reviewerDetailLines = computed(() => buildReviewerDetailLines(
    props.reviewerTitles,
    props.reviewerOffice,
    props.reviewerShowDesignation,
    props.reviewerShowOffice,
    props.reviewerInfoOrder,
));

function buildReviewerDetailLines(titles, office, showDesignation, showOffice, infoOrder) {
    const designationLines = showDesignation
        ? (Array.isArray(titles) ? titles : []).map((title) => normalizeText(title)).filter(Boolean)
        : [];
    const officeLine = showOffice && normalizeText(office)
        ? [normalizeText(office)]
        : [];

    return uniqueTextLines(infoOrder === 'office_first'
        ? [...officeLine, ...designationLines]
        : [...designationLines, ...officeLine]);
}

function uniqueTextLines(lines) {
    const seen = new Set();

    return (lines ?? []).filter((line) => {
        const text = normalizeText(line);

        if (!text) {
            return false;
        }

        const key = text.toLowerCase();
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

function formatTaskType(value) {
    return value === 'check_blank' ? 'Check / Blank (-)' : 'Countable';
}

function formatNumericValue(value) {
    if (value === null || value === undefined || value === '') return '-';

    const numericValue = Number(value);
    if (!Number.isFinite(numericValue)) return String(value);
    if (numericValue === 0) return '-';

    return Number.isInteger(numericValue)
        ? String(numericValue)
        : numericValue.toFixed(2).replace(/\.0+$|(?<=\.[0-9]*[1-9])0+$/u, '');
}

function toggleMarkValue(cell) {
    if (!props.canManage || props.isSavingReport) return;

    cell.mark_value = cell.mark_value === 'check' ? 'dash' : 'check';
}
</script>

<style scoped src="./swa-report-template.css"></style>