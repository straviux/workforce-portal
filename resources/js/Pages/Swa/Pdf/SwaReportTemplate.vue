<template>
    <div class="swa-sheet">
        <div class="swa-sheet__header">
            <img :src="logoUrl" alt="PGP Logo" class="swa-sheet__logo" />
            <p class="swa-sheet__line">Provincial Government of Palawan</p>
            <p class="swa-sheet__line swa-sheet__line--program">{{ preparedByOffice }}</p>
            <p class="swa-sheet__line swa-sheet__line--title">STATEMENT OF WORK ACCOMPLISHED</p>
            <p class="swa-sheet__line swa-sheet__line--name">{{ preparedByName }}</p>
            <p class="swa-sheet__line swa-sheet__line--designation">{{ preparedByTitle }}</p>
            <p class="swa-sheet__line swa-sheet__line--period">For the Period: {{ documentPeriodLabel }}</p>
        </div>

        <table class="swa-layout-table">
            <thead>
                <tr>
                    <th class="swa-task-header" style="width:40pt; border-right: none !important;">
                        <div class="swa-date-header">
                            <span>DATE</span>
                        </div>

                    </th>
                    <th v-for="task in draftRows" :key="task.sort_order" class="swa-task-header"
                        style="border-left: none !important;">
                        <div
                            style="width: 138px; border-top: 1px solid #000;transform: rotate(-46deg);transform-origin: left bottom;" />
                        <div class="swa-task-header__text">
                            <span>{{ task.task_name }}</span>
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
                        <td v-for="taskValue in documentRow.task_values"
                            :key="`${documentRow.key}-${taskValue.sort_order}`" class="swa-value-cell">
                            <input v-if="taskValue.task_type === 'countable' && editable"
                                v-model.number="taskValue.cell.numeric_value" v-keyfilter.int class="swa-value-input"
                                :disabled="!canManage || isSavingReport" />

                            <span v-else-if="taskValue.task_type === 'countable'" class="swa-value-text">
                                {{ formatNumericValue(taskValue.cell.numeric_value) }}
                            </span>

                            <button v-else-if="editable" type="button" class="swa-mark-btn"
                                :disabled="!canManage || isSavingReport" @click="toggleMarkValue(taskValue.cell)">
                                {{ taskValue.cell.mark_value === 'check' ? '✓' : '-' }}
                            </button>

                            <span v-else class="swa-value-text">{{ taskValue.cell.mark_value === 'check' ? '✓' : '-'
                            }}</span>
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

const logoUrl = '/images/pgp-logo.svg';
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