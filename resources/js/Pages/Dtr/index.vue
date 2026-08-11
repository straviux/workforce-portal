<script setup>
import { ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import WorkforceLayout from '@/Layouts/WorkforceLayout.vue';
import PersonalDtrModal from '@/Pages/Dtr/Modal/PersonalDtrModal.vue';
import EmployeeDtrModal from '@/Pages/Dtr/Modal/EmployeeDtrModal.vue';

defineOptions({ layout: WorkforceLayout });

const showPersonalDtrModal = ref(false);
const showEmployeeDtrModal = ref(false);
</script>

<template>

    <Head title="DTR" />

    <div class="flex items-center justify-between mb-5 gap-4">
        <div>
            <h1 class="text-xl font-bold text-surface-800 dark:text-surface-50">DTR</h1>
            <p class="text-sm text-surface-400 mt-0.5">Choose a submodule to open its base view.</p>
        </div>
    </div>

    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        <button type="button"
            class="ios-card p-5 text-left transition-all hover:-translate-y-0.5 hover:shadow-lg cursor-pointer"
            @click="showPersonalDtrModal = true">
            <div class="flex items-start justify-between gap-4">
                <div class="flex items-start gap-3 min-w-0">
                    <div
                        class="w-12 h-12 rounded-2xl bg-emerald-100 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300 flex items-center justify-center shrink-0">
                        <i class="pi pi-user text-lg"></i>
                    </div>
                    <div class="min-w-0">
                        <div class="flex items-center gap-2 flex-wrap">
                            <p class="text-base font-semibold text-surface-800 dark:text-surface-100">Personal DTR</p>
                            <Tag value="Ready" severity="success" />
                        </div>
                        <p class="text-sm text-surface-500 mt-1">
                            Log daily time in/out and generate your own Daily Time Record.
                        </p>
                    </div>
                </div>

                <i class="pi pi-angle-right text-surface-400 text-lg mt-1"></i>
            </div>
        </button>

        <button type="button"
            class="ios-card p-5 text-left transition-all hover:-translate-y-0.5 hover:shadow-lg cursor-pointer"
            @click="showEmployeeDtrModal = true">
            <div class="flex items-start justify-between gap-4">
                <div class="flex items-start gap-3 min-w-0">
                    <div
                        class="w-12 h-12 rounded-2xl bg-sky-100 text-sky-700 dark:bg-sky-950/40 dark:text-sky-300 flex items-center justify-center shrink-0">
                        <i class="pi pi-users text-lg"></i>
                    </div>
                    <div class="min-w-0">
                        <div class="flex items-center gap-2 flex-wrap">
                            <p class="text-base font-semibold text-surface-800 dark:text-surface-100">Employee DTR</p>
                            <Tag value="Ready" severity="success" />
                        </div>
                        <p class="text-sm text-surface-500 mt-1">
                            Encode time in/out and generate Daily Time Records on behalf of employees.
                        </p>
                    </div>
                </div>

                <i class="pi pi-angle-right text-surface-400 text-lg mt-1"></i>
            </div>
        </button>
    </div>

    <PersonalDtrModal v-model:show="showPersonalDtrModal" />
    <EmployeeDtrModal v-model:show="showEmployeeDtrModal" />
</template>
