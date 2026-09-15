<script setup>
const model = defineModel({ type: String, default: '' });
defineProps({ rows: { type: [String, Number], default: 3 } });
</script>

<template>
    <textarea
        v-model="model"
        :rows="rows"
        class="w-full rounded-lg border-gray-300 text-sm text-ink-800 shadow-sm focus:border-brand-500 focus:ring-brand-500"
    />
</template>
