<template>
    <div>
        <div v-for="(page, pageIndex) in paginatedEmployees" :key="`page-${pageIndex}`"
            :class="['pdf-page', page.isLast ? '' : 'break-after']"
            style="position:relative;display:flex;flex-direction:column;min-height:90vh;padding-bottom:18pt;">

            <!-- GOVERNMENT HEADER -->
            <div
                style="position:relative;display:flex;flex-direction:column;align-items:center;justify-content:center;padding:8pt 4pt;">
                <img :src="'/images/pgp-logo.svg'" alt="PGP Logo"
                    style="position:absolute;left:35%;top:35%;transform:translateY(-50%);width:54pt;height:auto;" />
                <p class="t-9">Republic of the Philippines</p>
                <p class="t-9">Provincial Government of Palawan</p>
                <p class="t-9">Puerto Princesa City</p>
                <p class="bold t-11" style="margin-top: 14pt !important;">PAYROLL</p>
            </div>

            <!-- Header details -->

            <div class="t-11" style="display:flex;align-items:self-start;gap:2pt;">
                <div style="width: 90px;">AGENCY:</div>
                <div class="bold">{{ payrollAgency }}</div>
            </div>
            <div class="t-11" style="display:flex;align-items:self-start;gap:2pt;margin-top:1pt">
                <div style="width: 90px;">OFFICE:</div>
                <div class="bold">{{ payrollOffice }}</div>
            </div>




            <!-- TABLE -->
            <div style="flex:1;display:flex;flex-direction:column;margin-top: 2pt;">
                <table style="width:100%;border-collapse:collapse;table-layout:fixed;">
                    <colgroup>
                        <col style="width:5%;">
                        <col style="width:28%;">
                        <col style="width:24%;">
                        <col style="width:13%;">
                        <col style="width:5%;">
                        <col style="width:25%;">
                    </colgroup>

                    <thead>
                        <tr>
                            <th class=" t-11" style="border:1pt solid #000;padding:8pt;text-align:center;vertical-align:middle;font-weight: normal;
                                width: 50pt;">
                                No.
                            </th>
                            <th class=" t-11"
                                style="border:1pt solid #000;padding:8pt;text-align:center;vertical-align:middle;font-weight: normal;">
                                Name
                            </th>
                            <th class=" t-11"
                                style="border:1pt solid #000;padding:8pt;text-align:center;vertical-align:middle;font-weight: normal;">
                                Address
                            </th>
                            <th class=" t-11"
                                style="border:1pt solid #000;padding:8pt;text-align:center;vertical-align:middle;font-weight: normal;">
                                Amount
                            </th>
                            <th class=" t-11"
                                style="border:1pt solid #000;padding:8pt;text-align:center;vertical-align:middle;font-weight: normal; width: 50pt;">
                                No.
                            </th>
                            <th class="t-11"
                                style="border:1pt solid #000;padding:8pt;text-align:center;vertical-align:middle;font-weight: normal;">
                                SIGNATURE/THUMBMARK
                            </th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr v-for="(emp, idx) in page.employees" :key="emp.id ?? `${pageIndex}-${idx}`">
                            <td class="t-10"
                                style="border:1pt solid #000;height:18pt;padding:4pt 4pt;text-align:center;vertical-align:middle;">
                                {{ page.startIndex + idx + 1 }}
                            </td>
                            <td class=" t-10"
                                style="border:1pt solid #000;height:18pt;padding:4pt 4pt;vertical-align:middle;">
                                {{ emp.payee_name }}
                            </td>
                            <td class="t-10"
                                style="border:1pt solid #000;height:18pt;padding:4pt 4pt;vertical-align:middle;text-align: center;">
                                {{ emp.payee_address || '' }}
                            </td>
                            <td class="t-10"
                                style="border:1pt solid #000;height:18pt;padding:4pt 6pt;text-align:right;vertical-align:middle;">
                                {{ money(employeePayrollAmount(emp)) }}
                            </td>
                            <td class="t-10"
                                style="border:1pt solid #000;height:18pt;padding:4pt;text-align:center;vertical-align:middle;">
                                {{ page.startIndex + idx + 1 }}
                            </td>
                            <td style="border:1pt solid #000;height:18pt;padding:4pt 4pt;vertical-align:middle;">&nbsp;
                            </td>
                        </tr>

                        <tr v-for="i in page.spacerRows" :key="`spacer-${pageIndex}-${i}`">
                            <td style="border:1pt solid #000;height:18pt;">&nbsp;</td>
                            <td style="border:1pt solid #000;height:18pt;">&nbsp;</td>
                            <td style="border:1pt solid #000;height:18pt;">&nbsp;</td>
                            <td style="border:1pt solid #000;height:18pt;">&nbsp;</td>
                            <td style="border:1pt solid #000;height:18pt;">&nbsp;</td>
                            <td style="border:1pt solid #000;height:18pt;">&nbsp;</td>
                        </tr>

                        <tr>
                            <td style="border:1pt solid #000;height:18pt;">&nbsp;</td>
                            <td class="bold t-11" colspan="2"
                                style="border:1pt solid #000;height:18pt;padding:2pt 6pt;vertical-align:middle;">
                                SUBTOTAL
                            </td>
                            <td class="bold t-11"
                                style="border:1pt solid #000;height:18pt;padding:2pt 6pt;text-align:right;vertical-align:middle;">
                                {{ money(page.subtotal) }}
                            </td>
                            <td style="border:1pt solid #000;height:18pt;">&nbsp;</td>
                            <td style="border:1pt solid #000;height:18pt;">&nbsp;</td>
                        </tr>

                        <tr v-if="page.isLast">
                            <td style="border:1pt solid #000;height:18pt;">&nbsp;</td>

                            <td class="bold t-11" colspan="2"
                                style="border:1pt solid #000;height:18pt;padding:2pt 6pt;vertical-align:middle;">
                                TOTAL
                            </td>
                            <td class="bold t-11"
                                style="border:1pt solid #000;height:18pt;padding:2pt 6pt;text-align:right;vertical-align:middle;">
                                {{ money(grandTotalAmount) }}
                            </td>
                            <td style="border:1pt solid #000;height:18pt;">&nbsp;</td>
                            <td style="border:1pt solid #000;height:18pt;">&nbsp;</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Signatures -->
            <div v-if="page.isLast" style="margin-top:22pt;padding-top:10pt; margin-left: 32pt;">
                <div style="display:grid;grid-template-columns:1fr 1fr;column-gap:26pt;row-gap:28pt;">
                    <div>
                        <div class="t-10" style="line-height:1.35;min-height:18pt;">
                            <p style="font-weight: 700;">A. CERTIFICATION</p>
                            <p class="t-9">This is to certify that this payroll contains the names of persons who
                                actually received
                                donations indicated above and are chargeable against the corresponding appropriate
                                provided thereof.</p>
                        </div>
                        <div style="margin-top:34pt;text-align:center;">
                            <p class="bold t-10" style="width:85%;margin:0 auto !important;padding-bottom:2pt;">
                                {{ formatSignatoryName(preparedBySignatory.name) }}
                            </p>
                            <p v-for="title in preparedBySignatory.titleDisplay" :key="`A-${title}`" class="t-10"
                                style="margin-top:1pt !important;white-space:pre-line;">
                                {{ title }}
                            </p>
                            <p v-if="preparedBySignatory.office" class="t-10" style="margin-top:1pt !important;">
                                {{ preparedBySignatory.office }}
                            </p>
                        </div>
                    </div>


                    <!-- Treasurer -->
                    <div>
                        <div class="t-10" style="line-height:1.35;min-height:18pt;">
                            <p style="font-weight: 700;">C. Cash Available</p>
                        </div>
                        <div style="margin-top:44pt;text-align:center;margin-left: -260pt;">
                            <p class="bold t-10" style="width:85%;margin:0 auto !important;padding-bottom:2pt;">
                                {{ formatSignatoryName(treasurerSignatory.name) }}
                            </p>
                            <p v-for="title in treasurerSignatory.titleDisplay" :key="`C-${title}`" class="t-9"
                                style="margin-top:1pt !important;white-space:pre-line;">
                                {{ title }}
                            </p>
                            <p v-if="treasurerSignatory.office" class="t-10" style="margin-top:1pt !important;">
                                {{ treasurerSignatory.office }}
                            </p>
                        </div>
                    </div>


                    <!-- Accountant -->
                    <div>
                        <div class="t-10" style="line-height:1.35;min-height:18pt;">
                            <p style="font-weight: 700;">B. CERTIFIED: Supporting Documents Valid, Proper, and Legal</p>
                        </div>
                        <div style="margin-top:24pt;text-align:center;">
                            <p class="bold t-10" style="width:85%;margin:0 auto !important;padding-bottom:2pt;">
                                {{ formatSignatoryName(accountantSignatory.name) }}
                            </p>
                            <p v-for="title in accountantSignatory.titleDisplay" :key="`B-${title}`" class="t-10"
                                style="margin-top:1pt !important;white-space:pre-line;">
                                {{ title }}
                            </p>
                            <p v-if="accountantSignatory.office" class="t-10" style="margin-top:1pt !important;">
                                {{ accountantSignatory.office }}
                            </p>
                        </div>
                    </div>

                    <!-- Approver/Governor -->
                    <!-- Accountant -->
                    <div>
                        <div class="t-10"
                            style="line-height:1.35;min-height:18pt; display: flex;width: 100%;align-items: flex-start;">
                            <p style="font-weight: 700;width: 38%;">D. APPROVED FOR PAYMENT:</p>
                            <p style="width: 35%;margin-top: -42pt !important;" class="t-8">CERTIFIED: Each employeee
                                whose name
                                appears above
                                has been
                                paid the amount indicated opposite his/her name

                            </p>
                        </div>
                        <div
                            style="margin-top:24pt;text-align:center; display: flex;width: 100%;align-items: flex-start;">

                            <div style="font-weight: 700;width: 38%;">
                                <p class="bold t-10" style="width:85%;margin:0 auto !important;padding-bottom:2pt;">
                                    {{ formatSignatoryName(approverSignatory.name) }}
                                </p>
                                <p v-for="title in approverSignatory.titleDisplay" :key="`D-${title}`" class="t-10"
                                    style="white-space:pre-line;">
                                    {{ title }}
                                </p>
                                <p v-if="approverSignatory.office" class="t-10" style="white-space:pre-line;">
                                    {{ approverSignatory.office }}
                                </p>

                            </div>
                            <div>____________________________________</div>
                        </div>
                    </div>


                </div>
            </div>

            <div class="t-8" style="position:absolute;left:0;bottom:0;width:100%;text-align:center;">
                Page {{ pageIndex + 1 }} of {{ paginatedEmployees.length }}
            </div>

        </div>
    </div>
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

