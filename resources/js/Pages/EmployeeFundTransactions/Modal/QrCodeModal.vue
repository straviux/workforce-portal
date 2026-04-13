<template>
    <Dialog :visible="show" @update:visible="val => emit('update:show', val)" modal
        :pt="{ root: { class: 'ios-dialog-root' }, mask: { class: 'ios-dialog-mask' } }">
        <template #container>
            <div ref="elModal" class="ios-modal w-105 max-w-[92vw]" :style="modalStyle">

                <div class="ios-nav-bar" @pointerdown="onDragStart">
                    <button class="ios-nav-btn ios-nav-cancel" @click="emit('update:show', false)">
                        <i class="pi pi-times"></i> Close
                    </button>
                    <span class="ios-nav-title">QR Code</span>
                </div>

                <div class="ios-body" v-if="modelValue">

                    <!-- QR Code Display -->
                    <div class="ios-section">
                        <div class="ios-card flex flex-col items-center py-4 gap-4">
                            <div v-if="modelValue.qrCode" class="w-48 h-48 flex items-center justify-center"
                                v-safe-html="modelValue.qrCode">
                            </div>
                            <div v-else class="w-48 h-48 bg-surface-100 dark:bg-surface-700 rounded-xl flex items-center justify-center">
                                <i class="pi pi-qrcode text-5xl text-surface-400"></i>
                            </div>

                            <div v-if="modelValue.expiresAt" class="text-center">
                                <p class="text-xs text-surface-400">Expires in</p>
                                <p class="text-lg font-mono font-bold" :class="countdownUrgent ? 'text-red-500' : 'text-primary-400'">
                                    {{ countdown || '—' }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Instructions -->
                    <div class="ios-section">
                        <div class="ios-card">
                            <p class="text-xs font-semibold uppercase tracking-wide text-surface-400 mb-2">How to use</p>
                            <ol class="text-sm space-y-1 list-decimal list-inside text-surface-300">
                                <li>Open a QR scanner app on your phone.</li>
                                <li>Point at the code to scan it.</li>
                                <li>The transaction details will open in your browser.</li>
                            </ol>
                        </div>
                    </div>

                    <!-- Copy URL -->
                    <div class="ios-section" v-if="modelValue.url">
                        <div class="ios-card flex items-center gap-3">
                            <div class="flex-1 min-w-0">
                                <p class="text-xs text-surface-400 truncate">{{ modelValue.url }}</p>
                            </div>
                            <Button icon="pi pi-copy" severity="secondary" class="rounded" outlined size="small"
                                @click="copyUrl" v-tooltip="copied ? 'Copied!' : 'Copy URL'" />
                        </div>
                    </div>

                </div>

                <div class="ios-body" v-else>
                    <div class="flex flex-col items-center py-10 gap-3">
                        <i class="pi pi-spin pi-spinner text-3xl text-surface-400"></i>
                        <p class="text-sm text-surface-400">Loading QR code…</p>
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
    modelValue: { type: Object, default: null },
    countdown: { type: String, default: '' },
});

const emit = defineEmits(['update:show']);

const copied = ref(false);

const countdownUrgent = computed(() => {
    if (!props.countdown) return false;
    const parts = props.countdown.split(':');
    if (parts.length < 2) return false;
    const totalSeconds = parseInt(parts[0]) * 60 + parseInt(parts[1]);
    return totalSeconds < 60;
});

async function copyUrl() {
    if (!props.modelValue?.url) return;
    try {
        await navigator.clipboard.writeText(props.modelValue.url);
        copied.value = true;
        setTimeout(() => { copied.value = false; }, 2000);
    } catch {
        // clipboard not available
    }
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
