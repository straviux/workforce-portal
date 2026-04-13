<template>
    <Select
        v-model="internalValue"
        :options="options"
        optionLabel="name"
        optionValue="id"
        :placeholder="placeholder"
        :loading="loading"
        :disabled="loading"
        filter
        class="w-full"
        @change="handleChange"
    />
</template>

<script setup>
import { ref, watch, onMounted } from 'vue';
import axios from 'axios';

const props = defineProps({
    modelValue: { type: [Number, String, null], default: null },
    fiscalYear: { type: String, default: '' },
    placeholder: { type: String, default: 'Select Responsibility Center' },
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

watch(() => props.fiscalYear, () => {
    fetchOptions();
});

async function fetchOptions() {
    loading.value = true;
    try {
        const params = {};
        if (props.fiscalYear) params.fiscal_year = props.fiscalYear;
        const res = await axios.get('/api/responsibility-centers', { params });
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

onMounted(() => {
    fetchOptions();
});
</script>
