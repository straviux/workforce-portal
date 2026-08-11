<template>
    <div class="dtr-sheet" style="margin-top:0 !important">
        <div style="display: flex;flex-direction: column; align-items: center; justify-content: center; ">
            <div style="align-self: flex-start;font-size:5pt">National Form No. 48</div>
            <div style="font-size: 11pt; font-weight: bold;margin-top:6pt">DAILY TIME RECORD</div>
            <div style="display:flex;width:100%;justify-content: space-between;margin-top:4pt"><span style="font-size:9pt;font-style: italic;">NAME:</span> 
                <div style="border-bottom: 1px solid #111;font-size: 8pt;width:80% !important;font-weight: bold;text-align:center">{{ preparedByName }}</div>
            </div>
            <div style="display:flex;width:100%;justify-content: space-between;margin-top:4pt"><span style="font-size:9pt;font-style: italic;">For the month of:</span> 
                <div style="border-bottom: 1px solid #111;font-size: 7pt;font-weight: bold;text-align:center;width: 65% !important;">{{ documentPeriodLabel }}</div>
            </div>

            <div style="display:flex;width:100%;justify-content: space-between;margin-top:8pt"><span style="font-size:7pt;font-style: italic;">Office Hour of Arrival</span> 
                <div style="font-size: 6pt;text-align:right;width: 50% !important;font-style: italic;">Regular Days ____________</div>
            </div>
            <div style="display:flex;width:100%;justify-content: space-between;margin-top:4pt"><span style="font-size:7pt;font-style: italic;">And Departure</span> 
                <div style="font-size: 6pt;text-align:right;width: 50% !important;font-style: italic;">Saturdays ____________</div>
            </div>
          
        </div>

        <table class="dtr-layout-table">
            <thead>
                <tr>
                    <th class="dtr-day-col" rowspan="2">Day</th>
                    <th class="dtr-group-header" colspan="2">AM</th>
                    <th class="dtr-group-header" colspan="2">PM</th>
                    <th class="" colspan="2">Undertime</th>
                </tr>
                <tr>
                    <th class="dtr-sub-header">Arrival</th>
                    <th class="dtr-sub-header">Depart.</th>
                    <th class="dtr-sub-header">Arrival</th>
                    <th class="dtr-sub-header">Depart.</th>
                    <th class="dtr-sub-header">Hrs.</th>
                    <th class="dtr-sub-header">Mins.</th>
                </tr>
            </thead>

            <tbody>
                <tr v-for="documentRow in documentRows" :key="documentRow.key">
                    <td class="dtr-date-cell">{{ documentRow.day_number }}</td>

                    <td v-if="documentRow.kind === 'special'" class="dtr-special-row" colspan="6">
                        HOLIDAY
                    </td>
                    <td v-else-if="documentRow.kind === 'work_suspension'" class="dtr-special-row" colspan="6">
                        WORK SUSPENSION
                    </td>
                    <td v-else-if="documentRow.kind === 'offday'" class="dtr-special-row" colspan="6">
                        {{ documentRow.label }}
                    </td>

                    <template v-else>
                        <td class="dtr-value-cell">{{ formatTime(documentRow.values.am_arrival) }}</td>
                        <td class="dtr-value-cell">{{ formatTime(documentRow.values.am_departure) }}</td>
                        <td class="dtr-value-cell">{{ formatTime(documentRow.values.pm_arrival) }}</td>
                        <td class="dtr-value-cell">{{ formatTime(documentRow.values.pm_departure) }}</td>
                        <td class="dtr-value-cell">{{ formatUndertimeUnit(documentRow.values.undertime_hours) }}</td>
                        <td class="dtr-value-cell">{{ formatUndertimeUnit(documentRow.values.undertime_minutes) }}</td>
                    </template>
                </tr>
            </tbody>
        </table>

        <div style="font-size: 6.5pt;text-align: justify;margin-top: 10pt">
            I CERTIFY on my honor that the above is true and correct on office the hours of work performed and 
            record of which was made daily the time of arrival and departure from office.
        </div>
        <div style="text-align: right;width:100%;font-size:5pt;margin-top:14pt">.............................................................</div>
        <div style="border-top: 1px solid #111; width: 100%;font-size:6pt;margin-top:10pt;padding-top: 2.5pt;">Verified as to prescribed Office hours:</div>
        <div style="display: flex; justify-content: center; margin-top: 18pt;">
          
            <div style="display: flex; flex-direction: column; align-items: center;font-weight: 600; border-top: 1px solid #111;  width: 80%;font-size: 7pt;padding-top: 3pt;">
                <p >{{
                    reviewerName }}
                </p>
                <p v-for="line in reviewerDetailLines" :key="line" style="font-size: 7pt;text-transform: capitalize !important;">{{ line }}</p>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    documentRows: { type: Array, default: () => [] },
    preparedByOffice: { type: String, default: 'SCHOLARSHIP PROGRAM' },
    preparedByName: { type: String, default: '______________________________' },
    preparedByTitle: { type: String, default: '______________________________' },
    reviewerName: { type: String, default: '______________________________' },
    reviewerTitles: { type: Array, default: () => ['Program Manager'] },
    reviewerOffice: { type: String, default: '' },
    reviewerNameUnderline: { type: Boolean, default: false },
    reviewerShowDesignation: { type: Boolean, default: true },
    reviewerShowOffice: { type: Boolean, default: true },
    reviewerInfoOrder: { type: String, default: 'designation_first' },
    documentPeriodLabel: { type: String, default: '—' },
});


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

function formatTime(value) {
    if (!value) return '';

    const [hourText, minuteText] = String(value).split(':');
    const hour = Number(hourText);
    const minute = Number(minuteText);

    if (!Number.isFinite(hour) || !Number.isFinite(minute)) return value;

    const period = hour >= 12 ? 'PM' : 'AM';
    const displayHour = hour % 12 === 0 ? 12 : hour % 12;

    return `${displayHour}:${String(minute).padStart(2, '0')} ${period}`;
}

function formatUndertimeUnit(value) {
    const numericValue = Number(value);

    return Number.isFinite(numericValue) && numericValue > 0 ? String(numericValue) : '';
}
</script>

<style scoped src="./dtr-report-template.css"></style>
