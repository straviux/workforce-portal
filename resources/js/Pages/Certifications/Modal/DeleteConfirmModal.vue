<template>
    <Dialog :visible="show" @update:visible="value => emit('update:show', value)" modal
        :pt="{ root: { class: 'ios-dialog-root' }, mask: { class: 'ios-dialog-mask' } }">
        <template #container>
            <div ref="elModal" class="ios-modal w-105 max-w-[92vw]" :style="modalStyle">
                <div class="ios-nav-bar" @pointerdown="onDragStart">
                    <button class="ios-nav-btn ios-nav-cancel" @click="emit('update:show', false)">
                        <AppIcon name="X" />
                    </button>
                    <span class="ios-nav-title">Delete Certification</span>
                    <button class="ios-nav-btn ios-nav-action" :disabled="actionDisabled"
                        :class="actionMorphClass" @click="emit('confirm-delete')">
                        <AppIcon v-if="actionState === 'success'" name="Check" />
                        <span>{{ actionLabel }}</span>
                    </button>
                </div>

                <div class="ios-body">
                    <div class="ios-section">
                        <div class="mt-8 pb-4 text-center">
                            <p class="text-sm text-surface-400 mb-3">
                                This action cannot be undone. The following transaction will be permanently removed.
                            </p>
                            <div class="bg-surface-100 dark:bg-surface-700 rounded-xl p-3 text-left text-sm space-y-1">
                                <p><span class="text-surface-400">Name:</span> <span class="font-semibold">{{
                                    certificationName || '—' }}</span></p>
                                <p><span class="text-surface-400">Issued:</span> <span class="font-semibold">{{
                                    issuedDate || '—' }}</span></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </Dialog>
</template>

<script setup>
import { computed, ref } from 'vue';

// The delete request lives in the parent (Certifications/index.vue), so
// the morph state is owned there and passed down here; this modal never
// fakes completion. actionLabel renders as the header button's text.
defineProps({
    show: Boolean,
    certificationName: { type: String, default: '' },
    issuedDate: { type: String, default: '' },
    actionLabel: { type: String, default: 'Delete' },
    actionDisabled: { type: Boolean, default: false },
    actionMorphClass: { type: String, default: '' },
    actionState: { type: String, default: 'idle' },
});

const emit = defineEmits(['update:show', 'confirm-delete']);

const elModal = ref(null);
const dragOffset = ref({ x: 0, y: 0 });
const dragStart = ref(null);

const modalStyle = computed(() => ({
    transform: `translate(${dragOffset.value.x}px, ${dragOffset.value.y}px)`,
}));

function onDragStart(event) {
    if (event.target.closest('button')) return;

    dragStart.value = {
        x: event.clientX - dragOffset.value.x,
        y: event.clientY - dragOffset.value.y,
    };

    window.addEventListener('pointermove', onDragMove);
    window.addEventListener('pointerup', onDragEnd);
}

function onDragMove(event) {
    if (!dragStart.value) return;

    dragOffset.value = {
        x: event.clientX - dragStart.value.x,
        y: event.clientY - dragStart.value.y,
    };
}

function onDragEnd() {
    dragStart.value = null;
    window.removeEventListener('pointermove', onDragMove);
    window.removeEventListener('pointerup', onDragEnd);
}
</script>