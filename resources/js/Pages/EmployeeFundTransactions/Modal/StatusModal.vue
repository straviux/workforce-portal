<template>
    <Dialog :visible="show" @update:visible="val => emit('update:show', val)" modal
        :pt="{ root: { class: 'ios-dialog-root' }, mask: { class: 'ios-dialog-mask' } }">
        <template #container>
            <div ref="elModal" class="ios-modal w-115 max-w-[92vw]" :style="modalStyle">

                <div class="ios-nav-bar" @pointerdown="onDragStart">
                    <button class="ios-nav-btn ios-nav-cancel" @click="emit('update:show', false)">
                        <i class="pi pi-times"></i> Cancel
                    </button>
                    <span class="ios-nav-title">Update Status</span>
                    <button class="ios-nav-btn ios-nav-action font-semibold" @click="handleSave" :disabled="isSaving">
                        <i class="pi pi-check"></i> Save
                    </button>
                </div>

                <div class="ios-body">

                    <div class="ios-section" v-if="modelValue">
                        <p class="text-xs text-surface-400 uppercase tracking-wide mb-1">
                            Transaction: <span class="font-semibold text-surface-300">{{ modelValue.transaction_id }}</span>
                        </p>
                        <p class="text-xs text-surface-400">
                            Current status:
                            <Tag :value="modelValue.transaction_status" :severity="statusSeverity(modelValue.transaction_status)" class="capitalize ml-1" />
                        </p>
                    </div>

                    <div class="ios-section">
                        <label class="text-xs text-surface-400 uppercase tracking-wide mb-2 block">New Status</label>
                        <Select v-model="selectedStatus" :options="statusOptions" optionLabel="label" optionValue="value"
                            placeholder="Select status" class="w-full" />
                    </div>

                    <div class="ios-section">
                        <Button label="Update Status" icon="pi pi-check" class="w-full rounded"
                            :loading="isSaving" :disabled="isSaving || !selectedStatus" @click="handleSave" />
                    </div>

                </div>

            </div>
        </template>
    </Dialog>
</template>

<script setup>
import { ref, watch, computed } from 'vue';

const props = defineProps({
    show: Boolean,
    modelValue: { type: Object, default: null },
    statusOptions: {
        type: Array,
        default: () => [
            { label: 'Pending', value: 'pending' },
            { label: 'Approved', value: 'approved' },
            { label: 'Active', value: 'active' },
            { label: 'Denied', value: 'denied' },
            { label: 'Suspended', value: 'suspended' },
        ],
    },
    isSaving: { type: Boolean, default: false },
});

const emit = defineEmits(['update:show', 'save']);

const selectedStatus = ref(null);

watch(() => props.modelValue, (val) => {
    selectedStatus.value = val?.transaction_status || null;
}, { immediate: true });

watch(() => props.show, (val) => {
    if (val && props.modelValue) {
        selectedStatus.value = props.modelValue.transaction_status || null;
    }
});

function handleSave() {
    if (!selectedStatus.value) return;
    emit('save', selectedStatus.value);
}

function statusSeverity(status) {
    const map = {
        pending: 'warn',
        approved: 'success',
        active: 'info',
        denied: 'danger',
        suspended: 'secondary',
    };
    return map[status] || 'secondary';
}

const elModal = ref(null);
const dragOffset = ref({ x: 0, y: 0 });
const dragStart = ref(null);

const modalStyle = computed(() => ({
    transform: `translate(${dragOffset.value.x}px, ${dragOffset.value.y}px)`,
}));

function onDragStart(e) {
    if (e.target.closest('button, input, .p-select')) return;
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
