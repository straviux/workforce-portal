<template>
    <Select
        v-model="internalValue"
        :options="options"
        optionLabel="name"
        optionValue="id"
        :placeholder="placeholder"
        :loading="loading"
        :disabled="loading || !responsibilityCenterId"
        filter
        class="w-full"
        @change="handleChange"
    />
</template>

<script setup>
import { ref, watch } from 'vue';
import axios from 'axios';

const props = defineProps({
    modelValue: { type: [Number, String, null], default: null },
    responsibilityCenterId: { type: [Number, String, null], default: null },
    placeholder: { type: String, default: 'Select Particular' },
});

const emit = defineEmits(['update:modelValue', 'change']);

const options = ref([]);
const loading = ref(false);
const internalValue = ref(props.modelValue);

watch(() => props.modelValue, (val) => {
    internalValue.value = val;
});

watch(internalValue, (val) => {
    emit('update:modelValue', val);
});

watch(() => props.responsibilityCenterId, (id) => {
    options.value = [];
    internalValue.value = null;
    emit('update:modelValue', null);
    if (id) fetchOptions(id);
});

async function fetchOptions(id) {
    loading.value = true;
    try {
        const res = await axios.get(`/api/responsibility-centers/${id}/particulars`);
        options.value = res.data?.data || res.data || [];
    } catch {
        options.value = [];
    } finally {
        loading.value = false;
    }
}

function handleChange(e) {
    const selected = options.value.find(o => o.id === e.value);
    emit('change', selected || null);
}
</script>