const explanation = props.voucher.explanation || props.voucher.particulars_description || null;

const sortedEmployees = computed(() =>
    [...props.employees].sort((a, b) => (a.payee_name ?? '').localeCompare(b.payee_name ?? ''))
);

const EMPLOYEES_PER_PAGE = 10;
const MIN_FILLER_ROWS = 2;
const paginatedEmployees = computed(() => {
    const source = sortedEmployees.value;
    const shouldAddSpacerRows = source.length < 3;

    if (!source.length) {
        return [{
            employees: [],
            startIndex: 0,
            spacerRows: EMPLOYEES_PER_PAGE,
            subtotal: 0,
            isLast: true,
        }];
    }

    const pages = [];

    for (let index = 0; index < source.length; index += EMPLOYEES_PER_PAGE) {
        const employees = source.slice(index, index + EMPLOYEES_PER_PAGE);
        const isLast = index + EMPLOYEES_PER_PAGE >= source.length;
        const spacerRows = shouldAddSpacerRows
            ? Math.max(MIN_FILLER_ROWS, EMPLOYEES_PER_PAGE - employees.length)
            : 0;

        pages.push({
            employees,
            startIndex: index,
            spacerRows,
            subtotal: employees.reduce((sum, employee) => sum + employeePayrollAmount(employee), 0),
            isLast,
        });
    }

    return pages;
});

