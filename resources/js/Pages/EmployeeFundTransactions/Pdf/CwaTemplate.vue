<template>
    <div style="display:flex;flex-direction:column;width:100%;border:1pt solid #000;">
        <div style="padding:2pt">
            <p style="text-align: right;font-size: 7.5pt;">"Annex B"</p>
        </div>
        <!-- GOVERNMENT HEADER -->
        <div
            style="position:relative;display:flex;flex-direction:column;align-items:center;justify-content:center;padding:8pt 4pt;min-height:58pt;">
            <img :src="'/images/pgp-logo.svg'" alt="PGP Logo"
                style="position:absolute;left:15%;top:50%;transform:translateY(-50%);width:54pt;height:auto;" />
            <p class="t-9">Republic of the Philippines</p>
            <p class="t-9">PROVINCIAL GOVERNMENT OF PALAWAN</p>
            <p class="t-9">OFFICE OF THE GOVERNOR</p>
            <p class="bold t-9">CERTIFICATE OF WORK ACCOMPLISHED</p>
            <p class="t-9">For the Period: {{ periodLabel }}</p>
        </div>





        <!-- TABLE -->
        <div style="display:flex;flex-direction:column;">
            <!-- acknowledgement -->
            <table class="t-8" style="width:100%;border-collapse:collapse;table-layout:fixed;">
                <colgroup>
                    <!-- No -->
                    <col style="width:2%">
                    <!-- COSP Name -->
                    <col style="width:14.0%">
                    <!-- Contract Ref No. -->
                    <col style="width:7.5%">
                    <!-- DTR -->
                    <col style="width:5%">
                    <!-- Lost Hours Amount -->
                    <col style="width:5%">
                    <!-- SWA -->
                    <col style="width:2.8%">
                    <!-- ATM Account No. -->
                    <col style="width:8%">
                    <!-- Monthly Compensation -->
                    <col style="width:8%">
                    <!-- SSS -->
                    <col style="width:4.8%">
                    <!-- PhilHealth -->
                    <col style="width:5.5%">
                    <!-- HDMF -->
                    <col style="width:4.8%">
                    <!-- WHT -->
                    <col style="width:3.3%">
                    <!-- LOAN -->
                    <col style="width:3.3%">
                    <!-- Total Deductions -->
                    <col style="width:5.5%">
                    <!-- Net Amt Due -->
                    <col style="width:7%">
                    <!-- No. -->
                    <col style="width:2%">
                    <!-- Signature of Payee -->
                    <col>
                </colgroup>
                <tr>
                    <td colspan="14" style="border:none;padding:2pt 4pt;">
                        We acknowledge receipt of cash shown opposite our name as full compensation for services
                        rendered for the period covered.
                    </td>
                    <td colspan="3" style="border-left:1pt solid #000;border-top:1pt solid #000;padding:2pt 4pt;">
                        No. of Calendar Days:
                    </td>
                </tr>
            </table>
            <!-- content -->
            <table class="t-8"
                style="width:100%;border-collapse:collapse;table-layout:fixed;border-top:1pt solid #000;">
                <colgroup>
                    <!-- No -->
                    <col style="width:2%">
                    <!-- COSP Name -->
                    <col style="width:14.0%">
                    <!-- Contract Ref No. -->
                    <col style="width:7.5%">
                    <!-- DTR -->
                    <col style="width:5%">
                    <!-- Lost Hours Amount -->
                    <col style="width:5%">
                    <!-- SWA -->
                    <col style="width:2.8%">
                    <!-- ATM Account No. -->
                    <col style="width:8%">
                    <!-- Monthly Compensation -->
                    <col style="width:8%">
                    <!-- SSS -->
                    <col style="width:4.8%">
                    <!-- PhilHealth -->
                    <col style="width:5.5%">
                    <!-- HDMF -->
                    <col style="width:4.8%">
                    <!-- WHT -->
                    <col style="width:3.3%">
                    <!-- LOAN -->
                    <col style="width:3.3%">
                    <!-- Total Deductions -->
                    <col style="width:5.5%">
                    <!-- Net Amt Due -->
                    <col style="width:7%">
                    <!-- No. -->
                    <col style="width:2%">
                    <!-- Signature of Payee -->
                    <col>
                </colgroup>
                <thead>
                    <tr>
                        <td class="t-8" rowspan="3"
                            style="border-right:1pt solid #000;text-align:center;vertical-align:middle;">No.
                        </td>
                        <td class="t-8" rowspan="3"
                            style="border-right:1pt solid #000;padding:1pt;text-align:center;vertical-align:middle;">
                            COSP
                            NAME<br>(1)</td>
                        <td class="t-8" rowspan="3"
                            style="border-right:1pt solid #000;padding:1pt;text-align:center;vertical-align:middle;">
                            Contract
                            Ref No.<br>(2)</td>
                        <td class="t-8" rowspan="3"
                            style="border-right:1pt solid #000;padding:1pt;text-align:center;vertical-align:middle;overflow:hidden;word-break:break-word;">
                            DTR<br>(3)<br><span style="font-size:7pt;">(LH=Absent Hours+Tardiness+Undertime)</span></td>
                        <td class="t-8" rowspan="3"
                            style="border-right:1pt solid #000;padding:1pt;text-align:center;vertical-align:middle;">
                            Lost
                            Hours<br>Amount in Peso</td>
                        <td class="t-8" rowspan="3"
                            style="border-right:1pt solid #000;padding:1pt;text-align:center;vertical-align:middle;">
                            SWA<br>(4)<br>(Y/N)</td>
                        <td class="t-8" rowspan="3"
                            style="border-right:1pt solid #000;padding:1pt;text-align:center;vertical-align:middle;">ATM
                            Account No.<br>(5)</td>
                        <td class="t-8" rowspan="3"
                            style="border-right:1pt solid #000;padding:1pt;text-align:center;vertical-align:middle;">
                            Monthly<br>Compensation<br>(Php)<br>(6)</td>
                        <td class="t-8" colspan="5"
                            style="border-right:1pt solid #000;padding:1pt;text-align:center;vertical-align:middle;">
                            Monthly
                            Deductions (Php)</td>
                        <td class="t-8" rowspan="3"
                            style="border-right:1pt solid #000;padding:1pt;text-align:center;vertical-align:middle;">
                            Total<br>Deductions<br>(Php)</td>
                        <td class="t-8" rowspan="3"
                            style="border-right:1pt solid #000;padding:1pt;text-align:center;vertical-align:middle;">Net
                            Amt Due <br> (Php)</td>
                        <td class="t-8" rowspan="3"
                            style="border-right:1pt solid #000;padding:1pt;text-align:center;vertical-align:middle;">No.
                        </td>
                        <td class="t-8" rowspan="3" style="padding:1pt;text-align:center;vertical-align:middle;">
                            Signature
                            of Payee</td>
                    </tr>
                    <tr>
                        <td class="t-8" colspan="3"
                            style="border:1pt solid #000;padding:1pt;text-align:center;vertical-align:middle;">Premiums
                        </td>
                        <td class="t-8" rowspan="2"
                            style="border:1pt solid #000;padding:1pt;text-align:center;vertical-align:middle;">WHT</td>
                        <td class="t-8" rowspan="2"
                            style="border:1pt solid #000;padding:1pt;text-align:center;vertical-align:middle;">LOAN</td>
                    </tr>
                    <tr>
                        <td class="t-8"
                            style="border:1pt solid #000;padding:1pt;text-align:center;vertical-align:middle;">SSS</td>
                        <td class="t-8"
                            style="border:1pt solid #000;padding:1pt;text-align:center;vertical-align:middle;">
                            Phil-Health</td>
                        <td class="t-8"
                            style="border:1pt solid #000;padding:1pt;text-align:center;vertical-align:middle;">HDMF</td>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(emp, idx) in sortedEmployees" :key="emp.id">
                        <td class="t-8"
                            style="border: 1pt solid #000;border-left:none;text-align:center;vertical-align:bottom;height:18pt !important;">
                            {{
                                idx +
                                1 }}</td>
                        <td class="t-8"
                            style="border: 1pt solid #000;border-right:1pt solid #000;padding:2pt 4pt;vertical-align:bottom;word-break:break-word;overflow:hidden;">
                            {{ emp.payee_name }}</td>
                        <td class="t-8"
                            style="border: 1pt solid #000;border-right:1pt solid #000;padding:2pt 4pt;text-align:center;vertical-align:bottom;overflow:hidden;">
                            {{ emp.contract_ref_no || '' }}</td>
                        <td class="t-7"
                            style="border: 1pt solid #000;border-right:1pt solid #000;padding:2pt 4pt;text-align:center;vertical-align:bottom;overflow:hidden;">
                            {{ formatLostHour(emp.lost_hour_minutes) }}</td>
                        <td style="border: 1pt solid #000;border-right:1pt solid #000;">&nbsp;</td>
                        <td class="t-8"
                            style="border: 1pt solid #000;border-right:1pt solid #000;padding:1pt;text-align:center;vertical-align:bottom;">
                            Y</td>
                        <td class="t-8"
                            style="border: 1pt solid #000;border-right:1pt solid #000;padding:2pt 4pt;text-align:center;vertical-align:bottom;overflow:hidden;">
                            {{ emp.atm_account_no || '' }}</td>
                        <td class="t-8"
                            style="border: 1pt solid #000;border-right:1pt solid #000;padding:2pt 6pt;text-align:right;vertical-align:bottom;">
                            {{
                                money(emp.monthly_compensation) }}</td>
                        <td class="t-8"
                            style="border: 1pt solid #000;border-right:1pt solid #000;padding:2pt 4pt;text-align:right;vertical-align:bottom;">
                            {{
                                money(emp.deduction_sss) }}</td>
                        <td class="t-8"
                            style="border: 1pt solid #000;border-right:1pt solid #000;padding:2pt 4pt;text-align:right;vertical-align:bottom;">
                            {{
                                money(emp.deduction_philhealth) }}</td>
                        <td class="t-8"
                            style="border: 1pt solid #000;border-right:1pt solid #000;padding:2pt 4pt;text-align:right;vertical-align:bottom;">
                            {{
                                money(emp.deduction_hdmf) }}</td>
                        <td style="border: 1pt solid #000;border-right:1pt solid #000;">&nbsp;</td>
                        <td style="border: 1pt solid #000;border-right:1pt solid #000;">&nbsp;</td>
                        <td style="border: 1pt solid #000;border-right:1pt solid #000;">&nbsp;</td>
                        <td style="border: 1pt solid #000;border-right:1pt solid #000;">&nbsp;</td>
                        <td style="border: 1pt solid #000;text-align: center;vertical-align:bottom;">{{ idx +
                            1 }}</td>
                        <td style="border: 1pt solid #000;border-right:none;text-align: center;">&nbsp;</td>
                    </tr>

                    <!-- Spacer rows fill remaining rows up to page minimum -->
                    <tr v-for="i in spacerRows" :key="`spacer-${i}`">
                        <td style="border-right:1pt solid #000;border-top:1pt solid #000;">&nbsp;</td>
                        <td style="border-right:1pt solid #000;border-top:1pt solid #000;">&nbsp;</td>
                        <td style="border-right:1pt solid #000;border-top:1pt solid #000;">&nbsp;</td>
                        <td style="border-right:1pt solid #000;border-top:1pt solid #000;">&nbsp;</td>
                        <td style="border-right:1pt solid #000;border-top:1pt solid #000;">&nbsp;</td>
                        <td style="border-right:1pt solid #000;border-top:1pt solid #000;">&nbsp;</td>
                        <td style="border-right:1pt solid #000;border-top:1pt solid #000;">&nbsp;</td>
                        <td style="border-right:1pt solid #000;border-top:1pt solid #000;">&nbsp;</td>
                        <td style="border-right:1pt solid #000;border-top:1pt solid #000;">&nbsp;</td>
                        <td style="border-right:1pt solid #000;border-top:1pt solid #000;">&nbsp;</td>
                        <td style="border-right:1pt solid #000;border-top:1pt solid #000;">&nbsp;</td>
                        <td style="border-right:1pt solid #000;border-top:1pt solid #000;">&nbsp;</td>
                        <td style="border-right:1pt solid #000;border-top:1pt solid #000;">&nbsp;</td>
                        <td style="border-right:1pt solid #000;border-top:1pt solid #000;">&nbsp;</td>
                        <td style="border-right:1pt solid #000;border-top:1pt solid #000;">&nbsp;</td>
                        <td style="border-right:1pt solid #000;border-top:1pt solid #000;">&nbsp;</td>
                        <td style="border-top:1pt solid #000;">&nbsp;</td>
                    </tr>
                    <!-- Totals row — no borders -->
                    <tr style="height:12pt;">
                        <td style="border-top: 1pt solid #000;border-bottom:1pt solid #000;">&nbsp;</td>
                        <td style="border-top: 1pt solid #000;border-bottom:1pt solid #000;">&nbsp;</td>
                        <td style="border-top: 1pt solid #000;border-bottom:1pt solid #000;">&nbsp;</td>
                        <td style="border-top: 1pt solid #000;border-bottom:1pt solid #000;">&nbsp;</td>
                        <td style="border-top: 1pt solid #000;border-bottom:1pt solid #000;">&nbsp;</td>
                        <td style="border-top: 1pt solid #000;border-bottom:1pt solid #000;">&nbsp;</td>
                        <td class="bold t-8"
                            style="border-top: 1pt solid #000;border-bottom:1pt solid #000;text-align:right;vertical-align:bottom;">
                            TOTAL</td>
                        <td class="bold t-8"
                            style="text-align:right;vertical-align:bottom;border-top:1pt solid #000;border-bottom:1pt solid #000;">
                            {{
                                money(totalMonthlyComp) }}</td>
                        <td class="bold t-8"
                            style="text-align:right;vertical-align:bottom;border-top:1pt solid #000;border-bottom:1pt solid #000;">
                            {{
                                money(totalSSS) }}</td>
                        <td class="bold t-8"
                            style="text-align:right;vertical-align:bottom;border-top:1pt solid #000;border-bottom:1pt solid #000;">
                            {{
                                money(totalPhilHealth) }}</td>
                        <td class="bold t-8"
                            style="text-align:right;vertical-align:bottom;border-top:1pt solid #000;border-bottom:1pt solid #000;">
                            {{
                                money(totalHDMF) }}</td>
                        <td style="border-top: 1pt solid #000;border-bottom:1pt solid #000">&nbsp;</td>
                        <td style="border-top: 1pt solid #000;border-bottom:1pt solid #000">&nbsp;</td>
                        <td style="border-top: 1pt solid #000;border-bottom:1pt solid #000">&nbsp;</td>
                        <td style="border-top: 1pt solid #000;border-bottom:1pt solid #000">&nbsp;</td>
                        <td style="border-top: 1pt solid #000;border-bottom:1pt solid #000">&nbsp;</td>
                        <td style="border-top: 1pt solid #000;border-bottom:1pt solid #000">&nbsp;</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Signatures -->
        <table class="t-8" style="width:100%;border-collapse:collapse;table-layout:fixed;margin-top:20pt;">
            <colgroup>
                <!-- No -->
                <col style="width:2%">
                <!-- COSP Name -->
                <col style="width:14.0%">
                <!-- Contract Ref No. -->
                <col style="width:7.5%">
                <!-- DTR -->
                <col style="width:5%">
                <!-- Lost Hours Amount -->
                <col style="width:5%">
                <!-- SWA -->
                <col style="width:2.8%">
                <!-- ATM Account No. -->
                <col style="width:8%">
                <!-- Monthly Compensation -->
                <col style="width:8%">
                <!-- SSS -->
                <col style="width:4.8%">
                <!-- PhilHealth -->
                <col style="width:5.5%">
                <!-- HDMF -->
                <col style="width:4.8%">
                <!-- WHT -->
                <col style="width:3.3%">
                <!-- LOAN -->
                <col style="width:3.3%">
                <!-- Total Deductions -->
                <col style="width:5.5%">
                <!-- Net Amt Due -->
                <col style="width:7%">
                <!-- No. -->
                <col style="width:2%">
                <!-- Signature of Payee -->
            </colgroup>
            <!-- ROW 1 -->
            <tr>
                <td colspan="6"
                    style="padding:6pt 3pt;vertical-align:top;border-bottom: 1pt solid #000;border-right: 1pt solid #000;font-size: 7pt;">
                    <strong>A.</strong> <span> I hereby VERIFY and CERTIFY that the services as stated are duly rendered
                        after due examination and verification of the submitted DTR and Statement of Work
                        Accomplished</span>
                </td>
                <td colspan="6"
                    style="padding:6pt 3pt;vertical-align:top;border-bottom: 1pt solid #000;border-right: 1pt solid #000;font-size: 7pt;">
                    <strong>C.</strong> <span> I hereby CERTIFY that funds are available and that cash is on
                        hand/available in the treasury to cover the payment of the above-stated amount.</span>
                </td>
                <td colspan="5" rowspan="3" style="padding:8pt 0 2pt;">
                    <div style="display: flex;flex-direction:column;justify-content:center;align-items: center;">
                        <div style="border-bottom: 1pt solid #000;width: 76%;height:18pt;font-size: 7pt;">
                            <strong>E.</strong> <span> I hereby CERTIFY that I have disbursed the total amount of</span>
                        </div>
                        <div style="border-bottom: 1pt solid #000;width: 76%;height: 14pt;">&nbsp;</div>
                        <div style="padding: 2pt 8pt;font-size: 7.5pt;display: flex;">
                            <p>(₱__________)</p>
                            <p>&nbsp;covering the salaries/wages of the
                                employees
                                listed in the attached
                                payroll, in accordance with law and regulations.</p>
                        </div>
                    </div>

                    <div
                        style="display: flex;justify-content: space-between;font-size: 7pt;padding: 3pt 8pt; margin-top: 22pt;">
                        <div style="width: 40%; border-top: 1pt solid #000;text-align: center;">
                            <span>Disbursing Officer</span>
                        </div>
                        <div style="width: 40%; border-top: 1pt solid #000;text-align: center;">
                            <span>Date</span>
                        </div>
                    </div>
                </td>
            </tr>
            <!-- ROW 2 -->
            <tr>
                <td colspan="6"
                    style="padding:16pt 8pt 8pt;vertical-align:bottom;border-bottom: 1pt solid #000;border-right: 1pt solid #000;font-size: 7pt;">
                    <div style="display: flex;justify-content: space-between;align-items: flex-end;">
                        <div style="text-align: center; vertical-align: bottom; width: 40%;">
                            <p :style="signatoryNameStyle(sig('A').nameUnderline)">{{ sig('A').name }}</p>
                            <span style="white-space:pre-line;">{{ sig('A').titleDisplay }}</span>
                            <span v-if="sig('A').office" style="display:block;">{{ sig('A').office }}</span>
                        </div>
                        <div style="text-align: center;border-top: 1pt solid #000;width:35%;vertical-align: bottom;">
                            Date
                        </div>
                    </div>
                </td>
                <td colspan="6"
                    style="padding:16pt 8pt 8pt;vertical-align:bottom;border-bottom: 1pt solid #000;border-right: 1pt solid #000;font-size: 7pt;">
                    <div style="display: flex;justify-content: space-between;align-items: flex-end;">
                        <div style="text-align: center; vertical-align: bottom; width: 40%;">
                            <p :style="signatoryNameStyle(sig('C').nameUnderline)">{{ sig('C').name }}</p>
                            <span style="white-space:pre-line;">{{ sig('C').titleDisplay }}</span>
                            <span v-if="sig('C').office" style="display:block;">{{ sig('C').office }}</span>
                        </div>
                        <div style="text-align: center;border-top: 1pt solid #000;width:35%;vertical-align: bottom;">
                            Date
                        </div>
                    </div>
                </td>
            </tr>
            <!-- ROW 3 -->
            <tr>
                <td colspan="6" style="padding:3pt;vertical-align: top;border-right: 1pt solid #000;font-size: 7pt;">
                    <strong>B.</strong> <span> I hereby CERTIFY that the supporting documents are complete <br> and that
                        the changes to appropriation/allotment are lawful and under <br> my direct supervision.</span>
                </td>
                <td colspan="6" style="padding:3pt;vertical-align: top;border-right: 1pt solid #000;font-size: 7pt;">
                    <strong>D.</strong> <span>Approved for payment.</span>
                </td>
            </tr>
            <!-- ROW 4 -->
            <tr>
                <td colspan="6"
                    style="padding:10pt 8pt 8pt;vertical-align:bottom;border-right: 1pt solid #000;font-size: 7pt;">
                    <div style="display: flex;justify-content: space-between;align-items: flex-end;">
                        <div style="text-align: center; vertical-align: bottom;width: 40%;">
                            <p :style="signatoryNameStyle(sig('B').nameUnderline)">{{ sig('B').name }}</p>
                            <span style="white-space:pre-line;">{{ sig('B').titleDisplay }}</span>
                            <span v-if="sig('B').office" style="display:block;">{{ sig('B').office }}</span>
                        </div>
                        <div style="text-align: center;border-top: 1pt solid #000;width:35%;vertical-align: bottom;">
                            Date
                        </div>
                    </div>
                </td>
                <td colspan="6"
                    style="padding:10pt 8pt 8pt;vertical-align:bottom;border-right: 1pt solid #000;font-size: 7pt;">
                    <div style="display: flex;justify-content: space-between;align-items: flex-end;">
                        <div style="text-align: center; vertical-align: bottom;width: 40%;">
                            <p :style="signatoryNameStyle(sig('D').nameUnderline)">{{ sig('D').name }}</p>
                            <span style="white-space:pre-line;">{{ sig('D').titleDisplay }}</span>
                            <span v-if="sig('D').office" style="display:block;">{{ sig('D').office }}</span>
                        </div>
                        <div style="text-align: center;border-top: 1pt solid #000;width:35%;vertical-align: bottom;">
                            Date
                        </div>
                    </div>
                </td>

            </tr>
        </table>

    </div>
    <span style="font-size: 7pt; font-style: italic;">effective September 8, 2025</span>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    voucher: {
        type: Object,
        required: true,
    },
    employees: {
        type: Array,
        default: () => [],
    },
    signatories: {
        type: Array,
        default: () => [],
    },
});

