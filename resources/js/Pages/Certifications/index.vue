<template>

    <Head title="Certifications" />

    <div class="flex items-center justify-between mb-5 gap-4">
        <div>
            <h1 class="text-xl font-bold text-surface-800 dark:text-surface-50">Certifications</h1>
            <p class="text-sm text-surface-400 mt-0.5">Choose a certification submodule to manage its records.</p>
        </div>
    </div>

    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        <button type="button"
            class="ios-card p-5 text-left transition-all hover:-translate-y-0.5 hover:shadow-lg cursor-pointer"
            @click="openNonRosRegistry">
            <div class="flex items-start justify-between gap-4">
                <div class="flex items-start gap-3 min-w-0">
                    <div
                        class="w-12 h-12 rounded-2xl bg-sky-100 text-sky-700 dark:bg-sky-950/40 dark:text-sky-300 flex items-center justify-center shrink-0">
                        <i class="pi pi-file-edit text-lg"></i>
                    </div>
                    <div class="min-w-0">
                        <div class="flex items-center gap-2 flex-wrap">
                            <p class="text-base font-semibold text-surface-800 dark:text-surface-100">Certification for
                                Non-ROS</p>
                            <Tag value="Available" severity="success" />
                        </div>
                        <p class="text-sm text-surface-500 mt-1">
                            Manual entries for Non-ROS certifications with saved print records.
                        </p>
                    </div>
                </div>

                <i class="pi pi-angle-right text-surface-400 text-lg mt-1"></i>
            </div>

            <div class="mt-4 flex flex-wrap items-center gap-2 text-xs text-surface-500">
                <Tag :value="`${pagination.filtered_total} saved`" severity="secondary" />
                <span>{{ officeHeads.length }} office head{{ officeHeads.length !== 1 ? 's' : '' }} configured</span>
            </div>
        </button>
    </div>

    <Dialog v-model:visible="showNonRosRegistry" modal :draggable="false" :closable="false"
        :pt="{ root: { class: 'ios-dialog-root' }, mask: { class: 'ios-dialog-mask' } }">
        <template #container>
            <div class="ios-modal w-[98vw] max-w-[1480px]" :style="registryModalStyle">
                <div class="ios-nav-bar cursor-grab active:cursor-grabbing select-none"
                    @pointerdown="onRegistryDragStart">
                    <button type="button" class="ios-nav-btn ios-nav-cancel" @click="closeNonRosRegistry">
                        <i class="pi pi-times"></i>
                    </button>

                    <div class="min-w-0 flex-1 px-3 text-center leading-tight">
                        <span class="ios-nav-title block">Certification for Non-ROS</span>

                    </div>

                    <button v-if="canManageCertifications" type="button" class="ios-nav-btn ios-nav-action"
                        :disabled="!officeHeads.length" @click="openCreate">
                        Add
                    </button>
                    <div v-else class="min-w-[3.5rem] shrink-0"></div>
                </div>

                <div class="ios-body max-h-[88vh] overflow-y-auto">
                    <div class="ios-section pb-4">
                        <div class="ios-card p-4 flex items-start justify-between gap-4 flex-wrap">
                            <div>
                                <p class="text-sm font-medium text-surface-800 dark:text-surface-100">Saved
                                    Certifications</p>
                                <p class="text-sm text-surface-500 mt-1">
                                    Create, review, and print Non-ROS certifications from this registry.
                                </p>
                            </div>

                            <Tag value="Non-ROS" severity="info" />
                        </div>
                    </div>

                    <div class="ios-section">
                        <p class="ios-section-label">Search</p>
                        <div class="ios-card flex flex-wrap gap-3 p-4">
                            <IconField class="flex-1 min-w-60">
                                <InputIcon class="pi pi-search" />
                                <InputText v-model="filters.search"
                                    placeholder="Search name, designation, office, or signatory" class="w-full"
                                    size="small" @keyup.enter="fetchCertifications(1)" />
                            </IconField>

                            <Button icon="pi pi-filter-slash" severity="secondary" size="small" class="rounded" outlined
                                v-tooltip="'Reset filters'" @click="resetFilters" />
                        </div>
                    </div>

                    <div class="ios-section pb-4">
                        <p class="ios-section-label">

                        <p class="text-[11px] text-surface-400 mt-0.5">
                            {{ pagination.filtered_total }} record{{ pagination.filtered_total !== 1
                                ? 's' : '' }}
                        </p>
                        </p>

                        <div class="overflow-hidden ios-card"
                            style="border-radius:1.5rem;border:1px solid var(--p-datatable-border-color);">
                            <DataTable :value="certifications" :loading="loading" showGridlines stripedRows scrollable
                                lazy :totalRecords="pagination.filtered_total" :rows="pagination.per_page"
                                :first="paginatorFirst" paginator @page="onPage" :rowsPerPageOptions="[15, 25, 50]"
                                scrollHeight="58vh"
                                paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink RowsPerPageDropdown"
                                :pt="{
                                    root: { style: 'border-radius:0;border:none;' },
                                    tableContainer: { style: 'border-radius:0;' },
                                    paginator: { style: 'border:none;border-top:1px solid var(--p-datatable-border-color);' }
                                }">
                                <template #empty>
                                    <div class="text-center py-12 text-surface-400">
                                        <i class="pi pi-file-edit text-4xl block mb-3"></i>
                                        <p class="text-sm">No saved certifications found.</p>
                                    </div>
                                </template>

                                <Column field="subject_name" header="Name" style="min-width:220px;">
                                    <template #body="{ data }">
                                        <div>
                                            <p class="font-medium text-surface-800 dark:text-surface-100">{{
                                                formatSubjectDisplayName(data) }}</p>
                                            <p class="text-xs text-surface-400">Saved {{ formatDateTime(data.created_at)
                                                }}</p>
                                        </div>
                                    </template>
                                </Column>

                                <Column field="designation" header="Designation" style="min-width:220px;" />

                                <Column field="office" header="Office" style="min-width:200px;" />

                                <Column header="Signatory" style="min-width:190px;">
                                    <template #body="{ data }">
                                        <div>
                                            <p class="font-medium text-surface-800 dark:text-surface-100">{{
                                                data.signatory_name }}</p>
                                            <p class="text-xs text-surface-400">{{ data.signatory_office || '—' }}</p>
                                        </div>
                                    </template>
                                </Column>

                                <Column header="Issued Date" style="min-width:140px;">
                                    <template #body="{ data }">
                                        <span>{{ formatDate(data.issued_date) }}</span>
                                    </template>
                                </Column>

                                <Column header="Created By" style="min-width:160px;">
                                    <template #body="{ data }">
                                        <span>{{ data.creator?.name || '—' }}</span>
                                    </template>
                                </Column>

                                <Column header="" style="width:200px;min-width:200px;" frozen alignFrozen="right">
                                    <template #body="{ data }">
                                        <div class="flex items-center justify-end gap-2">
                                            <Button icon="pi pi-print" label="Print" class="rounded" size="small"
                                                @click="printCertification(data)" />
                                            <Button v-if="canManageCertifications" icon="pi pi-pencil"
                                                severity="secondary" text rounded size="small"
                                                @click="openEdit(data)" />
                                        </div>
                                    </template>
                                </Column>
                            </DataTable>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </Dialog>

    <NonRosCertificationModal v-model:show="showCertificationModal" :mode="certificationModalMode"
        :certification="selectedCertification" :office-heads="officeHeads" @saved="onSaved" />

    <PdfPreviewModal v-model:show="pdfPreview.show" :html-doc="pdfPreview.html" :paper-size="pdfPreview.size"
        :title="pdfPreview.title" />
