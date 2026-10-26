<template>
    <Dialog :visible="show" @update:visible="val => emit('update:show', val)" modal
        :pt="{ root: { class: 'ios-dialog-root' }, mask: { class: 'ios-dialog-mask' } }">
        <template #container>
            <div ref="elModal" class="ios-modal w-140 max-w-[92vw]" :style="modalStyle">

                <div class="ios-nav-bar" @pointerdown="onDragStart">
                    <button class="ios-nav-btn ios-nav-cancel" @click="emit('update:show', false)">
                        <AppIcon name="X" />
                    </button>
                    <span class="ios-nav-title">Add Remarks</span>
                    <button class="ios-nav-btn ios-nav-action font-semibold" @click="handleSave"
                        :disabled="actionState?.disabled" :class="actionState?.morphClass"
                        v-tooltip="'Save Remarks'">
                        <AppIcon v-if="actionState?.state === 'success'" name="Check" class="text-emerald-500" />
                        <AppIcon v-else name="Save" class="text-emerald-500" />
                        <span v-if="actionState?.label">{{ actionState.label }}</span>
                    </button>
                </div>

                <div class="ios-body !pb-6">

                    <div class="ios-section" v-if="modelValue">
                        <div class="flex text-xs text-surface-400 uppercase tracking-wide mb-2">
                            <p class="w-1/4">Transaction: </p><span class="font-semibold text-surface-300">{{
                                modelValue.transaction_id
                                }}</span>
                        </div>
                        <div class="flex text-xs text-surface-400 uppercase tracking-wide mb-2">
                            <p class="w-1/4">Payee: </p><span class="font-semibold text-surface-300">{{
                                modelValue.payee_name
                            }}</span>
                        </div>
                    </div>

                    <div class="ios-section">
                        <p class="text-xs text-surface-400 uppercase tracking-wide mb-2">Remarks</p>
                        <Editor v-model="remarks" editorStyle="height: 180px">
                            <template #toolbar>
                                <span class="ql-formats">
                                    <button class="ql-bold"></button>
                                    <button class="ql-italic"></button>
                                    <button class="ql-underline"></button>
                                </span>
                                <span class="ql-formats">
                                    <button class="ql-list" value="ordered"></button>
                                    <button class="ql-list" value="bullet"></button>
                                </span>
                                <span class="ql-formats">
                                    <button class="ql-clean"></button>
                                </span>
                            </template>
                        </Editor>
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
    // Parent-owned morph state (the save request lives in the parent):
    // { state, label, disabled, morphClass }. Idle label is '' so the
    // button stays icon-only until processing/success shows its text.
    actionState: { type: Object, default: null },
});

const emit = defineEmits(['update:show', 'save']);

const remarks = ref('');

watch(() => props.modelValue, (val) => {
    remarks.value = val?.remarks || '';
}, { immediate: true });

watch(() => props.show, (val) => {
    if (val && props.modelValue) {
        remarks.value = props.modelValue.remarks || '';
    }
});

function handleSave() {
    emit('save', remarks.value);
}

const elModal = ref(null);
const dragOffset = ref({ x: 0, y: 0 });
const dragStart = ref(null);

const modalStyle = computed(() => ({
    transform: `translate(${dragOffset.value.x}px, ${dragOffset.value.y}px)`,
}));

function onDragStart(e) {
    if (e.target.closest('button, input, .p-editor')) return;
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
