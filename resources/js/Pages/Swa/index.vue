<script setup>
import { ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import WorkforceLayout from '@/Layouts/WorkforceLayout.vue';
import PersonalSwaModal from '@/Pages/Swa/Modal/PersonalSwaModal.vue';
import EmployeeSwaModal from '@/Pages/Swa/Modal/EmployeeSwaModal.vue';

defineOptions({ layout: WorkforceLayout });

const showPersonalSwaModal = ref(false);
const showEmployeeSwaModal = ref(false);
</script>

<template>

    <Head title="SWA" />

    <div class="flex items-center justify-between mb-5 gap-4">
        <div>
            <h1 class="text-xl font-bold text-surface-800 dark:text-surface-50">SWA</h1>
            <p class="text-sm text-surface-400 mt-0.5">Choose a submodule to open its base view.</p>
        </div>
    </div>

    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        <button type="button"
            class="ios-card p-5 text-left transition-all hover:-translate-y-0.5 hover:shadow-lg cursor-pointer"
            @click="showPersonalSwaModal = true">
            <div class="flex items-start justify-between gap-4">
                <div class="flex items-start gap-3 min-w-0">
                    <div
                        class="w-12 h-12 rounded-2xl bg-emerald-100 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300 flex items-center justify-center shrink-0">
                        <i class="pi pi-user text-lg"></i>
                    </div>
                    <div class="min-w-0">
                        <div class="flex items-center gap-2 flex-wrap">
                            <p class="text-base font-semibold text-surface-800 dark:text-surface-100">Personal SWA</p>
                            <Tag value="Ready" severity="success" />
                        </div>
                        <p class="text-sm text-surface-500 mt-1">
                            Encode five tasks and generate SWA for the signed-in account user.
                        </p>
                    </div>
                </div>

                <i class="pi pi-angle-right text-surface-400 text-lg mt-1"></i>
            </div>
        </button>

        <button type="button"
            class="ios-card p-5 text-left transition-all hover:-translate-y-0.5 hover:shadow-lg cursor-pointer"
            @click="showEmployeeSwaModal = true">
            <div class="flex items-start justify-between gap-4">
                <div class="flex items-start gap-3 min-w-0">
                    <div
                        class="w-12 h-12 rounded-2xl bg-sky-100 text-sky-700 dark:bg-sky-950/40 dark:text-sky-300 flex items-center justify-center shrink-0">
                        <i class="pi pi-users text-lg"></i>
                    </div>
                    <div class="min-w-0">
                        <div class="flex items-center gap-2 flex-wrap">
                            <p class="text-base font-semibold text-surface-800 dark:text-surface-100">Employee SWA</p>
                            <Tag value="Ready" severity="success" />
                        </div>
                        <p class="text-sm text-surface-500 mt-1">
                            Encode five tasks and generate SWA records on behalf of other employees.
                        </p>
                    </div>
                </div>

                <i class="pi pi-angle-right text-surface-400 text-lg mt-1"></i>
            </div>
        </button>
    </div>

    <PersonalSwaModal v-model:show="showPersonalSwaModal" />
    <EmployeeSwaModal v-model:show="showEmployeeSwaModal" />
</template>