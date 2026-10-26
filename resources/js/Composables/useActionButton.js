import { computed, onUnmounted, reactive, ref } from 'vue';

/**
 * M3 Expressive processing morph state machine for action buttons.
 *
 * Idle -> processing -> success -> idle. On click the button stretches
 * wider, its corners morph from 12px to pill, and its label swaps to the
 * processing text with a spinner; it holds that state until the wrapped
 * async call actually resolves (success is never a fake timer), shows the
 * success label with a check for `successMs`, then morphs back to its
 * original size, shape, and text. On reject it returns to idle
 * immediately and rethrows, so the caller's existing error toast runs
 * unchanged and no error state is invented here.
 *
 * `run()` resolves only after the FULL cycle (processing -> success ->
 * idle), so a caller that closes a modal or navigates after
 * `await run(...)` still shows the success state first. Callers MUST
 * await run() before emitting saved/close events; a fire-and-forget
 * call would close the modal before success is visible.
 *
 * Double-clicks are guarded: calls while processing or success are
 * ignored. The button stays disabled through processing AND success
 * (`disabled`), while `loading` is processing-only: bind PrimeVue
 * `:loading` to `loading` (spinner) and `:disabled` to `disabled`, so
 * the success state can show its check icon instead of a spinner.
 *
 * The returned object is reactive (not a plain object of refs): template
 * bindings like `action.label` / `action.morphClass` unwrap correctly,
 * and script code reads fields WITHOUT `.value` (`action.state !==
 * 'idle'`). Returning a plain object of refs would silently break
 * `:class` bindings in templates (the RefImpl object stringifies into
 * garbage class names) — do not change this return shape back.
 *
 * Template wiring for a PrimeVue Button:
 *   <Button :label="action.label" :loading="action.loading"
 *       :disabled="action.disabled" :class="action.morphClass"
 *       @click="() => action.run(submit)">
 *       <template #icon>
 *           <AppIcon v-if="action.state === 'success'" name="Check" />
 *           <AppIcon v-else name="..." />
 *       </template>
 *   </Button>
 * For a native modal header button (.ios-nav-action), bind
 * `:disabled="action.disabled" :class="action.morphClass"` and render
 * `{{ action.label }}` as its text.
 *
 * Options: idleLabel (default ''), processingLabel (default
 * 'Processing...'), successLabel (default 'Done'), successMs
 * (default 2000).
 */
export function useActionButton(options = {}) {
    const {
        idleLabel = '',
        processingLabel = 'Processing...',
        successLabel = 'Done',
        successMs = 2000,
    } = options;

    const state = ref('idle');

    /** Spinner on: processing only, so success shows its check. */
    const loading = computed(() => state.value === 'processing');
    /** Clickable off: processing AND success, re-enabled at idle. */
    const disabled = computed(() => state.value !== 'idle');
    const label = computed(() => (
        state.value === 'processing'
            ? processingLabel
            : state.value === 'success'
                ? successLabel
                : idleLabel
    ));
    const morphClass = computed(() => actionMorphClass(state.value));

    let timer = null;

    onUnmounted(() => {
        if (timer) clearTimeout(timer);
    });

    async function run(asyncFn) {
        if (state.value !== 'idle') return undefined;

        state.value = 'processing';

        try {
            const result = await asyncFn();

            state.value = 'success';
            await new Promise((resolve) => {
                timer = setTimeout(resolve, successMs);
            });
            timer = null;
            state.value = 'idle';

            return result;
        } catch (error) {
            if (timer) {
                clearTimeout(timer);
                timer = null;
            }
            state.value = 'idle';
            throw error;
        }
    }

    // Reactive (see header note): template bindings unwrap, script reads
    // fields without .value. Wrapping this again in reactive() elsewhere
    // is harmless (reactive of reactive is itself).
    return reactive({ state, label, loading, disabled, morphClass, run });
}

/**
 * Morph class for a button whose busy state lives elsewhere instead of
 * in useActionButton: useForm().processing, or a parent-owned state
 * passed down as a prop. Pass 'processing', 'success', or anything else
 * for idle. Pair with the same label-swapping in the template.
 */
export function actionMorphClass(state) {
    if (state === 'processing') return 'morph-processing';
    if (state === 'success') return 'morph-success';
    return '';
}
