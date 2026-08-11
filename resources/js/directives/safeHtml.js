import DOMPurify from 'dompurify';

/**
 * v-safe-html directive
 * Renders rich-text HTML content (e.g. Quill output) into an element's
 * innerHTML, sanitised client-side with DOMPurify. Use this instead of
 * Vue's raw v-html for any user-authored HTML.
 */
const safeHtmlDirective = {
	mounted(el, binding) {
		el.innerHTML = DOMPurify.sanitize(binding.value ?? '');
	},
	updated(el, binding) {
		el.innerHTML = DOMPurify.sanitize(binding.value ?? '');
	},
};

export default safeHtmlDirective;
