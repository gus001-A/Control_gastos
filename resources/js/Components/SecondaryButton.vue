<script setup>
defineProps({
    type: {
        type: String,
        default: 'button',
    },
});
</script>

<template>
    <button
        :type="type"
        class="inline-flex h-10 items-center justify-center gap-1.5 rounded-xl border border-neutral-200 bg-white px-4 text-sm font-semibold text-neutral-900 shadow-sm transition-all duration-300 hover:-translate-y-0.5 hover:border-purple-300 hover:bg-purple-50 hover:text-purple-800 hover:shadow-md focus:outline-none focus-visible:ring-2 focus-visible:ring-purple-500 focus-visible:ring-offset-2 active:translate-y-0 active:scale-95 disabled:cursor-not-allowed disabled:opacity-50"
    >
        <slot />
    </button>
</template>
