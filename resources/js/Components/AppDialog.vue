<script setup>
import { ref, watch, onBeforeUnmount } from 'vue';
import PrimeDialog from 'primevue/dialog';
import { registerDialog, unregisterDialog } from '@/Composables/useDialogStack';

// Thin wrapper registered globally under the name `Dialog` (see app.js),
// so all existing <Dialog> call sites get stacked Esc handling with no
// edits. Only `visible` and `closeOnEscape` are declared props; every other
// prop, class/style, and event listener falls through via $attrs to the
// inner PrimeVue Dialog, and every slot (default, container, header, footer,
// closeicon, ...) is forwarded by the v-for loop below.
defineOptions({ inheritAttrs: false });

const props = defineProps({
    visible: { type: Boolean, default: false },
    closeOnEscape: { type: Boolean, default: true },
});

const emit = defineEmits(['update:visible']);

const entryId = ref(null);

function syncRegistration() {
    // Register only while actually visible AND Esc-closable. Dialogs that
    // explicitly pass closeOnEscape=false (the Esc-proof DTR/SWA workspace
    // parents) never enter the stack, so Esc can never close them, and a
    // dialog that flips closeOnEscape while open joins/leaves accordingly.
    const shouldRegister = props.visible && props.closeOnEscape;
    if (shouldRegister && entryId.value === null) {
        entryId.value = registerDialog(() => emit('update:visible', false));
    } else if (!shouldRegister && entryId.value !== null) {
        unregisterDialog(entryId.value);
        entryId.value = null;
    }
}

watch(() => [props.visible, props.closeOnEscape], syncRegistration, { immediate: true });

onBeforeUnmount(() => {
    if (entryId.value !== null) {
        unregisterDialog(entryId.value);
        entryId.value = null;
    }
});

function onUpdateVisible(value) {
    emit('update:visible', value);
}
</script>

<template>
    <!-- closeOnEscape is forced false on the inner dialog: PrimeVue's own
         per-dialog document listener must never fire, or it would close
         every open dialog. Esc handling lives entirely in the stack. -->
    <PrimeDialog v-bind="$attrs" :visible="visible" :closeOnEscape="false" @update:visible="onUpdateVisible">
        <template v-for="(_, name) in $slots" :key="name" #[name]="slotProps">
            <slot :name="name" v-bind="slotProps" />
        </template>
    </PrimeDialog>
</template>
