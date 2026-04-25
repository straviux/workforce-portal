<template>
    <div
        style="max-width:950px;margin:0 auto;background:#fff;font-family:Verdana,Geneva,sans-serif;font-size:11pt;line-height:1.55;color:#333;">

        <div style="display:flex;flex-direction:column;align-items:center;justify-content:center;text-align:center;">
            <img :src="logoUrl" alt="PGP Logo" style="width:82pt;height:auto;margin-bottom:10pt;" />

            <div>
                <p style="font-size:11.5pt;">Republic of the Philippines</p>
                <p style="font-size:11.5pt;margin-top: -2pt !important;">Provincial Government of Palawan</p>
                <p style="font-size:11.5pt;font-weight: 700;margin-top: -2pt !important;">Office of the Governor</p>
                <p style="font-size:11.5pt;font-style: italic;margin-top: -2pt !important;">Capitol Complex, Puerto
                    Princesa
                    City</p>
            </div>
        </div>

        <div style="text-align:center;margin-top:28pt;">
            <p style="font-size:18pt;font-weight:700;letter-spacing:2.7pt;">CERTIFICATION</p>
        </div>

        <div style="margin-top:30pt;font-size:11.5pt;line-height:1.9;text-align:justify;">
            <p style="text-indent:40pt;">
                THIS IS TO CERTIFY that <strong>{{ subjectDisplayName }},</strong> {{
                    designation }} of {{ office }}, is not a scholar/beneficiary under the <strong>Provincial Government of
                    Palawan
                    YAKAP sa Edukasyon Scholarship Program</strong>
            </p>

            <p style="text-indent:40pt;margin-top:14pt !important;">
                Based on program records, {{ subjectReference }} has not received any form of educational financial
                assistance, grant, or scholarship support from this Office. Accordingly, {{ subjectReference }} does
                not have any contractual obligations, including but not limited to Return -of- Service (ROS)
                commitments, with the Provincial Government of
                Palawan.
            </p>

            <p style="text-indent:40pt;margin-top:14pt !important;">
                Further, this is to certify that {{ subjectShortReference }} is hereby cleared of any and all
                obligations
                and accountabilities in relation to the Provincial Government of Palawan YAKAP sa Edukasyon Scholarship
                Program.
            </p>

            <p style="text-indent:40pt;margin-top:14pt !important;">
                This certification is issued upon the request of <strong>{{ subjectShortReference }}</strong> for
                whatever legal or official purpose it may serve.
            </p>

            <p style="text-indent:40pt;margin-top:14pt !important;">
                Issued this <span v-html="formattedIssuedDate" /> at the Provincial Government of Palawan, Puerto
                Princesa City,
                Philippines.
            </p>
        </div>

        <div style="margin-top:54pt;display:flex;justify-content:flex-end;">
            <div>
                <p style="font-weight: 700;">Certified Correct:</p>
                <p style="font-weight:700;font-size:11pt;margin-top: 42pt !important;">{{
                    certification?.signatory_name ||
                    '______________________________' }}</p>
                <p v-for="title in signatoryTitles" :key="title" style="font-size:11pt;">{{ title }}</p>
                <p v-if="certification?.signatory_office" style="font-size:11pt;">{{ certification.signatory_office }}
                </p>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    certification: { type: Object, required: true },
});

const logoUrl = '/images/pgp-logo.svg';

const subjectName = computed(() => props.certification?.subject_name || '—');
const subjectHonorific = computed(() => normalizeText(props.certification?.subject_honorific));
const designation = computed(() => props.certification?.designation || '—');
const office = computed(() => props.certification?.office || '—');

const subjectDisplayName = computed(() => {
    if (subjectName.value === '—') return '—';

    return [subjectHonorific.value, subjectName.value].filter(Boolean).join(' ');
});

const subjectReference = computed(() => subjectDisplayName.value === '—' ? 'the requesting party' : subjectDisplayName.value);
const subjectShortReference = computed(() => {
    if (subjectName.value === '—') return 'the requesting party';

    const shortenedName = removeFirstName(subjectName.value);
    return [subjectHonorific.value, shortenedName].filter(Boolean).join(' ') || subjectDisplayName.value;
});

const formattedIssuedDate = computed(() => {
    const value = props.certification?.issued_date;
    if (!value) return '__________ day of __________, ______';

    const parsed = new Date(value);
    if (Number.isNaN(parsed.getTime())) {
        return value;
    }

    const day = parsed.getDate();
    const month = parsed.toLocaleDateString('en-PH', { month: 'long' });
    const year = parsed.getFullYear();

    return `<strong>${formatOrdinal(day)} day </strong> of <strong>${month},${year}</strong>`;
});

const signatoryTitles = computed(() => {
    if (Array.isArray(props.certification?.signatory_titles)) {
        return props.certification.signatory_titles;
    }

    return [];
});

function normalizeText(value) {
    const text = typeof value === 'string' ? value.trim() : '';
    return text || '';
}

function removeFirstName(value) {
    const parts = String(value).trim().split(/\s+/).filter(Boolean);

    if (parts.length <= 1) {
        return parts[0] ?? '';
    }

    return parts.slice(1).join(' ');
}

function formatOrdinal(value) {
    const remainder100 = value % 100;

    if (remainder100 >= 11 && remainder100 <= 13) {
        return `${value}th`;
    }

    switch (value % 10) {
        case 1:
            return `${value}st`;
        case 2:
            return `${value}nd`;
        case 3:
            return `${value}rd`;
        default:
            return `${value}th`;
    }
}
</script>