</template>

<script setup>
import { computed, onBeforeUnmount, onMounted, reactive, ref } from 'vue';
import { Head, usePage } from '@inertiajs/vue3';
import axios from 'axios';
import { useToast } from 'primevue/usetoast';
import WorkforceLayout from '@/Layouts/WorkforceLayout.vue';
import { renderVueTemplate, usePdfPrint } from '@/composables/usePdfPrint';
import PdfPreviewModal from '@/Pages/EmployeeFundTransactions/Modal/PdfPreviewModal.vue';
import NonRosCertificationModal from '@/Pages/Certifications/Modal/NonRosCertificationModal.vue';
import NonRosCertificationTemplate from '@/Pages/Certifications/Pdf/NonRosCertificationTemplate.vue';

defineOptions({ layout: WorkforceLayout });

const toast = useToast();
const page = usePage();
const { buildHtmlDoc } = usePdfPrint();

const certifications = ref([]);
const officeHeads = ref([]);
const loading = ref(false);
const showNonRosRegistry = ref(false);
const showCertificationModal = ref(false);
const certificationModalMode = ref('create');
const selectedCertification = ref(null);
const registryDragOffset = ref({ x: 0, y: 0 });
const registryDragStart = ref(null);

const pagination = reactive({
    filtered_total: 0,
    per_page: 15,
    current_page: 1,
    last_page: 1,
});

