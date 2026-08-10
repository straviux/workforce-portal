<template>
    <Dialog :visible="show" @update:visible="val => emit('update:show', val)" modal
        :pt="{ root: { class: 'ios-dialog-root' }, mask: { class: 'ios-dialog-mask' } }">
        <template #container>
            <div ref="elModal" class="ios-modal w-160 max-w-[95vw]" :style="modalStyle">

                <div class="ios-nav-bar" @pointerdown="onDragStart">
                    <button class="ios-nav-btn ios-nav-cancel" @click="emit('update:show', false)">
                        <i class="pi pi-times"></i>
                    </button>
                    <span class="ios-nav-title">Match Users to Employees</span>
                    <button class="ios-nav-btn ios-nav-action" :disabled="!selectedCount || confirming"
                        @click="confirmSelected">
                        {{ confirming ? 'Linking…' : `Confirm (${selectedCount})` }}
                    </button>
                </div>

                <div class="ios-body !pt-2 !pb-6">
                    <p class="text-xs text-surface-400 mb-3">
                        Suggested matches are based on name similarity between employees without a linked portal
                        account and users not yet linked to any employee. Review and uncheck anything that looks
                        wrong before confirming.
                    </p>

                    <div v-if="loading" class="text-center py-12 text-surface-400">
                        <i class="pi pi-spin pi-spinner text-2xl block mb-3"></i>
                        <p class="text-sm">Looking for matches…</p>
                    </div>

                    <div v-else-if="!suggestions.length" class="text-center py-12 text-surface-400">
                        <i class="pi pi-check-circle text-3xl block mb-3"></i>
                        <p class="text-sm">Every employee already has a linked user account.</p>
                    </div>

                    <div v-else class="space-y-2">
                        <div v-for="row in suggestions" :key="row.employee.id"
                            class="flex items-center gap-3 p-3 rounded-2xl bg-surface-50 dark:bg-surface-800">
                            <Checkbox v-model="row.checked" :binary="true" :disabled="!row.suggested_user" />

                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-surface-800 dark:text-surface-100 truncate">
                                    {{ row.employee.full_name }}
                                </p>
                                <p class="text-xs text-surface-400 truncate">
                                    {{ row.employee.employee_no || '—' }}<span v-if="row.employee.office"> ·
                                        {{ row.employee.office }}</span>
                                </p>
                            </div>

                            <i class="pi pi-arrow-right text-surface-300 text-xs"></i>

                            <div class="flex-1 min-w-0 text-right">
                                <template v-if="row.suggested_user">
                                    <p class="text-sm font-medium text-surface-800 dark:text-surface-100 truncate">
                                        {{ row.suggested_user.name }}
                                    </p>
                                    <p class="text-xs text-surface-400 truncate">@{{ row.suggested_user.username }}</p>
                                </template>
                                <p v-else class="text-xs text-surface-400 italic">No confident match found</p>
                            </div>

                            <Tag v-if="row.suggested_user" :value="`${row.score}%`"
                                :severity="row.score >= 80 ? 'success' : 'warning'" />
                        </div>
                    </div>
                </div>

            </div>
        </template>
    </Dialog>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { useToast } from 'primevue/usetoast';
import axios from 'axios';

const props = defineProps({
    show: Boolean,
});

const emit = defineEmits(['update:show', 'matched']);

const toast = useToast();
const loading = ref(false);
const confirming = ref(false);
const suggestions = ref([]);

const selectedCount = computed(() => suggestions.value.filter((row) => row.checked && row.suggested_user).length);

async function load() {
    loading.value = true;
    try {
        const { data } = await axios.get('/api/employees/user-matches');
        suggestions.value = data.data.map((row) => ({ ...row, checked: !!row.suggested_user }));
    } catch {
        toast.add({ severity: 'error', summary: 'Error', detail: 'Could not load suggested matches.', life: 3500 });
    } finally {
        loading.value = false;
    }
}

async function confirmSelected() {
    const matches = suggestions.value
        .filter((row) => row.checked && row.suggested_user)
        .map((row) => ({ employee_id: row.employee.id, user_id: row.suggested_user.id }));

    if (!matches.length) return;

    confirming.value = true;
    try {
        const { data } = await axios.post('/api/employees/user-matches', { matches });
        toast.add({ severity: 'success', summary: 'Linked', detail: data.message, life: 3500 });
        emit('matched');
        emit('update:show', false);
    } catch (error) {
        toast.add({
            severity: 'error',
            summary: 'Error',
            detail: error.response?.data?.message || 'Could not link the selected matches.',
            life: 3500,
        });
    } finally {
        confirming.value = false;
    }
}

watch(() => props.show, (visible) => {
    if (visible) load();
});

// ── Drag ─────────────────────────────────────────────────
const elModal = ref(null);
const dragOffset = ref({ x: 0, y: 0 });
const dragStart = ref(null);

const modalStyle = computed(() => ({
    transform: `translate(${dragOffset.value.x}px, ${dragOffset.value.y}px)`,
}));

function onDragStart(e) {
    if (e.target.closest('button, input, select, textarea')) return;
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
