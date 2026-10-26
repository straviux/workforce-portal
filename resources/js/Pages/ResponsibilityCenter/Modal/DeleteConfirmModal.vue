<template>
    <Dialog :visible="show" @update:visible="val => emit('update:show', val)" modal
        :pt="{ root: { class: 'ios-dialog-root' }, mask: { class: 'ios-dialog-mask' } }">
        <template #container>
            <div ref="elModal" class="ios-modal w-105 max-w-[92vw]" :style="modalStyle">

                <div class="ios-nav-bar" @pointerdown="onDragStart">
                    <button class="ios-nav-btn ios-nav-cancel" @click="emit('update:show', false)">
                        <AppIcon name="X" /> Cancel
                    </button>
                    <span class="ios-nav-title">Confirm Delete</span>
                </div>

                <div class="ios-body">
                    <div class="ios-section">
                        <div class="ios-card text-center py-2">
                            <div class="flex justify-center mb-4">
                                <div
                                    class="w-14 h-14 rounded-full bg-red-100 dark:bg-red-900/30 flex items-center justify-center">
                                    <AppIcon name="Trash2" :size="24" class="text-red-500" />
                                </div>
                            </div>
                            <h3 class="text-base font-semibold mb-1">
                                {{ type === 'rc' ? `Delete Responsibility Center?` : `Delete Particular?` }}
                            </h3>
                            <p class="text-sm text-surface-400 mb-3">
                                {{ type === 'rc'
                                    ? `All particulars under this responsibility center will also be deleted. This action
                                cannot be undone.`
                                    : `This particular will be permanently removed. This action cannot be undone.` }}
                            </p>
                            <div v-if="targetName"
                                class="bg-surface-100 dark:bg-surface-700 rounded-xl p-3 text-left text-sm">
                                <p><span class="text-surface-400">Name:</span> <span class="font-semibold">{{ targetName
                                        }}</span></p>
                            </div>
                        </div>
                    </div>

                    <div class="ios-section">
                        <div class="flex gap-3">
                            <Button label="Cancel" severity="secondary" class="flex-1 rounded" outlined
                                :disabled="actionDisabled" @click="emit('update:show', false)" />
                            <Button :label="actionLabel" severity="danger" class="flex-1 rounded"
                                :loading="actionLoading" :disabled="actionDisabled" :class="actionMorphClass"
                                @click="emit('confirm')">
                                <template #icon><AppIcon v-if="actionState === 'success'" name="Check" /></template>
                            </Button>
                        </div>
                    </div>
                </div>

            </div>
        </template>
    </Dialog>
</template>

<script setup>
import { ref, computed } from 'vue';

// The delete request lives in the parent (ResponsibilityCenter/index.vue),
// which owns the useActionButton state and passes these primitives down —
// the modal never fakes completion itself. The Delete button had no idle
// icon, so the icon slot only renders the success check.
defineProps({
    show: Boolean,
    type: { type: String, default: 'rc' }, // 'rc' | 'particular'
    targetName: { type: String, default: '' },
    actionLabel: { type: String, default: 'Delete' },
    actionState: { type: String, default: 'idle' },
    actionLoading: { type: Boolean, default: false },
    actionDisabled: { type: Boolean, default: false },
    actionMorphClass: { type: String, default: '' },
});

const emit = defineEmits(['update:show', 'confirm']);

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