const filters = reactive({
    search: '',
});

const pdfPreview = reactive({
    show: false,
    html: '',
    title: '',
    size: 'a4',
});

const paginatorFirst = computed(() => (pagination.current_page - 1) * pagination.per_page);
const currentPermissions = computed(() => page.props.auth?.user?.permissions ?? []);
const canManageCertifications = computed(() => currentPermissions.value.includes('certifications.manage'));
const registryModalStyle = computed(() => ({
    transform: `translate(${registryDragOffset.value.x}px, ${registryDragOffset.value.y}px)`,
}));

async function fetchCertifications(pageNumber = pagination.current_page) {
    loading.value = true;

    try {
        const params = {
            page: pageNumber,
            per_page: pagination.per_page,
        };

        if (filters.search) params.search = filters.search;

        const { data } = await axios.get('/api/certifications/non-ros', { params });
        certifications.value = data.data ?? [];
        officeHeads.value = data.office_heads ?? [];
        pagination.filtered_total = data.filtered_total ?? 0;
        pagination.per_page = data.per_page ?? 15;
        pagination.current_page = data.current_page ?? 1;
        pagination.last_page = data.last_page ?? 1;
    } catch {
        toast.add({ severity: 'error', summary: 'Error', detail: 'Could not load certifications.', life: 3500 });
    } finally {
        loading.value = false;
    }
}

function onPage(event) {
    pagination.per_page = event.rows;
    fetchCertifications(event.page + 1);
}

function resetFilters() {
    filters.search = '';
    fetchCertifications(1);
}

function openNonRosRegistry() {
    registryDragOffset.value = { x: 0, y: 0 };
    showNonRosRegistry.value = true;
    fetchCertifications(1);
}

function closeNonRosRegistry() {
    showNonRosRegistry.value = false;
}

function onRegistryDragStart(event) {
    if (event.target.closest('button, input, textarea, .p-inputtext, .p-datepicker, .p-select, .p-button')) return;

    registryDragStart.value = {
        x: event.clientX - registryDragOffset.value.x,
        y: event.clientY - registryDragOffset.value.y,
    };

    window.addEventListener('pointermove', onRegistryDragMove);
    window.addEventListener('pointerup', onRegistryDragEnd);
}

function onRegistryDragMove(event) {
    if (!registryDragStart.value) return;

    registryDragOffset.value = {
        x: event.clientX - registryDragStart.value.x,
        y: event.clientY - registryDragStart.value.y,
    };
}

function onRegistryDragEnd() {
    registryDragStart.value = null;
    window.removeEventListener('pointermove', onRegistryDragMove);
    window.removeEventListener('pointerup', onRegistryDragEnd);
}

function openCreate() {
    selectedCertification.value = null;
    certificationModalMode.value = 'create';
    showCertificationModal.value = true;
}

function openEdit(certification) {
    selectedCertification.value = certification;
    certificationModalMode.value = 'edit';
    showCertificationModal.value = true;
}

function onSaved() {
    showCertificationModal.value = false;
    fetchCertifications(pagination.current_page);
}

function printCertification(certification) {
    const certificationHtml = renderVueTemplate(NonRosCertificationTemplate, {
        certification,
    });

    const bodyHtml = '<style>@page { margin: 12mm 22mm; } @media screen { body { padding: 12mm 22mm; font-family: Verdana, Geneva, sans-serif; } } @media print { body { padding: 0; font-family: Verdana, Geneva, sans-serif; } }</style>' + certificationHtml;

    const safeName = String(certification.subject_name ?? `Certification_${certification.id}`)
        .replace(/\s+/g, '_');

    pdfPreview.title = `Non-ROS-Certification-${safeName}`;
    pdfPreview.size = 'a4';
    pdfPreview.html = buildHtmlDoc(bodyHtml, pdfPreview.title, 'a4');
    pdfPreview.show = true;
}

function formatSubjectDisplayName(certification) {
    const honorific = String(certification?.subject_honorific ?? '').trim();
    const name = String(certification?.subject_name ?? '').trim();

    return [honorific, name].filter(Boolean).join(' ') || '—';
}

function formatDate(value) {
    if (!value) return '—';

    return new Date(value).toLocaleDateString('en-PH', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
}

function formatDateTime(value) {
    if (!value) return '—';

    return new Date(value).toLocaleString('en-PH', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: 'numeric',
        minute: '2-digit',
    });
}

onMounted(() => {
    fetchCertifications(1);
});

onBeforeUnmount(() => {
    onRegistryDragEnd();
});
</script>