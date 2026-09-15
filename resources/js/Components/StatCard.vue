<script setup>
defineProps({
    label: { type: String, required: true },
    value: { type: String, required: true },
    icon: { type: String, default: '' },
    color: { type: String, default: '#16a34a' },
    subtitle: { type: String, default: '' },
});
</script>

<template>
    <div class="rounded-2xl border border-gray-200/80 bg-white p-5 shadow-sm shadow-gray-200/50">
        <div class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wide text-gray-500">
            <span v-if="icon" class="text-base leading-none">{{ icon }}</span>
            {{ label }}
        </div>
        <div class="mt-2 text-2xl font-extrabold" :style="{ color }">{{ value }}</div>
        <div v-if="subtitle" class="mt-1 text-xs text-gray-400">{{ subtitle }}</div>
    </div>
</template>
