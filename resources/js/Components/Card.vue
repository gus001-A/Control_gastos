<script setup>
defineProps({
    title: { type: String, default: '' },
    icon: { type: [Object, Function], default: null },
    padded: { type: Boolean, default: true },
});
</script>

<template>
    <div class="rounded-2xl border border-ink-100 bg-white shadow-card transition-shadow duration-200 hover:shadow-glow">
        <div v-if="title || $slots.actions" class="flex items-center justify-between border-b border-ink-100 px-5 py-4">
            <h3 class="inline-flex items-center gap-1.5 text-sm font-bold text-ink-800">
                <component :is="icon" v-if="icon" :size="15" class="text-brand-600" />
                {{ title }}
            </h3>
            <div v-if="$slots.actions"><slot name="actions" /></div>
        </div>
        <div :class="padded ? 'p-5' : ''">
            <slot />
        </div>
    </div>
</template>
