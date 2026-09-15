<script setup>
defineProps({
    disabled: Boolean,
});
</script>

<template>
    <button
        :disabled="disabled"
        class="inline-flex items-center rounded-lg border border-transparent bg-brand-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition duration-150 ease-in-out hover:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2 active:bg-brand-800 disabled:opacity-50"
        :class="{ 'opacity-25': disabled }"
    >
        <slot />
    </button>
</template>
