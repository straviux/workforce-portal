<template>
    <div class="swa-sheet" style="margin-top:0 !important">
        <div class="swa-sheet__header">
            <div class="swa-sheet__header-logos"
                :class="{ 'swa-sheet__header-logos--stacked': !isScholarshipProgramOffice }">
                <img :src="pgpLogoUrl" alt="PGP Logo" class="swa-sheet__logo swa-sheet__logo--left" />
                <div class="swa-sheet__header-text">
                    <p class="swa-sheet__line">Republic of the Philippines</p>
                    <p class="swa-sheet__line">Provincial Government of Palawan</p>
                    <p class="swa-sheet__line swa-sheet__line--program">{{ preparedByOffice }}</p>
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
                <tr>
                    <th class="swa-task-header" style="width:40pt; border-right: none !important;">
                        <div class="swa-date-header">
                            <span>DATE</span>
                        </div>

                    </th>
                    <th
                        style="position: relative; height: 100px; padding: 0; vertical-align: bottom; border-left: none !important; border-right: none !important;">
                        <div
                            style="width: 138px; border-top: 1px solid #000;transform: rotate(-46deg);transform-origin: left bottom;" />
                        <div
                            style="position: absolute; text-indent:-15px;padding-left:15px;left: 45px !important; bottom: -5px; display: block; width: 120px !important; height: auto; white-space: normal; word-wrap: break-word; overflow-wrap: anywhere; word-break: break-word; transform: rotate(-46deg); transform-origin: left bottom; font-size: 7pt; line-height: 1.15; text-align: left; font-weight: 700;">
                            <span>{{ task1?.task_name }}</span>
                        </div>
                    </th>
                    <th
                        style="position: relative; height: 100px; padding: 0; vertical-align: bottom; border-left: none !important; border-right: none !important;">
                        <div
                            style="width: 138px; border-top: 1px solid #000;transform: rotate(-46deg);transform-origin: left bottom;" />
                        <div
                            style="position: absolute; text-indent:-15px;padding-left:15px;left: 45px !important; bottom: -5px; display: block; width: 120px !important; height: auto; white-space: normal; word-wrap: break-word; overflow-wrap: anywhere; word-break: break-word; transform: rotate(-46deg); transform-origin: left bottom; font-size: 7pt; line-height: 1.15; text-align: left; font-weight: 700;">
                            <span>{{ task2?.task_name }}</span>
                        </div>
                    </th>
                    <th
                        style="position: relative; height: 100px; padding: 0; vertical-align: bottom; border-left: none !important; border-right: none !important;">
                        <div
                            style="width: 138px; border-top: 1px solid #000;transform: rotate(-46deg);transform-origin: left bottom;" />
                        <div
                            style="position: absolute; text-indent:-15px;padding-left:15px;left: 45px !important; bottom: -5px; display: block; width: 120px !important; height: auto; white-space: normal; word-wrap: break-word; overflow-wrap: anywhere; word-break: break-word; transform: rotate(-46deg); transform-origin: left bottom; font-size: 7pt; line-height: 1.15; text-align: left; font-weight: 700;">
                            <span>{{ task3?.task_name }}</span>
                        </div>
                    </th>
                    <th
                        style="position: relative; height: 100px; padding: 0; vertical-align: bottom; border-left: none !important; border-right: none !important;">
                        <div
                            style="width: 138px; border-top: 1px solid #000;transform: rotate(-46deg);transform-origin: left bottom;" />
                        <div
                            style="position: absolute; text-indent:-15px;padding-left:15px;left: 45px !important; bottom: -5px; display: block; width: 120px !important; height: auto; white-space: normal; word-wrap: break-word; overflow-wrap: anywhere; word-break: break-word; transform: rotate(-46deg); transform-origin: left bottom; font-size: 7pt; line-height: 1.15; text-align: left; font-weight: 700;">
                            <span>{{ task4?.task_name }}</span>
                        </div>
                    </th>
                    <th
                        style="position: relative; height: 100px; padding: 0; vertical-align: bottom; border-left: none !important; border-right: none !important;">
                        <div
                            style="width: 138px; border-top: 1px solid #000;transform: rotate(-46deg);transform-origin: left bottom;" />
                        <div
                            style="position: absolute; text-indent:-15px;padding-left:15px;left:45px !important; bottom: -5px; display: block; width: 120px !important; height: auto; white-space: normal; word-wrap: break-word; overflow-wrap: anywhere; word-break: break-word; transform: rotate(-46deg); transform-origin: left bottom; font-size: 7pt; line-height: 1.15; text-align: left; font-weight: 700;">
                            <span>{{ task5?.task_name }}</span>
                        </div>
                    </th>
                    <th class="swa-task-header" style="border-left: none !important;">
                        <div
                            style="width: 138px; border-top: 1px solid #000;transform: rotate(-46deg);transform-origin: left bottom;" />

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
    preparedByOffice: { type: String, default: 'SCHOLARSHIP PROGRAM' },
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
const isScholarshipProgramOffice = computed(
    () => props.preparedByOffice.trim().toUpperCase() === 'SCHOLARSHIP PROGRAM',
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