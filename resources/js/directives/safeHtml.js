/**
 * v-safe-html directive
 * Renders trusted HTML content (e.g. Quill output) into an element's innerHTML.
 * Only use with content that has been sanitised server-side.
 */
const safeHtmlDirective = {
    mounted(el, binding) {
        el.innerHTML = binding.value ?? '';
    },
    updated(el, binding) {
        el.innerHTML = binding.value ?? '';
    },
};

export default safeHtmlDirective;
