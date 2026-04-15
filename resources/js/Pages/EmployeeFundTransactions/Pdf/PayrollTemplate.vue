<template>
    <div style="display:flex;flex-direction:column;min-height:90vh;">

        <!-- GOVERNMENT HEADER -->
        <div
            style="position:relative;display:flex;flex-direction:column;align-items:center;justify-content:center;padding:8pt 4pt;">
            <img :src="'/images/pgp-logo.svg'" alt="PGP Logo"
                style="position:absolute;left:35%;top:50%;transform:translateY(-50%);width:54pt;height:auto;" />
            <p class="t-9">Republic of the Philippines</p>
            <p class="t-9">Provincial Government of Palawan</p>
            <p class="t-9">Puerto Princesa City</p>
            <p class="bold t-11" style="margin-top: 14pt !important;">PAYROLL</p>
        </div>

        <!-- Payee info -->
        <div style="padding:4pt 0;">
            <span class="bold t-10">AGENCY: {{ voucher.payee_name }}</span>
        </div>
        <div v-if="voucher.office" style="padding:2pt 0 6pt 0;">
            <span class="t-10">OFFICE: {{ voucher.office }}</span>
        </div>
        <div v-if="voucher.fiscal_year" style="padding:2pt 0 6pt 0;">
            <span class="bold t-10">For Fiscal Year: {{ voucher.fiscal_year }}</span>
        </div>

        <!-- Explanation -->
        <div v-if="explanation" style="padding:4pt 0;text-align:center;">
            <span class="t-10 blk" v-safe-html="explanation"></span>
        </div>

        <!-- TABLE -->
        <div style="flex:1;display:flex;flex-direction:column;">

            <!-- Header row -->
            <div class="dv-row b-l b-r" style="border-top:1pt solid #000;">
                <div class="bold t-11"
                    style="width:24pt;border-right:1pt solid #000;display:flex;align-items:center;justify-content:center;padding:2pt;">
                    No.</div>
                <div class="bold t-11"
                    style="flex:2;border-right:1pt solid #000;display:flex;align-items:center;justify-content:center;padding:2pt;">
                    Name</div>
                <div class="bold t-11"
                    style="flex:2;border-right:1pt solid #000;display:flex;align-items:center;justify-content:center;padding:2pt;">
                    Address</div>
                <div class="bold t-11"
                    style="width:90pt;border-right:1pt solid #000;display:flex;align-items:center;justify-content:center;text-align:center;padding:2pt;">
                    Amount</div>
                <div class="bold t-11"
                    style="width:24pt;border-right:1pt solid #000;display:flex;align-items:center;justify-content:center;padding:2pt;">
                    No.</div>
                <div class="bold t-11"
                    style="flex:2;display:flex;align-items:center;justify-content:center;padding:2pt;">
                    Signature</div>
            </div>

            <!-- Employee rows -->
            <div v-for="(emp, idx) in sortedEmployees" :key="emp.id" class="dv-row b-l b-r" style="height:18pt;">
                <div class="t-10"
                    style="width:24pt;border-right:1pt solid #000;display:flex;align-items:center;justify-content:center;padding:2pt;">
                    {{ idx + 1 }}</div>
                <div class="bold t-10"
                    style="flex:2;border-right:1pt solid #000;display:flex;align-items:center;padding:2pt 4pt;">
                    {{ emp.payee_name }}
                </div>
                <div class="t-10"
                    style="flex:2;border-right:1pt solid #000;display:flex;align-items:center;padding:2pt 4pt;">
                    {{ emp.payee_address || '' }}
                </div>
                <div class="t-10"
                    style="width:90pt;border-right:1pt solid #000;display:flex;align-items:center;justify-content:flex-end;padding:2pt 6pt;">
                    {{ money(emp.monthly_compensation) }}
                </div>
                <div class="t-10"
                    style="width:24pt;border-right:1pt solid #000;display:flex;align-items:center;justify-content:center;padding:2pt;">
                    {{ idx + 1 }}</div>
                <div style="flex:2;">&nbsp;</div>
            </div>

            <!-- Spacer rows -->
            <div v-for="i in spacerRows" :key="`spacer-${i}`" class="dv-row b-l b-r" style="height:18pt;">
                <div style="width:24pt;border-right:1pt solid #000;">&nbsp;</div>
                <div style="flex:2;border-right:1pt solid #000;">&nbsp;</div>
                <div style="flex:2;border-right:1pt solid #000;">&nbsp;</div>
                <div style="width:90pt;border-right:1pt solid #000;">&nbsp;</div>
                <div style="width:24pt;border-right:1pt solid #000;">&nbsp;</div>
                <div style="flex:2;">&nbsp;</div>
            </div>

            <!-- GRAND TOTAL row -->
            <div class="dv-row b-l b-r" style="border-top:1pt solid #000;border-bottom:1pt solid #000;">
                <div style="width:24pt;border-right:1pt solid #000;">&nbsp;</div>
                <div style="flex:2;border-right:1pt solid #000;">&nbsp;</div>
                <div class="bold t-11"
                    style="flex:2;border-right:1pt solid #000;display:flex;align-items:center;justify-content:flex-end;padding:2pt 6pt;">
                    TOTAL</div>
                <div class="bold t-11"
                    style="width:90pt;border-right:1pt solid #000;display:flex;align-items:center;justify-content:flex-end;padding:2pt 6pt;">
                    {{ money(totalAmount) }}
                </div>
                <div style="width:24pt;border-right:1pt solid #000;">&nbsp;</div>
                <div style="flex:2;">&nbsp;</div>
            </div>

        </div>

        <!-- Signatures -->
        <div style="display:flex;gap:20pt;margin-top:20pt;padding-top:20pt;">
            <div style="flex:1;text-align:center;">
                <div style="border-top:0.5pt solid #000;padding-top:3pt;">
                    <span class="t-9">Prepared by</span>
                </div>
            </div>
            <div style="flex:1;text-align:center;">
                <div style="border-top:0.5pt solid #000;padding-top:3pt;">
                    <span class="t-9">Certified Correct</span>
                </div>
            </div>
            <div style="flex:1;text-align:center;">
                <div style="border-top:0.5pt solid #000;padding-top:3pt;">
                    <span class="t-9">Approved by</span>
                </div>
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
});

const explanation = props.voucher.explanation || props.voucher.particulars_description || null;

const sortedEmployees = computed(() =>
    [...props.employees].sort((a, b) => (a.payee_name ?? '').localeCompare(b.payee_name ?? ''))
);

const ROWS_PER_PAGE = 20;
const spacerRows = computed(() => Math.max(0, ROWS_PER_PAGE - sortedEmployees.value.length));

const totalAmount = computed(() =>
    props.employees.reduce((s, e) => s + Number(e.monthly_compensation ?? 0), 0)
);

const money = (val) => {
    if (!val && val !== 0) return '\u00a0';
    return new Intl.NumberFormat('en-PH', {
        style: 'currency',
        currency: 'PHP',
        minimumFractionDigits: 2,
    }).format(Number(val));
};
</script>
