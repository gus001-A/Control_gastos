<script setup>
defineProps({
    color: { type: String, default: '#64748b' },
});
</script>

<template>
    <span
        class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold"
        :style="{ backgroundColor: color + '1a', color }"
    >
        <slot />
    </span>
</template>
