<template>
    <Dialog :visible="show" @update:visible="val => emit('update:show', val)" modal
        :pt="{ root: { class: 'ios-dialog-root' }, mask: { class: 'ios-dialog-mask' } }">
        <template #container>
            <div ref="elModal" class="ios-modal w-120 max-w-[95vw]" :style="modalStyle">

                <div class="ios-nav-bar" @pointerdown="onDragStart">
                    <button class="ios-nav-btn ios-nav-cancel" @click="emit('update:show', false)">
                        <AppIcon name="X" />
                    </button>
                    <span class="ios-nav-title">{{ mode === 'edit' ? `Edit Responsibility Center` : `New Responsibility
                        Center` }}</span>
                    <button class="ios-nav-btn ios-nav-action" :disabled="saveAction.disabled"
                        :class="saveAction.morphClass" @click="submit">
                        {{ saveAction.label }}
                    </button>
                </div>

                <div class="ios-body">
                    <div class="ios-section pb-4">
                        <p class="ios-section-label">Details</p>
                        <div class="ios-card p-4 space-y-3">
                            <div class="ios-form-group">
                                <label class="ios-label">Code <span class="text-red-500">*</span></label>
                                <InputText v-model="form.code" placeholder="e.g. RC-001" class="w-full" />
                                <span v-if="errors.code" class="ios-hint ios-error">{{ errors.code }}</span>
                            </div>
                            <div class="ios-form-group">
                                <label class="ios-label">Name <span class="text-red-500">*</span></label>
                                <InputText v-model="form.name" placeholder="e.g. General Services Office"
                                    class="w-full" />
                                <span v-if="errors.name" class="ios-hint ios-error">{{ errors.name }}</span>
                            </div>
                            <div class="ios-form-group">
                                <label class="ios-label">Fiscal Year <span
                                        class="text-surface-400 font-normal">(optional)</span></label>
                                <InputText v-model="form.fiscal_year" placeholder="e.g. 2024-2025" class="w-full" />
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </template>
    </Dialog>
</template>

<script setup>
import { ref, reactive, computed, watch } from 'vue';
import axios from 'axios';
import { useToast } from 'primevue/usetoast';
import { useActionButton } from '@/Composables/useActionButton';

const props = defineProps({
    show: Boolean,
    rc: { type: Object, default: null },
    mode: { type: String, default: 'create' },
});

const emit = defineEmits(['update:show', 'saved']);

const toast = useToast();
// Wrapped in reactive() so template prop bindings unwrap the composable's
// refs (raw use renders class="dep __v_isRef" in script-setup SFCs).
const saveAction = reactive(useActionButton({ idleLabel: 'Save', processingLabel: 'Saving...', successLabel: 'Saved' }));
const errors = ref({});

const defaultForm = () => ({ code: '', name: '', fiscal_year: '' });
const form = reactive(defaultForm());

watch(() => props.show, (val) => {
    if (val) {
        errors.value = {};
        if (props.mode === 'edit' && props.rc) {
            form.code = props.rc.code ?? '';
            form.name = props.rc.name ?? '';
            form.fiscal_year = props.rc.fiscal_year ?? '';
        } else {
            Object.assign(form, defaultForm());
        }
        dragOffset.value = { x: 0, y: 0 };
    }
});

async function submit() {
    errors.value = {};
    if (!form.code) { errors.value.code = 'Code is required'; }
    if (!form.name) { errors.value.name = 'Name is required'; }
    if (Object.keys(errors.value).length) return;

    if (saveAction.state !== 'idle') return;
    try {
        await saveAction.run(async () => {
            try {
                if (props.mode === 'edit') {
                    await axios.put(`/api/responsibility-centers/${props.rc.id}`, form);
                    toast.add({ severity: 'success', summary: 'Success', detail: 'Responsibility Center updated', life: 3000 });
                } else {
                    await axios.post('/api/responsibility-centers', form);
                    toast.add({ severity: 'success', summary: 'Success', detail: 'Responsibility Center created', life: 3000 });
                }
            } catch (err) {
                const data = err.response?.data;
                if (data?.errors) {
                    errors.value = Object.fromEntries(Object.entries(data.errors).map(([k, v]) => [k, v[0]]));
                } else {
                    toast.add({ severity: 'error', summary: 'Error', detail: data?.message ?? 'Failed to save', life: 5000 });
                }
                throw err;
            }
        });
    } catch {
        return;
    }
    // Only after the full morph cycle (run resolves after success shows).
    emit('update:show', false);
    emit('saved');
}

// Drag
const elModal = ref(null);
const dragOffset = ref({ x: 0, y: 0 });
const dragStart = ref(null);

const modalStyle = computed(() => ({
    transform: `translate(${dragOffset.value.x}px, ${dragOffset.value.y}px)`,
}));

function onDragStart(e) {
    if (e.target.closest('button, input, textarea, .p-inputtext')) return;
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

<style scoped>
:deep(.p-inputtext),
:deep(.p-select) {
    border-radius: 1rem;
}
</style>
