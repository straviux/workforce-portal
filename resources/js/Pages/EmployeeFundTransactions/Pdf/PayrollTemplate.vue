<template>
    <div style="display:flex;flex-direction:column;min-height:90vh;">

        <!-- GOVERNMENT HEADER -->
        <div style="display:flex;flex-direction:column;align-items:center;justify-content:center;padding:8pt 4pt;min-height:58pt;">
            <p class="bold t-12">GENERAL PAYROLL</p>
            <p class="t-11">PROVINCIAL GOVERNMENT OF PALAWAN</p>
            <p class="t-11">OFFICE OF THE GOVERNOR</p>
        </div>

        <!-- Payee info -->
        <div style="padding:4pt 0;">
            <span class="bold t-10">Payee: {{ voucher.payee_name }}</span>
        </div>
        <div v-if="voucher.office" style="padding:2pt 0 6pt 0;">
            <span class="t-10">Office/Unit: {{ voucher.office }}</span>
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
                <div class="bold t-11" style="width:30pt;border-right:1pt solid #000;display:flex;align-items:center;justify-content:center;padding:2pt;">No.</div>
                <div class="bold t-11" style="width:200pt;border-right:1pt solid #000;display:flex;align-items:center;justify-content:center;padding:2pt;">Employee Name</div>
                <div class="bold t-11" style="width:80pt;border-right:1pt solid #000;display:flex;align-items:center;justify-content:center;text-align:center;padding:2pt;">Monthly Compensation</div>
                <div class="bold t-11" style="width:60pt;border-right:1pt solid #000;display:flex;align-items:center;justify-content:center;padding:2pt;">SSS</div>
                <div class="bold t-11" style="width:60pt;border-right:1pt solid #000;display:flex;align-items:center;justify-content:center;padding:2pt;">PhilHealth</div>
                <div class="bold t-11" style="width:60pt;border-right:1pt solid #000;display:flex;align-items:center;justify-content:center;padding:2pt;">HDMF</div>
                <div class="bold t-11" style="width:80pt;border-right:1pt solid #000;display:flex;align-items:center;justify-content:center;padding:2pt;">Net Pay</div>
                <div class="bold t-11" style="flex:1;display:flex;align-items:center;justify-content:center;padding:2pt;">Signature</div>
            </div>

            <!-- Employee row -->
            <div class="dv-row b-l b-r">
                <div class="t-10" style="width:30pt;border-right:1pt solid #000;display:flex;align-items:center;justify-content:center;padding:2pt;">1</div>
                <div class="bold t-11" style="width:200pt;border-right:1pt solid #000;display:flex;align-items:center;padding:2pt 4pt;">
                    {{ voucher.payee_name }}
                </div>
                <div class="t-11" style="width:80pt;border-right:1pt solid #000;display:flex;align-items:center;justify-content:flex-end;padding:2pt 6pt;">
                    {{ money(voucher.monthly_compensation) }}
                </div>
                <div class="t-11" style="width:60pt;border-right:1pt solid #000;display:flex;align-items:center;justify-content:flex-end;padding:2pt 6pt;">
                    {{ money(voucher.deduction_sss) }}
                </div>
                <div class="t-11" style="width:60pt;border-right:1pt solid #000;display:flex;align-items:center;justify-content:flex-end;padding:2pt 6pt;">
                    {{ money(voucher.deduction_philhealth) }}
                </div>
                <div class="t-11" style="width:60pt;border-right:1pt solid #000;display:flex;align-items:center;justify-content:flex-end;padding:2pt 6pt;">
                    {{ money(voucher.deduction_hdmf) }}
                </div>
                <div class="bold t-11" style="width:80pt;border-right:1pt solid #000;display:flex;align-items:center;justify-content:flex-end;padding:2pt 6pt;">
                    {{ money(netPay) }}
                </div>
                <div style="flex:1;">&nbsp;</div>
            </div>

            <!-- GRAND TOTAL row -->
            <div class="dv-row b-l b-r">
                <div style="width:30pt;border-right:1pt solid #000;">&nbsp;</div>
                <div style="width:200pt;border-right:1pt solid #000;">&nbsp;</div>
                <div style="width:80pt;border-right:1pt solid #000;">&nbsp;</div>
                <div style="width:60pt;border-right:1pt solid #000;">&nbsp;</div>
                <div style="width:60pt;border-right:1pt solid #000;">&nbsp;</div>
                <div class="bold t-11" style="width:60pt;border-right:1pt solid #000;display:flex;align-items:center;justify-content:center;padding:2pt;">TOTAL</div>
                <div class="bold t-11" style="width:80pt;border-right:1pt solid #000;display:flex;align-items:center;justify-content:flex-end;padding:2pt 6pt;">
                    {{ money(netPay) }}
                </div>
                <div style="flex:1;">&nbsp;</div>
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
});

const explanation = props.voucher.explanation || props.voucher.particulars_description || null;

const netPay = computed(() => {
    const comp = Number(props.voucher.monthly_compensation || 0);
    const sss = Number(props.voucher.deduction_sss || 0);
    const ph = Number(props.voucher.deduction_philhealth || 0);
    const hdmf = Number(props.voucher.deduction_hdmf || 0);
    return comp - sss - ph - hdmf;
});

const money = (val) => {
    if (!val && val !== 0) return '\u00a0';
    return new Intl.NumberFormat('en-PH', {
        style: 'currency',
        currency: 'PHP',
        minimumFractionDigits: 2,
    }).format(Number(val));
};
</script>
