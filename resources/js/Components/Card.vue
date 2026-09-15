<script setup>
defineProps({
    title: { type: String, default: '' },
    padded: { type: Boolean, default: true },
});
</script>

<template>
    <div class="rounded-2xl border border-ink-100 bg-white shadow-card transition-shadow duration-200 hover:shadow-glow">
        <div v-if="title || $slots.actions" class="flex items-center justify-between border-b border-ink-100 px-5 py-4">
            <h3 class="text-sm font-bold text-ink-800">{{ title }}</h3>
            <div v-if="$slots.actions"><slot name="actions" /></div>
        </div>
        <div :class="padded ? 'p-5' : ''">
            <slot />
        </div>
    </div>
</template>
