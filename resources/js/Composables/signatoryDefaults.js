/**
 * Central signatory defaults (GET /api/signatory-defaults).
 *
 * All signatory configuration now lives in the Signatories module; the
 * per-report pickers are gone and the backend resolves each document's
 * signature block server-side from these defaults. This file is the single
 * client-side reader for that contract:
 *
 *   GET /api/signatory-defaults -> { defaults: { swa, dtr, certification, fund }, offices }
 *   each default D (or null) = {
 *     document_type, office_head_signatory_id,
 *     office_head: { id, name, office, titles } | null,
 *     signatory_titles, signatory_name_underline,
 *     signatory_show_designation, signatory_show_office, signatory_info_order,
 *   }
 *   offices = main offices with their assignments (specific
 *   offices/programs), each carrying its assigned head id, from the
 *   Offices module.
 *
 * Display resolution (resolveDefaultDisplay):
 * - The document office's head in the Offices module wins first: the
 *   assignment's own head, else its main office's head, mirroring the
 *   backend.
 * - A saved default whose office head still exists wins, with the live
 *   office-head row (name/office) taking precedence over the snapshot copy.
 * - A saved default whose office head is gone is STALE: it resolves to no
 *   head, so callers show "update it in Signatories" guidance instead of
 *   silently falling back to another head (which would rewrite history).
 * - No saved default at all falls back to the first office head with all
 *   of its titles, matching the note shown in the Signatories module
 *   ("reports will use the first office head until a default is saved").
 * - No office heads at all resolves to null: the caller keeps its existing
 *   "no signatory configured" guard.
 */
import axios from 'axios';

export const SIGNATORY_DEFAULT_TYPES = [
    { key: 'swa', label: 'SWA' },
    { key: 'dtr', label: 'DTR' },
    { key: 'certification', label: 'Certification' },
    { key: 'fund', label: 'Fund' },
];

export function toTitleList(value) {
    if (Array.isArray(value)) {
        return value.map((title) => String(title ?? '').trim()).filter(Boolean);
    }

    const text = String(value ?? '').trim();
    return text ? [text] : [];
}

export function normalizeSignatoryDefault(raw) {
    if (!raw) return null;

    return {
        document_type: raw.document_type ?? null,
        office_head_signatory_id: raw.office_head_signatory_id ?? null,
        office_head: raw.office_head
            ? {
                id: raw.office_head.id ?? null,
                name: String(raw.office_head.name ?? '').trim(),
                office: String(raw.office_head.office ?? '').trim() || null,
                titles: toTitleList(raw.office_head.titles ?? raw.office_head.title),
            }
            : null,
        signatory_titles: Array.isArray(raw.signatory_titles)
            ? toTitleList(raw.signatory_titles)
            : null,
        signatory_name_underline: raw.signatory_name_underline === true,
        signatory_show_designation: raw.signatory_show_designation !== false,
        signatory_show_office: raw.signatory_show_office !== false,
        signatory_info_order: raw.signatory_info_order === 'office_first'
            ? 'office_first'
            : 'designation_first',
    };
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
            }
            : null,
        children: (raw.children ?? []).map(normalizeOffice).filter(Boolean),
    };
}

export async function fetchSignatoryDefaults() {
    const { data } = await axios.get('/api/signatory-defaults');
    const defaults = data?.defaults ?? {};

    return {
        swa: normalizeSignatoryDefault(defaults.swa),
        dtr: normalizeSignatoryDefault(defaults.dtr),
        certification: normalizeSignatoryDefault(defaults.certification),
        fund: normalizeSignatoryDefault(defaults.fund),
        offices: (data?.offices ?? []).map(normalizeOffice).filter(Boolean),
    };
}

/** Flatten the office tree for name lookup: each assignment keeps its main office's head id as the fallback. */
function flattenOffices(offices) {
    const flat = [];

    for (const main of offices ?? []) {
        flat.push({ name: main?.name ?? null, headId: main?.office_head_signatory_id ?? null, parentHeadId: null });

        for (const child of main?.children ?? []) {
            flat.push({ name: child?.name ?? null, headId: child?.office_head_signatory_id ?? null, parentHeadId: main?.office_head_signatory_id ?? null });
        }
    }

    return flat;
}

/**
 * Resolve one document type's display block. `fallbackUnderline` is the
 * underline value used only for the unsaved-default fallback (SWA/DTR
 * previously defaulted to underlined, certifications to not underlined);
 * a saved default always supplies its own flag. `office`, when given,
 * resolves the Offices-module head first (the assignment's own head,
 * else its main office's head, case-insensitive, trimmed), mirroring
 * the backend. The saved title subset only applies when the resolved head
 * is the saved head.
 * Returns null, or { head, titles, underline, showDesignation, showOffice,
 * infoOrder, saved, stale }.
 */
export function resolveDefaultDisplay(def, officeHeads, fallbackUnderline, office = null, offices = []) {
    const heads = (officeHeads ?? []).map((head) => ({
        ...head,
        name: String(head?.name ?? '').trim(),
        office: String(head?.office ?? '').trim() || null,
        titles: toTitleList(head?.titles ?? head?.title),
    })).filter((head) => head.id != null);

    if (!heads.length) return null;

    const wanted = String(office ?? '').trim().toLowerCase();
    const entry = wanted
        ? flattenOffices(offices).find((item) => String(item?.name ?? '').trim().toLowerCase() === wanted) ?? null
        : null;
    const officeHeadId = entry ? (entry.headId ?? entry.parentHeadId) : null;
    const resolved = officeHeadId ? heads.find((head) => head.id === officeHeadId) ?? null : null;

    if (resolved) {
        const useSavedTitles = def
            && def.office_head_signatory_id === resolved.id
            && Array.isArray(def.signatory_titles);

        return {
            head: resolved,
            titles: useSavedTitles ? def.signatory_titles : [...resolved.titles],
            underline: def ? def.signatory_name_underline : Boolean(fallbackUnderline),
            showDesignation: def ? def.signatory_show_designation : true,
            showOffice: def ? def.signatory_show_office : true,
            infoOrder: def ? def.signatory_info_order : 'designation_first',
            saved: Boolean(def),
            stale: false,
        };
    }

    if (def) {
        const liveHead = heads.find((head) => head.id === def.office_head_signatory_id) ?? null;
        const head = liveHead ?? def.office_head;

        if (!head) {
            return { head: null, titles: [], underline: false, showDesignation: true, showOffice: true, infoOrder: 'designation_first', saved: true, stale: true };
        }

        return {
            head,
            titles: Array.isArray(def.signatory_titles) ? def.signatory_titles : [...head.titles],
            underline: def.signatory_name_underline,
            showDesignation: def.signatory_show_designation,
            showOffice: def.signatory_show_office,
            infoOrder: def.signatory_info_order,
            saved: true,
            stale: false,
        };
    }

    const first = heads[0];

    return {
        head: first,
        titles: [...first.titles],
        underline: Boolean(fallbackUnderline),
        showDesignation: true,
        showOffice: true,
        infoOrder: 'designation_first',
        saved: false,
        stale: false,
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
