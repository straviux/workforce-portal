<script setup>
import { computed } from "vue";
import { Link } from "@inertiajs/vue3";

const props = defineProps({
    href: {
        type: String,
        required: true,
    },
    active: {
        type: Boolean,
        default: false,
    },
});

const classes = computed(() =>
    props.active
        ? "relative px-4 py-3 flex rounded-2xl text-white bg-linear-to-r from-[#2b5876] to-[#4e4376]"
        : "px-4 py-3 flex rounded-2xl text-gray-400 hover:text-white hover:bg-white/10 group"
);
</script>

<template>
    <Link :href="href" :class="classes">
        <slot />
    </Link>
</template>
