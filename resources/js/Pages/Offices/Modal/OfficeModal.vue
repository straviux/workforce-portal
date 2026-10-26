<template>
    <Dialog :visible="show" @update:visible="val => emit('update:show', val)" modal
        :pt="{ root: { class: 'ios-dialog-root' }, mask: { class: 'ios-dialog-mask' } }">
        <template #container>
            <div ref="elModal" class="ios-modal w-120 max-w-[95vw]" :style="modalStyle">

                <div class="ios-nav-bar" @pointerdown="onDragStart">
                    <button class="ios-nav-btn ios-nav-cancel" @click="emit('update:show', false)">
                        <AppIcon name="X" />
                    </button>
                    <span class="ios-nav-title">{{ modalTitle }}</span>
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
                                <label class="ios-label">Name <span class="text-red-500">*</span></label>
                                <InputText v-model="form.name" placeholder="e.g. General Services Office" class="w-full" />
                                <span v-if="errors.name" class="ios-hint ios-error">{{ errors.name }}</span>
                            </div>
                            <div class="ios-form-group">
                                <label class="ios-label">Main office</label>
                                <Select v-model="form.parent_id" :options="parentOptions" optionLabel="label"
                                    optionValue="value" placeholder="Main office (top level)" class="w-full"
                                    :disabled="parentSelectDisabled" />
                                <span v-if="parentSelectDisabled" class="ios-hint">This main office has assignments
                                    under it, so it cannot move under another main office.</span>
                                <span v-if="errors.parent_id" class="ios-hint ios-error">{{ errors.parent_id }}</span>
                            </div>
                            <div class="ios-form-group">
                                <label class="ios-label">Assigned head <span
                                        class="text-surface-400 font-normal">(optional)</span></label>
                                <Select v-model="form.office_head_signatory_id" :options="officeHeads" optionLabel="name"
                                    optionValue="id" placeholder="No head assigned" class="w-full" showClear />
                                <span v-if="errors.office_head_signatory_id" class="ios-hint ios-error">{{
                                    errors.office_head_signatory_id }}</span>
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
    office: { type: Object, default: null },
    mode: { type: String, default: 'create' },
    parentOffice: { type: Object, default: null },
    mainOffices: { type: Array, default: () => [] },
    officeHeads: { type: Array, default: () => [] },
});

const emit = defineEmits(['update:show', 'saved']);

const toast = useToast();
// Wrapped in reactive() so template prop bindings unwrap the composable's
// refs (raw use renders class="dep __v_isRef" in script-setup SFCs).
const saveAction = reactive(useActionButton({ idleLabel: 'Save', processingLabel: 'Saving...', successLabel: 'Saved' }));
const errors = ref({});

const defaultForm = () => ({ name: '', parent_id: null, office_head_signatory_id: null });
const form = reactive(defaultForm());

const isAssignment = computed(() => {
    if (props.mode === 'edit' && props.office) {
        return props.office.parent_id !== null;
    }

    return form.parent_id !== null;
});

const modalTitle = computed(() => {
    if (props.mode === 'edit') {
        return isAssignment.value ? 'Edit Assignment' : 'Edit Main Office';
    }

    return isAssignment.value ? 'New Assignment' : 'New Office';
});

// A main office with assignments cannot move under another main office
// (that would make three levels); the server refuses it too.
const parentSelectDisabled = computed(() => props.mode === 'edit'
    && props.office
    && props.office.parent_id === null
    && (props.office.children?.length ?? 0) > 0);

const parentOptions = computed(() => {
    const mains = (props.mainOffices ?? [])
        .filter((office) => office.id !== props.office?.id)
        .map((office) => ({ label: office.name, value: office.id }));

    return [{ label: 'Main office (top level)', value: null }, ...mains];
});

watch(() => props.show, (val) => {
    if (val) {
        errors.value = {};
        if (props.mode === 'edit' && props.office) {
            form.name = props.office.name ?? '';
            form.parent_id = props.office.parent_id ?? null;
            form.office_head_signatory_id = props.office.office_head_signatory_id ?? null;
        } else {
            Object.assign(form, defaultForm());
            form.parent_id = props.parentOffice?.id ?? null;
        }
    }
});

async function submit() {
    errors.value = {};
    if (!form.name) { errors.value.name = 'Name is required'; }
    if (Object.keys(errors.value).length) return;

    if (saveAction.state !== 'idle') return;
    try {
        await saveAction.run(async () => {
            try {
                if (props.mode === 'edit') {
                    await axios.put(`/api/offices/${props.office.id}`, form);
                    toast.add({ severity: 'success', summary: 'Success', detail: 'Office updated', life: 3000 });
                } else {
                    await axios.post('/api/offices', form);
                    toast.add({ severity: 'success', summary: 'Success', detail: 'Office created', life: 3000 });
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
    dragOffset.value = { x: e.clientX - dragOffset.value.x, y: e.clientY - dragOffset.value.y };
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
