<script setup>
defineProps({
    message: {
        type: String,
        default: '',
    },
});
</script>

<template>
    <div v-if="message" class="text-red-500 text-xs mt-1">{{ message }}</div>
</template>
