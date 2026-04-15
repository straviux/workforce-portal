<template>
    <Select v-model="internalValue" :options="options" optionLabel="name" optionValue="id" :placeholder="placeholder"
        :loading="loading" :disabled="loading || !responsibilityCenterId" filter class="w-full"
        @change="handleChange" />
</template>

<script setup>
import { ref, watch, onMounted } from 'vue';
import axios from 'axios';

const props = defineProps({
    modelValue: { type: [Number, String, null], default: null },
    responsibilityCenterId: { type: [Number, String, null], default: null },
    accountCode: { type: String, default: null },
    placeholder: { type: String, default: 'Select Particular' },
});

const emit = defineEmits(['update:modelValue', 'change']);

const options = ref([]);
const loading = ref(false);
const internalValue = ref(props.modelValue);

// Sync parent → internal (e.g. when form is reset from outside)
watch(() => props.modelValue, (val) => {
    internalValue.value = val;
});

// When RC changes, capture the current saved value BEFORE any reactive side-effects,
// then load options and restore if possible.
// We do NOT watch(internalValue) for emitting — that path causes async _skipEmit races.
// User-initiated changes are handled via @change on the Select element below.
watch(() => props.responsibilityCenterId, (id) => {
    options.value = [];
    const savedValue = props.modelValue; // capture synchronously before any flush
    if (id) {
        fetchOptions(id, savedValue);
    } else {
        internalValue.value = null;
        emit('update:modelValue', null);
    }
});

onMounted(() => {
    if (props.responsibilityCenterId) fetchOptions(props.responsibilityCenterId, props.modelValue);
});

async function fetchOptions(id, savedValue = null) {
    loading.value = true;
    try {
        const res = await axios.get(`/api/responsibility-centers/${id}/particulars`);
        options.value = res.data?.data || res.data || [];
        // Try match by id first, then fall back to account_code
        const byId = savedValue != null ? options.value.find(o => o.id === savedValue) : null;
        const byCode = !byId && props.accountCode ? options.value.find(o => o.account_code === props.accountCode) : null;
        const match = byId || byCode || null;
        if (match) {
            internalValue.value = match.id;
            emit('update:modelValue', match.id);
            emit('change', match);
        } else {
            internalValue.value = null;
            if (props.modelValue !== null) emit('update:modelValue', null);
        }
    } catch {
        options.value = [];
        internalValue.value = null;
        if (props.modelValue !== null) emit('update:modelValue', null);
    } finally {
        loading.value = false;
    }
}

// Called only on user interaction (PrimeVue @change never fires for programmatic v-model changes)
function handleChange(e) {
    emit('update:modelValue', e.value);
    const selected = options.value.find(o => o.id === e.value);
    emit('change', selected || null);
}
</script>
