<template>
    <Dialog :visible="show" @update:visible="val => emit('update:show', val)" modal
        :pt="{ root: { class: 'ios-dialog-root' }, mask: { class: 'ios-dialog-mask' } }">
        <template #container>
            <div class="ios-modal w-105 max-w-[92vw]" :style="modalStyle">

                <div class="ios-nav-bar" @pointerdown="onDragStart">
                    <button class="ios-nav-btn ios-nav-cancel" @click="emit('update:show', false)">
                        <AppIcon name="X" />
                    </button>
                    <span class="ios-nav-title">Delete Transaction</span>
                    <button class="ios-nav-btn ios-nav-action" :disabled="actionState?.disabled"
                        :class="actionState?.morphClass" @click="emit('confirm-delete')">
                        <AppIcon v-if="actionState?.state === 'success'" name="Check" class="text-emerald-500" />
                        <AppIcon v-else name="Trash2" class="text-red-500" />
                        <span v-if="actionState?.label">{{ actionState.label }}</span>
                    </button>
                </div>

                <div class="ios-body">
                    <div class="text-center mt-8 pb-4">
                        <p class="text-sm text-surface-400 mb-3">
                            This action cannot be undone. The following transaction will be permanently removed.
                        </p>
                        <div class="p-3 text-center">
                            <p class="font-bold mono text-gray-700  text-xl text-shadow-md">{{ payeeName }}</p>
                            <p class="mono text-gray-700   ">{{ transactionId }}</p>
                            <p class="mono text-gray-700   ">{{ date }}</p>
                        </div>

                    </div>
                </div>

            </div>
        </template>
    </Dialog>
</template>

<script setup>
import { ref, computed } from 'vue';

defineProps({
    show: Boolean,
    transactionId: { type: String, default: '' },
    payeeName: { type: String, default: '' },
    date: { type: String, default: '' },
    // Parent-owned morph state (the delete request lives in the parent):
    // { state, label, disabled, morphClass }. Idle label is '' so the
    // button stays icon-only until processing/success shows its text.
    actionState: { type: Object, default: null },
});

const emit = defineEmits(['update:show', 'confirm-delete']);

const dragOffset = ref({ x: 0, y: 0 });
const dragStart = ref(null);

const modalStyle = computed(() => ({
    transform: `translate(${dragOffset.value.x}px, ${dragOffset.value.y}px)`,
}));

function onDragStart(e) {
    if (e.target.closest('button')) return;
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
