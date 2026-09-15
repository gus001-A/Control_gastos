<script setup>
defineProps({
    label: { type: String, required: true },
    value: { type: String, required: true },
    icon: { type: [Object, Function], default: null },
    color: { type: String, default: '#9333ea' },
    subtitle: { type: String, default: '' },
});
</script>

<template>
    <div class="group rounded-2xl border border-ink-100 bg-white p-5 shadow-card transition-all duration-200 hover:-translate-y-0.5 hover:shadow-glow">
        <div class="flex items-start justify-between">
            <span class="text-xs font-semibold uppercase tracking-wide text-ink-400">{{ label }}</span>
            <div
                v-if="icon"
                class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl transition-transform duration-200 group-hover:scale-110"
                :style="{ backgroundColor: color + '14', color }"
            >
                <component :is="icon" :size="17" stroke-width="2.2" />
            </div>
        </div>
        <div class="mt-3 text-2xl font-extrabold tracking-tight" :style="{ color }">{{ value }}</div>
        <div v-if="subtitle" class="mt-1 text-xs text-ink-400">{{ subtitle }}</div>
    </div>
</template>