const sig = (part) => {
    const found = props.signatories.find((s) => s.part === part);
    const s = found ?? { name: '', title: [], office: '' };
    const titles = Array.isArray(s.title) ? s.title : (s.title ? [s.title] : []);
    return {
        ...s,
        nameUnderline: s.name_underline !== false,
        titleDisplay: titles.join('\n'),
    };
};

function signatoryNameStyle(nameUnderline) {
    return `display:block;min-height:12pt;font-weight:600;${nameUnderline ? 'border-bottom:1pt solid #000;' : ''}`;
}

const sortedEmployees = computed(() =>
    [...props.employees].sort((left, right) => {
        const leftOrder = employeeListSortOrder(left);
        const rightOrder = employeeListSortOrder(right);

        if (leftOrder !== null || rightOrder !== null) {
            if (leftOrder === null) return 1;
            if (rightOrder === null) return -1;
            if (leftOrder !== rightOrder) return leftOrder - rightOrder;
        }

        return Number(right.monthly_compensation ?? 0) - Number(left.monthly_compensation ?? 0);
    })
);

const totalMonthlyComp = computed(() => props.employees.reduce((s, e) => s + Number(e.monthly_compensation ?? 0), 0));
const totalSSS = computed(() => props.employees.reduce((s, e) => s + Number(e.deduction_sss ?? 0), 0));
const totalPhilHealth = computed(() => props.employees.reduce((s, e) => s + Number(e.deduction_philhealth ?? 0), 0));
const totalHDMF = computed(() => props.employees.reduce((s, e) => s + Number(e.deduction_hdmf ?? 0), 0));

