/**
 * PDF Print CSS Library — Workforce Portal
 */

const PDF_CSS = `
/* ── Reset ──────────────────────────────────── */
* { margin: 0; padding: 0; box-sizing: border-box; }

body {
  font-family: Arial, Helvetica, sans-serif;
  font-size: 10pt;
  color: #000;
  background: #fff;
  padding: 6mm 5mm;
  min-width: {{BODY_MIN_WIDTH}};
}

@page { size: {{PAGE_SIZE}}; margin: 6mm 5mm; }

p  { display: block !important; margin: 0 !important; padding: 0 !important; line-height: 1.5; }
ol, ul { padding-left: 14pt; margin: 0; }
strong { font-weight: bold; }
em     { font-style: italic; }

/* ── Typography ─────────────────────────────── */
.t-5   { font-size:  5pt; }
.t-6   { font-size:  6pt; }
.t-7   { font-size:  7pt; }
.t-8   { font-size:  8pt; }
.t-9   { font-size:  9pt; }
.t-10  { font-size: 10pt; }
.t-11  { font-size: 11pt; }
.t-12  { font-size: 12pt; }
.t-14  { font-size: 14pt; }

.bold      { font-weight: bold; }
.normal    { font-weight: normal; }
.italic    { font-style: italic; }
.underline { text-decoration: underline; }
.nowrap    { white-space: nowrap; }

.left   { text-align: left;   justify-content: flex-start; }
.center { text-align: center; justify-content: center; }
.right  { text-align: right;  justify-content: flex-end; }

/* ── Borders ─────────────────────────────────── */
.b-all    { border: 1pt solid #000; }
.b-t      { border-top:    1pt solid #000; }
.b-r      { border-right:  1pt solid #000; }
.b-b      { border-bottom: 1pt solid #000; }
.b-l      { border-left:   1pt solid #000; }

.b-none   { border:        none !important; }
.b-t-none { border-top:    none !important; }
.b-r-none { border-right:  none !important; }
.b-b-none { border-bottom: none !important; }
.b-l-none { border-left:   none !important; }

/* ── Spacing ─────────────────────────────────── */
.px-1 { padding-left: 2pt;  padding-right: 2pt;  }
.px-2 { padding-left: 4pt;  padding-right: 4pt;  }
.px-3 { padding-left: 6pt;  padding-right: 6pt;  }
.px-4 { padding-left: 8pt;  padding-right: 8pt;  }
.py-1 { padding-top:  2pt;  padding-bottom: 2pt; }
.py-2 { padding-top:  4pt;  padding-bottom: 4pt; }
.py-3 { padding-top:  6pt;  padding-bottom: 6pt; }
.pt-0  { padding-top: 0;    }
.pt-4  { padding-top: 4pt;  }
.pt-8  { padding-top: 8pt;  }
.pt-40 { padding-top: 40pt; }

/* ── Grid Table System ───────────────────────── */
.pdf-table { display: grid; width: 100%; }
.pdf-table > div {
  display: flex;
  align-items: center;
  padding: 2pt 4pt;
  min-height: 18pt;
  word-break: break-word;
  overflow: hidden;
  line-height: 1.4;
}
.pdf-table > div.blk { display: block; padding: 3pt 4pt; }
.pdf-table > div.col-dir { flex-direction: column; align-items: center; justify-content: center; }

/* ── Column Span Utilities ────────────────────── */
.cs-1 { grid-column: span 1; }
.cs-2 { grid-column: span 2; }
.cs-3 { grid-column: span 3; }
.cs-4 { grid-column: span 4; }
.cs-5 { grid-column: span 5; }
.cs-6 { grid-column: span 6; }
.cs-7 { grid-column: span 7; }
.cs-8 { grid-column: span 8; }

/* ── Absolute Column Start ───────────────────── */
.c1 { grid-column-start: 1; }
.c2 { grid-column-start: 2; }
.c3 { grid-column-start: 3; }
.c4 { grid-column-start: 4; }
.c5 { grid-column-start: 5; }
.c6 { grid-column-start: 6; }
.c7 { grid-column-start: 7; }
.c8 { grid-column-start: 8; }

/* ── Row Span ─────────────────────────────────── */
.rs-2 { grid-row: span 2; }
.rs-3 { grid-row: span 3; }
.rs-4 { grid-row: span 4; }

/* ── Grid Column Definitions ─────────────────── */
.pdf-grid-obr { grid-template-columns: 100pt 181pt 70pt 50pt 65pt 110pt; }
.pdf-g-2 { grid-template-columns: 1fr 1fr; }
.pdf-g-3 { grid-template-columns: 1fr 1fr 1fr; }
.pdf-g-4 { grid-template-columns: 1fr 1fr 1fr 1fr; }

/* ── OBR Semantic Column Shortcuts ───────────── */
.obr-label  { grid-column: 1;     }
.obr-main   { grid-column: 2;     }
.obr-wide   { grid-column: 2 / 4; }
.obr-year   { grid-column: 3;     }
.obr-fpp    { grid-column: 4;     }
.obr-code   { grid-column: 5;     }
.obr-amt    { grid-column: 6;     }
.obr-right  { grid-column: 4 / 7; }
.obr-title  { grid-column: 1 / 4; }
.obr-all    { grid-column: 1 / 7; }

/* ── Page Break ──────────────────────────────── */
.break-after  { page-break-after:  always; }
.break-before { page-break-before: always; }
.no-break     { page-break-inside: avoid;  }

/* ── DV row system ────────────────────────────── */
.dv-row {
  display: flex;
  align-items: stretch;
  border-bottom: 1pt solid #000;
}
.blk { display: block; padding: 3pt 4pt; }

/* ── Print Overrides ────────────────────────── */
@media print {
  * { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
  body { margin: 0; padding: 0; }
}
`;

export const PAPER_SIZES = {
	a4: { w: 794, h: 1123, cssSize: 'A4', label: 'A4 — 210 × 297 mm' },
	long: { w: 816, h: 1248, cssSize: '8.5in 13in', label: 'Long — 8.5 × 13 in' },
	landscape: { w: 1248, h: 816, cssSize: '13in 8.5in', label: 'Long Landscape — 13 × 8.5 in' },
};

// Body min-width so the screen preview matches the paper width (margins: 5mm×2 = 10mm)
const BODY_MIN_WIDTHS = {
	a4: 'calc(210mm - 10mm)',
	long: 'calc(8.5in - 10mm)',
	landscape: 'calc(13in - 10mm)',
};

export const getPdfCss = (paperSize = 'a4') => {
	const size = PAPER_SIZES[paperSize]?.cssSize ?? PAPER_SIZES.a4.cssSize;
	const minWidth = BODY_MIN_WIDTHS[paperSize] ?? BODY_MIN_WIDTHS.a4;
	return PDF_CSS.replace('{{PAGE_SIZE}}', size).replace('{{BODY_MIN_WIDTH}}', minWidth);
};

export default getPdfCss('a4');
