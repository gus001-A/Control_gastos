<script setup>
defineProps({
    value: {
        type: String,
    },
    icon: {
        type: [Object, Function],
        default: null,
    },
});
</script>

<template>
    <label class="flex items-center gap-1.5 text-xs font-semibold uppercase tracking-wide text-ink-400">
        <component :is="icon" v-if="icon" :size="12" />
        <span v-if="value">{{ value }}</span>
        <span v-else><slot /></span>
    </label>
</template>
