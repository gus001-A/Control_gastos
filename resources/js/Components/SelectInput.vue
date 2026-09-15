<script setup>
const model = defineModel({ required: true });
</script>

<template>
    <select
        v-model="model"
        class="w-full rounded-lg border-ink-200 text-sm text-ink-800 shadow-sm transition focus:border-brand-500 focus:ring-brand-500"
    >
        <slot />
    </select>
</template>
