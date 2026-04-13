<script setup>
import { Head } from '@inertiajs/vue3';
import WorkforceLayout from '@/Layouts/WorkforceLayout.vue';
</script>

<template>
    <WorkforceLayout>
        <Head title="Dashboard" />
        <div class="flex items-center justify-center h-64 text-surface-400">
            <div class="text-center">
                <i class="pi pi-chart-bar text-5xl mb-3 block"></i>
                <p class="text-lg font-medium">Dashboard</p>
                <p class="text-sm mt-1">Coming soon.</p>
            </div>
        </div>
    </WorkforceLayout>
</template>
