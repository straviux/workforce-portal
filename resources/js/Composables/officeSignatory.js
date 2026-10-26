/**
 * Office signatory display (GET /api/offices).
 *
 * Document defaults are gone: the backend resolves each document's
 * signature block server-side from the Offices module, and this file is
 * the single client-side reader for the matching preview:
 *
 *   GET /api/offices -> main offices with their assignments (specific
 *   offices/programs), each carrying its assigned head id and head
 *   object ({ id, name, office, titles }).
 *
 * Display resolution (resolveOfficeDisplay):
 * - The document office's head wins first: the assignment's own head,
 *   else its main office's head, mirroring the backend. The live
 *   Part A head row takes precedence over the tree's head copy.
 * - An unknown office, or an office with no head anywhere up its chain,
 *   falls back to the first office head with all of its titles.
 * - No office heads at all resolves to null: the caller keeps its
 *   existing "no signatory configured" guard.
 * - Display flags are fixed (no underline, show designation and office,
 *   designation first), matching the server snapshot.
 */
import axios from 'axios';

/** Document scopes a Part A title can render in, in display order. */
export const TITLE_SCOPES = [
    { value: 'swa', label: 'SWA' },
    { value: 'dtr', label: 'DTR' },
    { value: 'certification', label: 'Certification' },
    { value: 'cwa', label: 'CWA' },
    { value: 'payroll', label: 'Payroll' },
];

export const ALL_TITLE_SCOPES = TITLE_SCOPES.map((scope) => scope.value);

export function titleScopeLabel(value) {
    return TITLE_SCOPES.find((scope) => scope.value === value)?.label ?? value;
}

export function toTitleList(value) {
    return titleTexts(normalizeTitleEntries(value));
}

/** Normalize stored titles to [{ text, scopes }]. Plain strings render everywhere. */
export function normalizeTitleEntries(value) {
    const list = Array.isArray(value) ? value : (value ? [value] : []);

    return list
        .map((title) => {
            if (typeof title === 'string') {
                return { text: title.trim(), scopes: [...ALL_TITLE_SCOPES] };
            }

            if (title && typeof title === 'object') {
                const scopes = Array.isArray(title.scopes) && title.scopes.length
                    ? title.scopes.filter((scope) => ALL_TITLE_SCOPES.includes(scope))
                    : [...ALL_TITLE_SCOPES];

                return { text: String(title.text ?? '').trim(), scopes };
            }

            return null;
        })
        .filter((entry) => entry && entry.text);
}

/** Plain texts from title entries, filtered to a scope (null = all). */
export function titleTexts(entries, scope = null) {
    const list = normalizeTitleEntries(entries);

    return list
        .filter((entry) => scope === null
            || !ALL_TITLE_SCOPES.includes(scope)
            || entry.scopes.includes(scope))
        .map((entry) => entry.text);
}

export function normalizeOffice(raw) {
    if (!raw) return null;

    return {
        id: raw.id ?? null,
        name: String(raw.name ?? '').trim() || null,
        parent_id: raw.parent_id ?? null,
        office_head_signatory_id: raw.office_head_signatory_id ?? null,
        office_head: raw.office_head
            ? {
                id: raw.office_head.id ?? null,
                name: String(raw.office_head.name ?? '').trim(),
                office: String(raw.office_head.office ?? '').trim() || null,
                titles: toTitleList(raw.office_head.titles ?? raw.office_head.title),
                titleEntries: normalizeTitleEntries(raw.office_head.titles ?? raw.office_head.title),
            }
            : null,
        children: (raw.children ?? []).map(normalizeOffice).filter(Boolean),
    };
}

export async function fetchOffices() {
    const { data } = await axios.get('/api/offices');
    const tree = Array.isArray(data) ? data : (data?.data ?? []);

    return tree.map(normalizeOffice).filter(Boolean);
}

/** Flatten the office tree for name lookup: each assignment keeps its main office's head as the fallback. */
function flattenOffices(offices) {
    const flat = [];

    for (const main of offices ?? []) {
        flat.push({
            name: main?.name ?? null,
            headId: main?.office_head_signatory_id ?? null,
            head: main?.office_head ?? null,
            parentHeadId: null,
            parentHead: null,
        });

        for (const child of main?.children ?? []) {
            flat.push({
                name: child?.name ?? null,
                headId: child?.office_head_signatory_id ?? null,
                head: child?.office_head ?? null,
                parentHeadId: main?.office_head_signatory_id ?? null,
                parentHead: main?.office_head ?? null,
            });
        }
    }

    return flat;
}

/**
 * Resolve one document office's display block for a document scope
 * ('swa', 'dtr', 'certification'; null = all titles).
 * Returns null, or { head, titles, underline, showDesignation, showOffice,
 * infoOrder }.
 */
export function resolveOfficeDisplay(officeHeads, offices, office = null, scope = null) {
    const heads = (officeHeads ?? []).map((head) => ({
        ...head,
        name: String(head?.name ?? '').trim(),
        office: String(head?.office ?? '').trim() || null,
        titles: toTitleList(head?.titles ?? head?.title),
        titleEntries: normalizeTitleEntries(head?.titles ?? head?.title),
    })).filter((head) => head.id != null);

    if (!heads.length) return null;

    const wanted = String(office ?? '').trim().toLowerCase();
    const entry = wanted
        ? flattenOffices(offices).find((item) => String(item?.name ?? '').trim().toLowerCase() === wanted) ?? null
        : null;

    const resolved = entry
        ? heads.find((head) => head.id === entry.headId)
            ?? entry.head
            ?? heads.find((head) => head.id === entry.parentHeadId)
            ?? entry.parentHead
            ?? null
        : null;

    const head = resolved ?? heads[0];
    const entries = head.titleEntries ?? normalizeTitleEntries(head.titles);

    return {
        head,
        titles: titleTexts(entries, scope),
        underline: false,
        showDesignation: true,
        showOffice: true,
        infoOrder: 'designation_first',
    };
}

/** Detail lines (titles + office) for a resolved display block, in order. */
export function defaultDisplayLines(display) {
    if (!display?.head) return [];

    const designationLines = display.showDesignation ? display.titles : [];
    const officeLines = display.showOffice && display.head.office ? [display.head.office] : [];
    const seen = new Set();

    return (display.infoOrder === 'office_first'
        ? [...officeLines, ...designationLines]
        : [...designationLines, ...officeLines])
        .map((line) => String(line ?? '').trim())
        .filter((line) => {
            if (!line) return false;
            const key = line.toLowerCase();
            if (seen.has(key)) return false;
            seen.add(key);
            return true;
        });
}