const grandTotalAmount = computed(() =>
    sortedEmployees.value.reduce((sum, employee) => sum + employeePayrollAmount(employee), 0)
);

const payrollOffice = computed(() => {
    const office = String(props.voucher.office ?? '').trim();
    const agency = String(props.voucher.agency ?? '').trim();

    return office || agency || '';
});

const payrollAgency = computed(() => {
    const agency = String(props.voucher.agency ?? '').trim();
    const office = String(props.voucher.office ?? '').trim();

    if (!agency || agency === office) {
        return '';
    }

    return agency;
});

function employeePayrollAmount(employee) {
    return Number(
        employee.amount
        ?? employee.employee_record?.amount
        ?? employee.employeeRecord?.amount
        ?? employee.monthly_compensation
        ?? 0
    );
}

function signatoryByPart(part) {
    const found = props.signatories.find((entry) => entry.part === part);
    const signatory = found ?? { name: '______________________________', title: [], office: '' };
    const titles = Array.isArray(signatory.title) ? signatory.title : (signatory.title ? [signatory.title] : []);

    return {
        ...signatory,
        titleDisplay: titles.filter(Boolean),
    };
}

const preparedBySignatory = computed(() => signatoryByPart('A'));
const accountantSignatory = computed(() => signatoryByPart('B'));
const treasurerSignatory = computed(() => signatoryByPart('C'));
const approverSignatory = computed(() => signatoryByPart('D'));

function formatSignatoryName(value) {
    const text = String(value ?? '').trim();
    return text ? text.toUpperCase() : '______________________________';
}

const money = (val) => {
    if (!val && val !== 0) return '\u00a0';
    return new Intl.NumberFormat('en-PH', {
        style: 'currency',
        currency: 'PHP',
        minimumFractionDigits: 2,
    }).format(Number(val));
};
</script>
