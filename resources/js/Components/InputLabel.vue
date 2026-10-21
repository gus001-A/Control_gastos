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
    <label class="flex items-center gap-1.5 text-[13px] font-medium text-neutral-700">
        <component :is="icon" v-if="icon" :size="12" />
        <span v-if="value">{{ value }}</span>
        <span v-else><slot /></span>
    </label>
</template>
