<script setup>
const model = defineModel({ required: true });
</script>

<template>
    <select
        v-model="model"
        class="w-full rounded-lg border-gray-300 text-sm text-ink-800 shadow-sm focus:border-brand-500 focus:ring-brand-500"
    >
        <slot />
    </select>
</template>
