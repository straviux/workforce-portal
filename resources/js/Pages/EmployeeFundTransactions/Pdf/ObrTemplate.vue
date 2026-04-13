<template>
    <div style="display:flex;flex-direction:column;min-height:93vh;border:1pt solid #000;margin-top:28pt;">

        <!-- GOVERNMENT HEADER -->
        <div style="position:relative;display:flex;flex-direction:column;align-items:center;justify-content:center;
                    border-bottom:1pt solid #000;padding:8pt 4pt;min-height:58pt;">
            <p class="bold t-12">Republic of the Philippines</p>
            <p class="bold t-12">PROVINCIAL GOVERNMENT OF PALAWAN</p>
            <p class="t-11">OFFICE OF THE GOVERNOR</p>
        </div>

        <!-- MAIN CONTENT -->
        <div style="display:flex;flex-direction:column;flex:1;">

            <!-- TITLE ROW -->
            <div class="dv-row">
                <div class="bold t-12" style="flex:1;border-right:1pt solid #000;display:flex;align-items:center;justify-content:center;padding:4pt;">
                    OBLIGATION REQUEST
                </div>
                <div class="t-9" style="width:200pt;display:flex;align-items:center;padding:2pt;">
                    No.&nbsp;&nbsp;<span class="bold t-10">{{ voucher.obr_no || '' }}</span>
                </div>
            </div>

            <!-- PAYEE -->
            <div class="dv-row">
                <div class="t-9" style="width:70pt;border-right:1pt solid #000;display:flex;align-items:center;padding:2pt;">Payee:</div>
                <div class="bold t-11" style="flex:1;border-right:1pt solid #000;display:flex;align-items:center;padding:2pt;">
                    {{ voucher.payee_name || ph }}
                </div>
                <div style="width:200pt;">&nbsp;</div>
            </div>

            <!-- EMPLOYEE ID (COS only) -->
            <div v-if="voucher.employee_type === 'contract_of_service'" class="dv-row">
                <div class="t-9" style="width:70pt;border-right:1pt solid #000;display:flex;align-items:center;padding:2pt;">Employee ID:</div>
                <div class="t-11" style="flex:1;border-right:1pt solid #000;display:flex;align-items:center;padding:2pt;">
                    {{ voucher.employee_id || ph }}
                </div>
                <div style="width:200pt;">&nbsp;</div>
            </div>

            <!-- OFFICE -->
            <div class="dv-row">
                <div class="t-9" style="width:70pt;border-right:1pt solid #000;display:flex;align-items:center;padding:2pt;">Office:</div>
                <div class="t-11" style="flex:1;border-right:1pt solid #000;display:flex;align-items:center;padding:2pt;">
                    {{ voucher.office || ph }}
                </div>
                <div style="width:200pt;">&nbsp;</div>
            </div>

            <!-- ADDRESS -->
            <div class="dv-row">
                <div class="t-9" style="width:70pt;border-right:1pt solid #000;display:flex;align-items:center;padding:2pt;">Address:</div>
                <div class="bold t-11" style="flex:1;border-right:1pt solid #000;display:flex;align-items:center;padding:2pt;">
                    {{ voucher.payee_address || ph }}
                </div>
                <div style="width:200pt;">&nbsp;</div>
            </div>

            <!-- COLUMN HEADERS -->
            <div class="dv-row">
                <div class="t-9" style="width:70pt;border-right:1pt solid #000;display:flex;align-items:center;padding:2pt;">Responsibility Center:</div>
                <div class="bold" style="flex:1;border-right:1pt solid #000;display:flex;align-items:center;justify-content:center;padding:2pt;">PARTICULARS</div>
                <div class="t-9" style="width:50pt;border-right:1pt solid #000;display:flex;align-items:center;justify-content:center;padding:2pt;">F.P.P</div>
                <div class="t-9" style="width:50pt;border-right:1pt solid #000;display:flex;align-items:center;justify-content:center;text-align:center;padding:2pt;">Account Code</div>
                <div class="t-9" style="width:100pt;display:flex;align-items:center;justify-content:center;padding:2pt 8pt;">Amount</div>
            </div>

            <!-- VALUES -->
            <div style="display:flex;min-height:30pt;">
                <div class="t-10" style="width:70pt;border-right:1pt solid #000;display:flex;align-items:center;justify-content:center;text-align:center;padding:2pt;">
                    {{ voucher.responsibility_center || ph }}
                </div>
                <div class="bold t-11" style="flex:1;border-right:1pt solid #000;display:flex;align-items:center;justify-content:center;padding:2pt;">
                    {{ voucher.particulars_name || ph }}
                </div>
                <div style="width:50pt;border-right:1pt solid #000;">&nbsp;</div>
                <div class="t-9" style="width:50pt;border-right:1pt solid #000;display:flex;align-items:center;padding:2pt;">
                    {{ voucher.account_code || '' }}
                </div>
                <div class="t-11" style="width:100pt;display:flex;align-items:center;justify-content:flex-end;padding:2pt 8pt;">
                    {{ money(voucher.amount) }}
                </div>
            </div>

            <!-- PARTICULARS DESCRIPTION (Quill HTML) -->
            <div v-if="desc" style="display:flex;min-height:30pt;">
                <div style="width:70pt;border-right:1pt solid #000;">&nbsp;</div>
                <div class="t-11 blk" style="flex:1;border-right:1pt solid #000;line-height:1.3;text-align:center;" v-safe-html="desc"></div>
                <div style="width:50pt;border-right:1pt solid #000;">&nbsp;</div>
                <div style="width:50pt;border-right:1pt solid #000;">&nbsp;</div>
                <div style="width:100pt;">&nbsp;</div>
            </div>

            <!-- FLEX SPACER -->
            <div style="flex:1;display:flex;min-height:20pt;">
                <div style="width:70pt;border-right:1pt solid #000;">&nbsp;</div>
                <div style="display:flex;flex:1;">&nbsp;</div>
                <div style="width:50pt;border-right:1pt solid #000;">&nbsp;</div>
                <div style="width:50pt;border-right:1pt solid #000;">&nbsp;</div>
                <div style="width:100pt;">&nbsp;</div>
            </div>

        </div>

        <!-- BOTTOM SECTION -->
        <div>

            <!-- TOTAL ROW -->
            <div style="display:flex;align-items:stretch;border-top:1pt solid #000;">
                <div style="width:70pt;">&nbsp;</div>
                <div style="display:flex;flex:1;">&nbsp;</div>
                <div class="bold" style="width:50pt;border-right:1pt solid #000;display:flex;align-items:center;justify-content:center;padding:2pt;">TOTAL</div>
                <div style="width:50pt;border-right:1pt solid #000;">&nbsp;</div>
                <div class="t-11" style="width:100pt;display:flex;align-items:center;justify-content:flex-end;padding:2pt 8pt;">
                    {{ money(voucher.amount) }}
                </div>
            </div>

            <!-- CERTIFICATION ROW -->
            <div style="display:flex;align-items:stretch;border-top:1pt solid #000;min-height:52pt;">
                <div style="width:20pt;height:20pt;border-right:1pt solid #000;border-bottom:1pt solid #000;display:flex;align-items:flex-start;justify-content:center;padding-top:4pt;">
                    <span class="bold t-12">A</span>
                </div>
                <div style="width:285pt;border-right:1pt solid #000;padding:4pt;display:flex;flex-direction:column;">
                    <span class="bold">Certified</span>
                    <div style="display:flex;align-items:flex-start;margin-top:8pt;margin-bottom:4pt;margin-left:4pt;">
                        <div style="border:1pt solid #000;width:14pt;height:14pt;flex-shrink:0;margin-right:4pt;"></div>
                        <span class="t-9">Allotment obligated for the purpose as indicated above</span>
                    </div>
                    <div style="display:flex;align-items:flex-start;margin-top:4pt;margin-left:4pt;">
                        <div style="border:1pt solid #000;width:14pt;height:14pt;flex-shrink:0;margin-right:4pt;"></div>
                        <span class="t-9">Supporting documents completed</span>
                    </div>
                </div>
                <!-- Signature section -->
                <div style="flex:1;border-right:1pt solid #000;padding:4pt;display:flex;flex-direction:column;">
                    <div style="margin-top:24pt;text-align:center;border-top:0.5pt solid #000;">
                        <span class="t-9">Budget Officer / Authorized Official</span>
                    </div>
                </div>
                <!-- Date obligated -->
                <div style="width:120pt;padding:4pt;display:flex;flex-direction:column;">
                    <span class="t-9">Date:</span>
                    <span class="t-10 bold" style="margin-top:4pt;">{{ voucher.date_obligated || '' }}</span>
                    <span class="t-8" style="margin-top:8pt;">Fiscal Year: {{ voucher.fiscal_year || '' }}</span>
                </div>
            </div>

        </div>

    </div>
</template>

<script setup>
const props = defineProps({
    voucher: {
        type: Object,
        required: true,
    },
});

const ph = '\u00a0';

const desc = props.voucher.particulars_description || null;

const money = (val) => {
    if (!val && val !== 0) return '\u00a0';
    return new Intl.NumberFormat('en-PH', {
        style: 'currency',
        currency: 'PHP',
        minimumFractionDigits: 2,
    }).format(Number(val));
};
</script>
