/**
 * Certification subject name helpers.
 *
 * Expected certification fields:
 *   subject_name       — pre-computed full name (fallback)
 *   subject_honorific  — e.g. Mr., Ms., Dr.
 *   subject_firstname  — e.g. Juan
 *   subject_middlename — e.g. Reyes
 *   subject_lastname   — e.g. Dela Cruz
 */

/**
 * Return a human-readable display name for the certification subject.
 * Includes honorific if present.
 *
 * @param {Object} cert
 * @returns {string}
 */
export function buildCertificationSubjectDisplayName(cert) {
    if (!cert) return '—';

    const parts = [
        cert.subject_honorific || '',
        cert.subject_firstname || '',
        cert.subject_middlename || '',
        cert.subject_lastname || '',
    ]
        .map((s) => (typeof s === 'string' ? s.trim() : ''))
        .filter(Boolean);

    return parts.length > 0 ? parts.join(' ') : (cert.subject_name || '—');
}

/**
 * Return the full subject name (no honorific).
 * Used for filename generation.
 *
 * @param {Object} cert
 * @returns {string}
 */
export function buildCertificationSubjectName(cert) {
    if (!cert) return '—';

    const parts = [
        cert.subject_firstname || '',
        cert.subject_middlename || '',
        cert.subject_lastname || '',
    ]
        .map((s) => (typeof s === 'string' ? s.trim() : ''))
        .filter(Boolean);

    return parts.length > 0 ? parts.join(' ') : (cert.subject_name || '—');
}

/**
 * Return a short reference for the subject (honorific + lastname).
 * e.g. "Mr. Dela Cruz"
 *
 * @param {Object} cert
 * @returns {string}
 */
export function buildCertificationSubjectShortReference(cert) {
    if (!cert) return '—';

    const honorific = typeof cert.subject_honorific === 'string'
        ? cert.subject_honorific.trim()
        : '';
    const lastname = typeof cert.subject_lastname === 'string'
        ? cert.subject_lastname.trim()
        : '';

    if (honorific && lastname) return `${honorific} ${lastname}`;
    if (lastname) return lastname;
    if (honorific) return honorific;

    return buildCertificationSubjectName(cert);
}

/**
 * Resolve subject name parts from a certification object.
 * Used to pre-populate the edit form.
 *
 * @param {Object} cert
 * @returns {{ firstname: string, middlename: string, lastname: string }}
 */
export function resolveCertificationSubjectParts(cert) {
    if (!cert) return { firstname: '', middlename: '', lastname: '' };

    const firstname = cert.subject_firstname ?? '';
    const middlename = cert.subject_middlename ?? '';
    const lastname = cert.subject_lastname ?? '';

    // Fallback: parse subject_name if individual fields are missing
    if (!firstname && !middlename && !lastname && cert.subject_name) {
        const nameParts = String(cert.subject_name).trim().split(/\s+/);
        return {
            firstname: nameParts[0] ?? '',
            middlename: nameParts.length > 2 ? nameParts.slice(1, -1).join(' ') : '',
            lastname: nameParts.length > 1 ? nameParts[nameParts.length - 1] : '',
        };
    }

    return {
        firstname: typeof firstname === 'string' ? firstname.trim() : '',
        middlename: typeof middlename === 'string' ? middlename.trim() : '',
        lastname: typeof lastname === 'string' ? lastname.trim() : '',
    };
}
