<script setup>
defineProps({
    disabled: Boolean,
});
</script>

<template>
    <button
        :disabled="disabled"
        class="shine inline-flex h-10 items-center justify-center gap-1.5 rounded-xl border border-transparent bg-gradient-to-br from-purple-600 via-purple-700 to-neutral-950 px-4 text-sm font-semibold text-white shadow-md shadow-purple-900/25 transition-all duration-300 hover:-translate-y-0.5 hover:shadow-lg hover:shadow-purple-700/40 focus:outline-none focus-visible:ring-2 focus-visible:ring-purple-500 focus-visible:ring-offset-2 active:translate-y-0 active:scale-95 disabled:cursor-not-allowed disabled:opacity-50"
    >
        <slot />
    </button>
</template>
