<template>
    <div style="display:flex;flex-direction:column;min-height:95vh;border:1pt solid #000;margin-top:28pt;">

        <!-- GOVERNMENT HEADER -->
        <div style="position:relative;display:flex;flex-direction:column;align-items:center;justify-content:center;
                    border-bottom:1pt solid #000;padding:8pt 4pt;min-height:58pt;">
            <p style="font-weight:bold;font-size:12pt;text-align:center;">Republic of the Philippines</p>
            <p style="font-weight:bold;font-size:12pt;text-align:center;">PROVINCIAL GOVERNMENT OF PALAWAN</p>
            <p style="font-size:11pt;text-align:center;">OFFICE OF THE GOVERNOR</p>
        </div>

        <!-- MAIN CONTENT -->
        <div style="display:flex;flex-direction:column;flex:1;">

            <!-- Title row -->
            <div class="dv-row">
                <div class="bold t-12 center" style="flex:1;border-right:1pt solid #000;display:flex;align-items:center;justify-content:center;padding:4pt;">
                    DISBURSEMENT VOUCHER
                </div>
                <div class="t-9" style="width:100pt;display:flex;align-items:center;padding:2pt;">
                    No.&nbsp;&nbsp;<span class="bold t-10">{{ voucher.dv_no || '' }}</span>
                </div>
            </div>

            <!-- Mode of Payment -->
            <div class="dv-row">
                <div class="t-9" style="width:76pt;border-right:1pt solid #000;display:flex;align-items:center;padding:2pt;">Mode of Payment:</div>
                <div style="flex:1;display:flex;align-items:center;gap:12pt;padding:4pt 6pt;">
                    <div style="display:flex;align-items:center;gap:4pt;">
                        <div style="width:14pt;height:14pt;border:1pt solid #000;flex-shrink:0;"></div>
                        <span class="t-10">Check</span>
                    </div>
                    <div style="display:flex;align-items:center;gap:4pt;">
                        <div style="width:14pt;height:14pt;border:1pt solid #000;flex-shrink:0;"></div>
                        <span class="t-10">Cash</span>
                    </div>
                    <div style="display:flex;align-items:center;gap:4pt;">
                        <div style="width:14pt;height:14pt;border:1pt solid #000;flex-shrink:0;"></div>
                        <span class="t-10">ATM — {{ voucher.atm_account_no || '' }}</span>
                    </div>
                </div>
            </div>

            <!-- Payee row -->
            <div class="dv-row" style="min-height:36pt;">
                <div class="t-9" style="width:76pt;border-right:1pt solid #000;display:flex;align-items:center;padding:2pt;">Payee:</div>
                <div class="bold t-11" style="flex:1;border-right:1pt solid #000;display:flex;align-items:center;padding:2pt;">
                    {{ voucher.payee_name || ph }}
                </div>
                <div style="width:132pt;border-right:1pt solid #000;display:flex;flex-direction:column;">
                    <div style="min-height:28pt;border-bottom:1pt solid #000;display:flex;padding:2pt;">
                        <div>
                            <span class="t-8">Employee ID</span>
                            <p class="t-10 bold" style="margin-top:2pt;">{{ voucher.employee_id || '' }}</p>
                        </div>
                    </div>
                    <div style="min-height:28pt;display:flex;padding:2pt;">
                        <span class="t-8">Responsibility Center: {{ voucher.responsibility_center || '' }}</span>
                    </div>
                </div>
                <div style="width:100pt;display:flex;flex-direction:column;">
                    <div style="min-height:28pt;border-bottom:1pt solid #000;display:flex;padding:2pt;">
                        <div>
                            <span class="t-8">OBR No.</span>
                            <p class="t-10 bold" style="margin-top:2pt;">{{ voucher.obr_no || '' }}</p>
                        </div>
                    </div>
                    <div style="min-height:28pt;display:flex;padding:2pt;">
                        <span class="t-8">Acct Code: {{ voucher.account_code || '' }}</span>
                    </div>
                </div>
            </div>

            <!-- Address row -->
            <div class="dv-row" style="min-height:36pt;">
                <div class="t-9" style="width:76pt;border-right:1pt solid #000;display:flex;align-items:center;padding:2pt;">Address:</div>
                <div class="bold t-11" style="flex:1;border-right:1pt solid #000;display:flex;align-items:center;padding:2pt;">
                    {{ voucher.payee_address || ph }}
                </div>
                <div style="width:132pt;border-right:1pt solid #000;padding:2pt;">
                    <span class="t-8">Office/Unit: {{ voucher.office || '' }}</span>
                </div>
                <div style="width:100pt;display:flex;padding:2pt;">
                    <div>
                        <span class="t-8">Date Obligated</span>
                        <p class="t-9" style="margin-top:2pt;">{{ voucher.date_obligated || '' }}</p>
                    </div>
                </div>
            </div>

            <!-- Column headers: EXPLANATION | AMOUNT -->
            <div class="dv-row">
                <div class="bold t-10 center" style="flex:1;border-right:1pt solid #000;display:flex;align-items:center;justify-content:center;padding:4pt;">EXPLANATION</div>
                <div class="bold t-10 center" style="width:100pt;display:flex;align-items:center;justify-content:center;padding:4pt;">AMOUNT</div>
            </div>

            <!-- Explanation content -->
            <div style="display:flex;min-height:10pt;">
                <div style="flex:1;border-right:1pt solid #000;">&nbsp;</div>
                <div style="width:100pt;">&nbsp;</div>
            </div>

            <div style="display:flex;min-height:30pt;">
                <div class="t-11 blk center" style="flex:1;border-right:1pt solid #000;line-height:1.3;text-align:center;" v-safe-html="explanation || ph"></div>
                <div class="t-11" style="width:100pt;display:flex;align-items:center;justify-content:flex-end;padding:2pt 8pt;">
                    {{ money(voucher.amount) }}
                </div>
            </div>

            <!-- Contract ref (COS only) -->
            <div v-if="voucher.contract_ref_no" style="display:flex;min-height:18pt;">
                <div class="t-10" style="flex:1;border-right:1pt solid #000;display:flex;align-items:center;justify-content:center;padding:2pt;">
                    Contract Ref: {{ voucher.contract_ref_no }}
                </div>
                <div style="width:100pt;">&nbsp;</div>
            </div>

            <!-- FLEX SPACER -->
            <div style="flex:1;display:flex;min-height:20pt;">
                <div style="flex:1;border-right:1pt solid #000;">&nbsp;</div>
                <div style="width:100pt;">&nbsp;</div>
            </div>

        </div>

        <!-- BOTTOM SECTION -->
        <div>

            <!-- TOTAL row -->
            <div class="dv-row" style="border-top:1pt solid #000;">
                <div class="bold t-11" style="flex:1;border-right:1pt solid #000;display:flex;align-items:center;justify-content:flex-end;padding:2pt 8pt;">TOTAL</div>
                <div class="bold t-11" style="width:100pt;display:flex;align-items:center;justify-content:flex-end;padding:2pt 8pt;">
                    {{ money(voucher.amount) }}
                </div>
            </div>

            <!-- Certification A + B -->
            <div class="dv-row">
                <div style="width:20pt;height:20pt;border-right:1pt solid #000;border-bottom:1pt solid #000;display:flex;align-items:flex-start;justify-content:center;padding-top:4pt;">
                    <span class="bold t-10">A</span>
                </div>
                <div style="width:268pt;border-right:1pt solid #000;padding:4pt;display:flex;flex-direction:column;">
                    <span class="bold">Certified</span>
                    <div style="display:flex;align-items:flex-start;margin-top:8pt;margin-bottom:4pt;margin-left:4pt;">
                        <div style="border:1pt solid #000;width:14pt;height:14pt;flex-shrink:0;margin-right:4pt;"></div>
                        <span class="t-9">Allotment obligated for the purpose as indicated above</span>
                    </div>
                    <div style="display:flex;align-items:flex-start;margin-top:8pt;margin-left:4pt;">
                        <div style="border:1pt solid #000;width:14pt;height:14pt;flex-shrink:0;margin-right:4pt;"></div>
                        <span class="t-9">Supporting documents completed</span>
                    </div>
                    <div style="margin-top:20pt;border-top:0.5pt solid #000;padding-top:3pt;text-align:center;">
                        <span class="t-9">Budget Officer / Authorized Official</span>
                    </div>
                </div>
                <div style="width:20pt;height:20pt;border-right:1pt solid #000;border-bottom:1pt solid #000;display:flex;align-items:flex-start;justify-content:center;padding-top:4pt;">
                    <span class="bold t-10">B</span>
                </div>
                <div style="flex:1;padding:4pt;display:flex;flex-direction:column;">
                    <span class="bold">Certified</span>
                    <div style="display:flex;align-items:flex-start;margin-top:8pt;margin-left:4pt;">
                        <div style="border:1pt solid #000;width:14pt;height:14pt;flex-shrink:0;margin-right:4pt;"></div>
                        <span class="t-9">Funds Available</span>
                    </div>
                    <div style="margin-top:20pt;border-top:0.5pt solid #000;padding-top:3pt;text-align:center;">
                        <span class="t-9">Accountant / Finance Officer</span>
                    </div>
                </div>
            </div>

            <!-- Received by -->
            <div style="display:flex;align-items:stretch;border-top:1pt solid #000;min-height:50pt;">
                <div style="flex:1;border-right:1pt solid #000;padding:4pt;">
                    <span class="bold t-9">Received by:</span>
                    <div style="margin-top:20pt;border-top:0.5pt solid #000;text-align:center;">
                        <span class="t-9">Signature over Printed Name</span>
                    </div>
                </div>
                <div style="flex:1;padding:4pt;">
                    <span class="t-9">Date Received: ___________________</span>
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

const explanation = props.voucher.explanation || props.voucher.particulars_description || null;

const money = (val) => {
    if (!val && val !== 0) return '\u00a0';
    return new Intl.NumberFormat('en-PH', {
        style: 'currency',
        currency: 'PHP',
        minimumFractionDigits: 2,
    }).format(Number(val));
};
</script>
