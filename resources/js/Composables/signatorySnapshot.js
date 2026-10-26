/**
 * Signatory configuration status only.
 *
 * The per-report signatory pickers and their presets are gone: all
 * signatory configuration lives in the Signatories module as central
 * per-document-type defaults (see Composables/signatoryDefaults.js), and
 * the backend resolves each document's signature block server-side.
 *
 * What remains here is signatoryConfigurationStatus(), the client-side
 * mirror of Signatory::configurationStatus(), kept as the completeness
 * fallback for the fund print gate in EmployeeFundTransactions/index.vue
 * when the signatories API response carries no authoritative
 * `configuration` object.
 */

/**
 * Client-side mirror of Signatory::configurationStatus(): complete when at
 * least one Part A row exists and Parts B, C and D hold exactly one row
 * each. The signatories API response carries the authoritative
 * `configuration` object; use this only as a fallback when it is absent.
 * Returns { complete: boolean, missing: string[] }.
 */
export function signatoryConfigurationStatus(signatories) {
    const rows = Array.isArray(signatories) ? signatories : [];
    const counts = rows.reduce((acc, row) => {
        if (row?.part) acc[row.part] = (acc[row.part] ?? 0) + 1;
        return acc;
    }, {});

    const missing = [];

    if ((counts.A ?? 0) < 1) missing.push('A');
    for (const part of ['B', 'C', 'D']) {
        if ((counts[part] ?? 0) !== 1) missing.push(part);
    }

    return { complete: missing.length === 0, missing };
}
