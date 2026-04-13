<template>
    <Dialog :visible="show" @update:visible="val => emit('update:show', val)" modal
        :pt="{ root: { class: 'ios-dialog-root' }, mask: { class: 'ios-dialog-mask' } }">
        <template #container>
            <div ref="elModal" class="ios-modal w-130 max-w-[92vw]" :style="modalStyle">

                <div class="ios-nav-bar" @pointerdown="onDragStart">
                    <button class="ios-nav-btn ios-nav-cancel" @click="emit('update:show', false)">
                        <i class="pi pi-times"></i> Close
                    </button>
                    <span class="ios-nav-title">Tracking History</span>
                </div>

                <div class="ios-body">

                    <!-- Timeline -->
                    <div class="ios-section" v-if="hasHistory">
                        <div class="relative">
                            <!-- Vertical line -->
                            <div class="absolute left-3.5 top-4 bottom-4 w-0.5 bg-surface-200 dark:bg-surface-600"></div>

                            <div v-for="(entry, index) in trackingData.tracking_information" :key="index"
                                class="relative flex gap-4 pb-6 last:pb-0">
                                <!-- Dot -->
                                <div class="relative z-10 shrink-0 w-7 h-7 rounded-full flex items-center justify-center mt-0.5"
                                    :class="index === 0 ? 'bg-primary-500' : 'bg-surface-300 dark:bg-surface-600'">
                                    <div class="w-2.5 h-2.5 rounded-full bg-white"></div>
                                </div>

                                <!-- Content -->
                                <div class="flex-1 min-w-0 pt-0.5">
                                    <p class="text-sm font-medium text-surface-800 dark:text-surface-100 leading-snug">
                                        {{ entry.description || entry.action || '—' }}
                                    </p>
                                    <p class="text-xs text-surface-400 mt-0.5">
                                        {{ formatDate(entry.created_at || entry.date) }}
                                        <span v-if="entry.by || entry.performed_by" class="ml-1">
                                            · {{ entry.by || entry.performed_by }}
                                        </span>
                                    </p>
                                    <p v-if="entry.status" class="mt-1">
                                        <Tag :value="entry.status" :severity="statusSeverity(entry.status)" class="capitalize" />
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="ios-section" v-else>
                        <div class="ios-card flex flex-col items-center gap-3 py-8 text-center">
                            <i class="pi pi-history text-4xl text-surface-300"></i>
                            <p class="text-sm text-surface-400">No tracking history available.</p>
                        </div>
                    </div>

                </div>

            </div>
        </template>
    </Dialog>
</template>

<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
    show: Boolean,
    trackingData: { type: Object, default: null },
});

const emit = defineEmits(['update:show']);

const hasHistory = computed(() => {
    return Array.isArray(props.trackingData?.tracking_information)
        && props.trackingData.tracking_information.length > 0;
});

function formatDate(val) {
    if (!val) return '—';
    return new Date(val).toLocaleDateString('en-PH', {
        year: 'numeric', month: 'short', day: 'numeric',
        hour: '2-digit', minute: '2-digit',
    });
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
