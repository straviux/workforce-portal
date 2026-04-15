<template>
    <Dialog :visible="show" @update:visible="val => emit('update:show', val)" modal
        :pt="{ root: { class: 'ios-dialog-root' }, mask: { class: 'ios-dialog-mask' } }">
        <template #container>
            <div ref="elModal" class="ios-modal" :style="[modalDragStyle, {
                width: '95vw',
                maxWidth: modalMaxWidth,
                maxHeight: '97vh',
                display: 'flex',
                flexDirection: 'column',
            }]">

                <!-- Nav Bar -->
                <div class="ios-nav-bar" @pointerdown="onDragStart">
                    <button class="ios-nav-btn ios-nav-cancel" @click="emit('update:show', false)">
                        <i class="pi pi-times"></i>
                    </button>
                    <span class="ios-nav-title">{{ title }}</span>
                    <button v-if="onExcel" @click="onExcel" class="ios-nav-btn" style="right:58px;"
                        v-tooltip.bottom="'Export Excel'">
                        <i class="pi pi-file-excel text-purple-400" style="font-size:20px;"></i>
                    </button>
                    <button class="ios-nav-btn ios-nav-action" @click="doPrint"
                        v-tooltip.right="'Print or Save Document'">
                        <i class="pi pi-print text-emerald-400" style="font-size:20px;"></i>
                    </button>
                </div>

                <!-- Toolbar strip -->
                <div class="flex items-center justify-between px-4 py-2
                            bg-[#f2f2f7] dark:bg-[#1e242b]
                            border-b border-[#e5e5ea] dark:border-white/10">
                    <div class="flex items-center gap-1.5">
                        <button @click="zoomOut" class="w-7 h-7 rounded-full flex items-center justify-center
                                   bg-white dark:bg-[#2a3040] border border-[#e5e5ea] dark:border-white/10
                                   text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-[#343d4e]
                                   transition-colors disabled:opacity-40" :disabled="zoomLevel <= 40">
                            <i class="pi pi-minus" style="font-size:10px;"></i>
                        </button>
                        <span class="text-xs font-medium text-gray-600 dark:text-gray-400 w-12 text-center">
                            {{ zoomLevel }}%
                        </span>
                        <button @click="zoomIn" class="w-7 h-7 rounded-full flex items-center justify-center
                                   bg-white dark:bg-[#2a3040] border border-[#e5e5ea] dark:border-white/10
                                   text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-[#343d4e]
                                   transition-colors disabled:opacity-40" :disabled="zoomLevel >= 150">
                            <i class="pi pi-plus" style="font-size:10px;"></i>
                        </button>
                        <button @click="fitToWidth" class="ml-1 px-2 h-7 rounded-full flex items-center justify-center gap-1
                                   bg-white dark:bg-[#2a3040] border border-[#e5e5ea] dark:border-white/10
                                   text-xs text-gray-600 dark:text-gray-300
                                   hover:bg-gray-100 dark:hover:bg-[#343d4e] transition-colors">
                            <i class="pi pi-expand" style="font-size:10px;"></i>
                            Fit
                        </button>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-xs text-gray-400 dark:text-gray-500 hidden sm:block">{{ paperLabel }}</span>
                    </div>
                </div>

                <!-- Paper Preview Area -->
                <div ref="scrollArea" class="overflow-auto bg-[#d1d1d6] dark:bg-[#1c1c1e]"
                    style="flex:1; min-height:0; padding:16px 0;">
                    <div :style="paperWrapperStyle" class="mx-auto">
                        <iframe ref="iframeEl" :srcdoc="htmlDoc" scrolling="no" style="border:none; display:block;"
                            :style="iframeStyle" @load="onIframeLoad" />
                    </div>
                </div>

            </div>
        </template>
    </Dialog>
</template>

<script setup>
import { ref, computed, watch, onMounted, onBeforeUnmount, nextTick } from 'vue';
import Dialog from 'primevue/dialog';
import { PAPER_SIZES } from '@/Pages/EmployeeFundTransactions/Pdf/pdf-styles.js';

// ── Props / Emits ──────────────────────────────────────────────────────
const props = defineProps({
    show: { type: Boolean, required: true },
    /** Full HTML document string — built by usePdfPrint.buildHtmlDoc() */
    htmlDoc: { type: String, default: '' },
    /** Paper size key — must match a key in PAPER_SIZES ('a4' | 'long' | 'landscape') */
    paperSize: {
        type: String,
        default: 'a4',
        validator: (v) => Object.keys(PAPER_SIZES).includes(v),
    },
    title: { type: String, default: 'Document Preview' },
    /** Optional Excel export handler */
    onExcel: { type: Function, default: null },
});

const emit = defineEmits(['update:show']);

// ── Paper dimensions ───────────────────────────────────────────────────
const paperDims = computed(() => PAPER_SIZES[props.paperSize] ?? PAPER_SIZES.a4);
const PAPER_W = computed(() => paperDims.value.w);
const PAPER_H = computed(() => paperDims.value.h);
const paperLabel = computed(() => paperDims.value.label);
const modalMaxWidth = computed(() => (PAPER_W.value + 64) + 'px');

// ── Refs ───────────────────────────────────────────────────────────────
const elModal = ref(null);
const scrollArea = ref(null);
const iframeEl = ref(null);

const actualIframeH = ref(PAPER_H.value);

watch(() => [props.htmlDoc, props.paperSize], () => {
    actualIframeH.value = PAPER_H.value;
});

// ── Zoom ───────────────────────────────────────────────────────────────
const zoomLevel = ref(100);
const scale = computed(() => zoomLevel.value / 100);

const iframeStyle = computed(() => ({
    width: PAPER_W.value + 'px',
    height: actualIframeH.value + 'px',
    transformOrigin: 'top left',
    transform: `scale(${scale.value})`,
    boxShadow: '0 4px 24px rgba(0,0,0,0.25)',
    background: '#fff',
}));

const paperWrapperStyle = computed(() => ({
    width: Math.round(PAPER_W.value * scale.value) + 'px',
    height: Math.round(actualIframeH.value * scale.value) + 'px',
    position: 'relative',
}));

const zoomIn = () => { zoomLevel.value = Math.min(150, zoomLevel.value + 10); };
const zoomOut = () => { zoomLevel.value = Math.max(40, zoomLevel.value - 10); };

const fitToWidth = () => {
    if (!scrollArea.value) return;
    const available = scrollArea.value.clientWidth - 48;
    const fit = Math.floor((available / PAPER_W.value) * 100);
    zoomLevel.value = Math.max(40, Math.min(150, fit));
};

// ── Drag ───────────────────────────────────────────────────────────────
const dragOffset = ref({ x: 0, y: 0 });
const dragStartPt = ref(null);

const modalDragStyle = computed(() => ({
    transform: `translate(${dragOffset.value.x}px, ${dragOffset.value.y}px)`,
}));

function resetDrag() {
    dragOffset.value = { x: 0, y: 0 };
    dragStartPt.value = null;
}

function onDragStart(e) {
    if (e.target.closest('button')) return;
    dragStartPt.value = { x: e.clientX - dragOffset.value.x, y: e.clientY - dragOffset.value.y };
    document.addEventListener('pointermove', onDragMove);
    document.addEventListener('pointerup', onDragEnd);
}

function onDragMove(e) {
    if (!dragStartPt.value) return;
    dragOffset.value = { x: e.clientX - dragStartPt.value.x, y: e.clientY - dragStartPt.value.y };
}

function onDragEnd() {
    dragStartPt.value = null;
    document.removeEventListener('pointermove', onDragMove);
    document.removeEventListener('pointerup', onDragEnd);
}

// ── Auto-fit when modal opens ──────────────────────────────────────────
watch(() => props.show, (val) => {
    if (val) {
        resetDrag();
        nextTick(() => fitToWidth());
    }
});

onMounted(() => {
    window.addEventListener('resize', fitToWidth);
});

onBeforeUnmount(() => {
    window.removeEventListener('resize', fitToWidth);
    document.removeEventListener('pointermove', onDragMove);
    document.removeEventListener('pointerup', onDragEnd);
});

// ── Iframe load ────────────────────────────────────────────────────────
const onIframeLoad = () => {
    const doc = iframeEl.value?.contentDocument;
    if (doc) {
        const h = doc.documentElement?.scrollHeight || doc.body?.scrollHeight;
        if (h && h > 0) actualIframeH.value = h;
    }
};

// ── Print ──────────────────────────────────────────────────────────────
const doPrint = () => {
    const win = iframeEl.value?.contentWindow;
    if (!win) return;
    win.focus();
    win.print();
};
</script>

<style>
/* Unscoped — Dialog PT classes are teleported to <body> */
.ios-dialog-root {
    background: transparent !important;
    box-shadow: none !important;
    padding: 0 !important;
}

.ios-dialog-mask {
    background: rgba(0, 0, 0, 0.45) !important;
    backdrop-filter: blur(4px);
}
</style>
