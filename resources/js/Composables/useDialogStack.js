import { onMounted, onUnmounted } from 'vue';

/**
 * Module-level stack of currently open, Esc-closable dialogs.
 *
 * Why this exists: PrimeVue 4.5.4 attaches its own document keydown listener
 * to every visible Dialog with no topmost check, so one Esc press closes ALL
 * open dialogs. AppDialog (the global `Dialog` wrapper) therefore passes
 * closeOnEscape=false to the inner PrimeVue Dialog and registers itself here
 * instead; a single capture-phase handler in WorkforceLayout closes only the
 * top entry. Registration order is opening order, so the last entry pushed is
 * the frontmost dialog.
 *
 * Entries hold only a close callback (emit update:visible=false); the stack
 * never touches a dialog's DOM, so PrimeVue's own hide/focus-restore logic
 * still runs when the top dialog closes, and the dialogs underneath stay
 * mounted and visible.
 */

let nextId = 0;
const stack = [];
let escapeListenerCount = 0;

export function registerDialog(closeCallback) {
    const entry = { id: ++nextId, close: closeCallback };
    stack.push(entry);
    return entry.id;
}

export function unregisterDialog(id) {
    const index = stack.findIndex((entry) => entry.id === id);
    if (index !== -1) {
        stack.splice(index, 1);
    }
}

/** Close only the top (frontmost) stack entry. Returns false when empty. */
export function closeTopDialog() {
    const top = stack[stack.length - 1];
    if (!top) {
        return false;
    }
    // Deliberately do NOT pop here: the entry is removed when the dialog's
    // visible prop flips to false (AppDialog's watcher). If a parent ignored
    // the close request the dialog is still open, so it must stay on top
    // and a further Esc press must retry it rather than skipping to the
    // dialog underneath.
    top.close();
    return true;
}

export function getDialogStackDepth() {
    return stack.length;
}

function onEscapeKeyDown(event) {
    if (event.key !== 'Escape' || stack.length === 0) {
        return;
    }
    // Capture phase (installed with capture: true) runs before PrimeVue's
    // bubble-phase document listeners, and stopPropagation() keeps those
    // listeners — and every other document keydown handler below this one —
    // from firing, so no dialog underneath can close on this press.
    // When the stack is empty we do nothing, so unrelated Esc handlers
    // (context menus, drawers, inputs) keep working.
    event.stopPropagation();
    closeTopDialog();
}

/**
 * Install the single capture-phase Escape handler. Safe to call from one
 * layout instance (the app has exactly one authenticated layout); a
 * reference count keeps the listener installed exactly once even if the
 * layout is ever mounted twice, and removes it when the last instance
 * unmounts. Call at the top level of a <script setup> block.
 */
export function useDialogEscapeHandler() {
    onMounted(() => {
        if (escapeListenerCount === 0) {
            document.addEventListener('keydown', onEscapeKeyDown, true);
        }
        escapeListenerCount += 1;
    });

    onUnmounted(() => {
        escapeListenerCount = Math.max(0, escapeListenerCount - 1);
        if (escapeListenerCount === 0) {
            document.removeEventListener('keydown', onEscapeKeyDown, true);
        }
    });
}
