<template>
    <Dialog v-model:visible="visible" modal header="Select Office Head" :style="{ width: '28rem' }" :draggable="false"
        :closable="true" @hide="emit('update:show', false)">
        <p class="text-sm text-surface-500 mb-4">Multiple office heads are configured. Choose who will appear on this
            document.</p>

        <div class="space-y-2">
            <label v-for="oh in options" :key="oh.id"
                class="flex items-center gap-3 p-3 rounded-2xl cursor-pointer transition-colors" :class="selected?.id === oh.id
                    ? 'bg-primary-50 dark:bg-primary-900/20 ring-1 ring-primary-400'
                    : 'bg-surface-50 dark:bg-surface-800 hover:bg-surface-100 dark:hover:bg-surface-700'">
                <RadioButton v-model="selected" :value="oh" :inputId="`oh-${oh.id}`" />
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-surface-800 dark:text-surface-100 truncate">{{ oh.name }}</p>
                    <p class="text-xs text-surface-400 truncate">{{ oh.title }}</p>
                </div>
            </label>
        </div>

        <template #footer>
            <Button label="Cancel" severity="secondary" text class="rounded" @click="cancel" />
            <Button label="Continue" icon="pi pi-print" class="rounded" :disabled="!selected" @click="confirm" />
        </template>
    </Dialog>
</template>

<script setup>
import { ref, computed, watch } from 'vue';

const props = defineProps({
    show: { type: Boolean, default: false },
    options: { type: Array, default: () => [] },
});

const emit = defineEmits(['update:show', 'select']);

const visible = computed({
    get: () => props.show,
    set: (val) => emit('update:show', val),
});

const selected = ref(null);

watch(() => props.show, (val) => {
    if (val) selected.value = props.options[0] ?? null;
});

function cancel() {
    emit('update:show', false);
}

function confirm() {
    if (!selected.value) return;
    emit('select', selected.value);
    emit('update:show', false);
}
</script>
