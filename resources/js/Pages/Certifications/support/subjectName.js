function normalizeText(value) {
    return typeof value === 'string' ? value.trim() : '';
}

function splitLegacySubjectName(value) {
    const parts = normalizeText(value).split(/\s+/).filter(Boolean);

    if (!parts.length) {
        return {
            firstname: '',
            middlename: '',
            lastname: '',
        };
    }

    if (parts.length === 1) {
        return {
            firstname: parts[0],
            middlename: '',
            lastname: '',
        };
    }

    return {
        firstname: parts[0],
        middlename: parts.slice(1, -1).join(' '),
        lastname: parts[parts.length - 1],
    };
}

function lastToken(value) {
    const parts = normalizeText(value).split(/\s+/).filter(Boolean);

    return parts.length ? parts[parts.length - 1] : '';
}

export function resolveCertificationSubjectParts(certification) {
    const firstname = normalizeText(certification?.subject_firstname);
    const middlename = normalizeText(certification?.subject_middlename);
    const lastname = normalizeText(certification?.subject_lastname);

    if (firstname || middlename || lastname) {
        return {
            firstname,
            middlename,
            lastname,
        };
    }

    return splitLegacySubjectName(certification?.subject_name);
}

export function buildCertificationSubjectName(certification) {
    const { firstname, middlename, lastname } = resolveCertificationSubjectParts(certification);

    return [firstname, middlename, lastname].filter(Boolean).join(' ');
}

export function buildCertificationSubjectDisplayName(certification) {
    const honorific = normalizeText(certification?.subject_honorific);
    const subjectName = buildCertificationSubjectName(certification);

    return [honorific, subjectName].filter(Boolean).join(' ') || '—';
}

export function buildCertificationSubjectShortReference(certification) {
    const honorific = normalizeText(certification?.subject_honorific);
    const { lastname } = resolveCertificationSubjectParts(certification);
    const shortName = lastname || lastToken(buildCertificationSubjectName(certification));

    return [honorific, shortName].filter(Boolean).join(' ') || buildCertificationSubjectDisplayName(certification);
}