const periodLabel = computed(() => {
    const from = props.voucher.date_from ? new Date(props.voucher.date_from) : null;
    const to = props.voucher.date_to ? new Date(props.voucher.date_to) : null;
    if (!from && !to) return props.voucher.fiscal_year ?? '';
    const fmt = (d, opts) => d.toLocaleDateString('en-US', opts);
    if (
        from && to &&
        from.getMonth() === to.getMonth() &&
        from.getFullYear() === to.getFullYear()
    ) {
        const month = fmt(from, { month: 'long' });
        const year = from.getFullYear();
        const dayFrom = String(from.getDate()).padStart(2, '0');
        const dayTo = String(to.getDate()).padStart(2, '0');
        return `${month} ${dayFrom}-${dayTo}, ${year}`;
    }
    const fmtFull = (d) => fmt(d, { month: 'long', day: '2-digit', year: 'numeric' });
    if (from && to) return `${fmtFull(from)} - ${fmtFull(to)}`;
    if (from) return fmtFull(from);
    return fmtFull(to);
});

const ROWS_PER_PAGE = 15;
const spacerRows = computed(() => Math.max(0, ROWS_PER_PAGE - sortedEmployees.value.length));

const explanation = props.voucher.explanation || props.voucher.particulars_description || null;

const money = (val) => {
    if (!val && val !== 0) return '\u00a0';
    return new Intl.NumberFormat('en-PH', {
        style: 'currency',
        currency: 'PHP',
        minimumFractionDigits: 2,
    }).format(Number(val));
};

function employeeListSortOrder(employee) {
    const sortOrder = Number(employee?.sort_order ?? employee?.sortOrder);

    return Number.isFinite(sortOrder) && sortOrder > 0 ? sortOrder : null;
}

const formatLostHour = (minutes) => {
    const totalMinutes = Number(minutes ?? 0);

    if (!Number.isFinite(totalMinutes) || totalMinutes <= 0) {
        return '\u00a0';
    }

    const wholeMinutes = Math.floor(totalMinutes);
    const hours = Math.floor(wholeMinutes / 60);
    const remainingMinutes = wholeMinutes % 60;
    const hourLabel = hours === 1 ? 'hr' : 'hrs';
    const minuteLabel = remainingMinutes === 1 ? 'min' : 'mins';

    if (hours && remainingMinutes) {
        return `${hours} ${hourLabel} & ${remainingMinutes} ${minuteLabel}`;
    }

    if (hours) {
        return `${hours} ${hourLabel}`;
    }

    return `${remainingMinutes} ${minuteLabel}`;
};
</script>
