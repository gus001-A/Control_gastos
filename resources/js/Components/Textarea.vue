<script setup>
const model = defineModel({ type: String, default: '' });
defineProps({ rows: { type: [String, Number], default: 3 } });
</script>

<template>
    <textarea
        v-model="model"
        :rows="rows"
        class="w-full rounded-lg border-neutral-200 text-sm text-neutral-800 shadow-sm transition placeholder:text-neutral-400 hover:border-neutral-300 focus:border-brand-500 focus:ring-2 focus:ring-brand-200"
    />
</template>
