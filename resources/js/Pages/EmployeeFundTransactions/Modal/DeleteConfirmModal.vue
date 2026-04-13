<template>
    <Dialog :visible="show" @update:visible="val => emit('update:show', val)" modal
        :pt="{ root: { class: 'ios-dialog-root' }, mask: { class: 'ios-dialog-mask' } }">
        <template #container>
            <div ref="elModal" class="ios-modal w-105 max-w-[92vw]" :style="modalStyle">

                <div class="ios-nav-bar" @pointerdown="onDragStart">
                    <button class="ios-nav-btn ios-nav-cancel" @click="emit('update:show', false)">
                        <i class="pi pi-times"></i> Cancel
                    </button>
                    <span class="ios-nav-title">Delete Transaction</span>
                </div>

                <div class="ios-body">
                    <div class="ios-section">
                        <div class="ios-card text-center py-2">
                            <div class="flex justify-center mb-4">
                                <div class="w-14 h-14 rounded-full bg-red-100 dark:bg-red-900/30 flex items-center justify-center">
                                    <i class="pi pi-trash text-2xl text-red-500"></i>
                                </div>
                            </div>
                            <h3 class="text-base font-semibold mb-1">Delete Transaction?</h3>
                            <p class="text-sm text-surface-400 mb-3">
                                This action cannot be undone. The following transaction will be permanently removed.
                            </p>
                            <div class="bg-surface-100 dark:bg-surface-700 rounded-xl p-3 text-left space-y-1 text-sm">
                                <p><span class="text-surface-400">ID:</span> <span class="font-mono font-semibold">{{ transactionId }}</span></p>
                                <p v-if="payeeName"><span class="text-surface-400">Payee:</span> {{ payeeName }}</p>
                                <p v-if="date"><span class="text-surface-400">Date:</span> {{ date }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="ios-section">
                        <div class="flex gap-3">
                            <Button label="Cancel" severity="secondary" class="flex-1 rounded" outlined
                                :disabled="isDeleting" @click="emit('update:show', false)" />
                            <Button label="Delete" severity="danger" class="flex-1 rounded"
                                :loading="isDeleting" :disabled="isDeleting"
                                @click="emit('confirm-delete')" />
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
    isDeleting: { type: Boolean, default: false },
});

const emit = defineEmits(['update:show', 'confirm-delete']);

const elModal = ref(null);
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
