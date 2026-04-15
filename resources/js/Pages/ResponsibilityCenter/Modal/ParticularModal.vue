<template>
    <Dialog :visible="show" @update:visible="val => emit('update:show', val)" modal
        :pt="{ root: { class: 'ios-dialog-root' }, mask: { class: 'ios-dialog-mask' } }">
        <template #container>
            <div ref="elModal" class="ios-modal w-140 max-w-[95vw]" :style="modalStyle">

                <div class="ios-nav-bar" @pointerdown="onDragStart">
                    <button class="ios-nav-btn ios-nav-cancel" @click="emit('update:show', false)">
                        <i class="pi pi-times"></i> Cancel
                    </button>
                    <span class="ios-nav-title">{{ mode === 'edit' ? `Edit Particular` : `New Particular` }}</span>
                    <button class="ios-nav-btn ios-nav-action" :disabled="saving" @click="submit">
                        {{ saving ? `Saving…` : `Save` }}
                    </button>
                </div>

                <div class="ios-body">
                    <div class="ios-section pb-4">
                        <p class="ios-section-label">Particular Details</p>
                        <div class="ios-card p-4 space-y-3">
                            <div class="ios-form-group">
                                <label class="ios-label">Name <span class="text-red-500">*</span></label>
                                <InputText v-model="form.name" placeholder="e.g. Salaries and Wages" class="w-full" />
                                <span v-if="errors.name" class="ios-hint ios-error">{{ errors.name }}</span>
                            </div>
                            <div class="ios-form-group">
                                <label class="ios-label">Account Code <span class="text-red-500">*</span></label>
                                <InputText v-model="form.account_code" placeholder="e.g. 5010-001" class="w-full" />
                                <span v-if="errors.account_code" class="ios-hint ios-error">{{ errors.account_code
                                    }}</span>
                            </div>
                            <div class="ios-form-group">
                                <label class="ios-label">Allotment <span class="text-red-500">*</span></label>
                                <InputNumber v-model="form.allotment" placeholder="e.g. 50000.00" :minFractionDigits="2"
                                    :maxFractionDigits="2" mode="decimal" class="w-full" inputClass="w-full"
                                    prefix="₱" />
                                <span v-if="errors.allotment" class="ios-hint ios-error">{{ errors.allotment }}</span>
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <div class="ios-form-group">
                                    <label class="ios-label">Date Approved</label>
                                    <DatePicker v-model="form.date_approved" class="w-full" dateFormat="M dd, yy"
                                        showIcon iconDisplay="input" showButtonBar />
                                </div>
                                <div class="ios-form-group">
                                    <label class="ios-label">Date Expired</label>
                                    <DatePicker v-model="form.date_expired" class="w-full" dateFormat="M dd, yy"
                                        showIcon iconDisplay="input" showButtonBar />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </template>
    </Dialog>
</template>

<script setup>
import { ref, reactive, computed, watch } from 'vue';
import axios from 'axios';
import { useToast } from 'primevue/usetoast';

const props = defineProps({
    show: Boolean,
    rc: { type: Object, default: null },
    particular: { type: Object, default: null },
    mode: { type: String, default: 'create' },
});

const emit = defineEmits(['update:show', 'saved']);

const toast = useToast();
const saving = ref(false);
const errors = ref({});

const defaultForm = () => ({
    name: '',
    account_code: '',
    allotment: null,
    date_approved: null,
    date_expired: null,
});
const form = reactive(defaultForm());

watch(() => props.show, (val) => {
    if (val) {
        errors.value = {};
        if (props.mode === 'edit' && props.particular) {
            form.name = props.particular.name ?? '';
            form.account_code = props.particular.account_code ?? '';
            form.allotment = props.particular.allotment ? parseFloat(props.particular.allotment) : null;
            form.date_approved = props.particular.date_approved ? new Date(props.particular.date_approved) : null;
            form.date_expired = props.particular.date_expired ? new Date(props.particular.date_expired) : null;
        } else {
            Object.assign(form, defaultForm());
        }
        dragOffset.value = { x: 0, y: 0 };
    }
});

async function submit() {
    errors.value = {};
    if (!form.name) errors.value.name = 'Name is required';
    if (!form.account_code) errors.value.account_code = 'Account Code is required';
    if (!form.allotment) errors.value.allotment = 'Allotment is required';
    if (Object.keys(errors.value).length) return;

    const payload = {
        name: form.name,
        account_code: form.account_code,
        allotment: form.allotment,
        date_approved: form.date_approved ? new Date(form.date_approved).toISOString().slice(0, 10) : null,
        date_expired: form.date_expired ? new Date(form.date_expired).toISOString().slice(0, 10) : null,
    };

    saving.value = true;
    try {
        if (props.mode === 'edit') {
            await axios.put(`/api/responsibility-centers/${props.rc.id}/particulars/${props.particular.id}`, payload);
            toast.add({ severity: 'success', summary: 'Success', detail: 'Particular updated', life: 3000 });
        } else {
            await axios.post(`/api/responsibility-centers/${props.rc.id}/particulars`, payload);
            toast.add({ severity: 'success', summary: 'Success', detail: 'Particular created', life: 3000 });
        }
        emit('update:show', false);
        emit('saved');
    } catch (err) {
        const data = err.response?.data;
        if (data?.errors) {
            errors.value = Object.fromEntries(Object.entries(data.errors).map(([k, v]) => [k, v[0]]));
        } else {
            toast.add({ severity: 'error', summary: 'Error', detail: data?.message ?? 'Failed to save', life: 5000 });
        }
    } finally {
        saving.value = false;
    }
}

// Drag
const elModal = ref(null);
const dragOffset = ref({ x: 0, y: 0 });
const dragStart = ref(null);

const modalStyle = computed(() => ({
    transform: `translate(${dragOffset.value.x}px, ${dragOffset.value.y}px)`,
}));

function onDragStart(e) {
    if (e.target.closest('button, input, textarea, .p-inputtext, .p-datepicker, .p-inputnumber')) return;
    dragStart.value = { x: e.clientX - dragOffset.value.x, y: e.clientY - dragOffset.value.y };
    window.addEventListener('pointermove', onDragMove);
    window.addEventListener('pointerup', onDragEnd);
}

function onDragMove(e) {
    if (!dragStart.value) return;
    dragOffset.value = { x: e.clientX - dragStart.value.x, y: e.clientY - dragStart.value.y };
}

function onDragEnd() {
    dragStart.value = null;
    window.removeEventListener('pointermove', onDragMove);
    window.removeEventListener('pointerup', onDragEnd);
}
</script>

<style scoped>
:deep(.p-inputtext),
:deep(.p-select),
:deep(.p-datepicker .p-inputtext) {
    border-radius: 1rem;
}

:deep(.p-datepicker) {
    flex: 1;
}
</style>
