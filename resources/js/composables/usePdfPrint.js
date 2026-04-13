import { getPdfCss } from '@/Pages/EmployeeFundTransactions/Pdf/pdf-styles.js';
import { createApp } from 'vue';
import safeHtmlDirective from '@/directives/safeHtml';

/**
 * Renders a Vue component to an HTML string.
 * Mounts it onto a temporary detached div, grabs innerHTML, then unmounts.
 *
 * @param {Component} Component  – Vue SFC / component object
 * @param {Object}    [props]    – props to pass
 * @returns {string}             – rendered inner HTML
 */
export function renderVueTemplate(Component, props = {}) {
	const el = document.createElement('div');
	const app = createApp(Component, props);
	app.directive('safe-html', safeHtmlDirective);
	app.mount(el);
	const html = el.innerHTML;
	app.unmount();
	return html;
}

/**
 * usePdfPrint — client-side PDF generation via window.print()
 */
export function usePdfPrint() {
	/**
	 * Assembles a complete HTML document string ready for srcdoc or window.open.
	 */
	const buildHtmlDoc = (bodyHtml, title = 'Document', paperSize = 'a4') => `<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>${title}</title>
  <style>${getPdfCss(paperSize)}</style>
</head>
<body>${bodyHtml}</body>
</html>`;

	/**
	 * Opens the document in a new tab and triggers the print dialog directly.
	 */
	const printHtml = (bodyHtml, title = 'Document', paperSize = 'a4') => {
		const win = window.open('', '_blank');
		if (!win) {
			alert('Pop-up blocked. Please allow pop-ups for this site and try again.');
			return;
		}
		win.document.write(buildHtmlDoc(bodyHtml, title, paperSize));
		win.document.close();
		win.onload = () => {
			win.focus();
			win.print();
		};
		setTimeout(() => {
			if (win && !win.closed) {
				win.focus();
				win.print();
			}
		}, 800);
	};

	return { buildHtmlDoc, printHtml };
}
