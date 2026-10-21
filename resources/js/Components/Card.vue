<script setup>
defineProps({
    title: { type: String, default: '' },
    icon: { type: [Object, Function], default: null },
    padded: { type: Boolean, default: true },
});
</script>

<template>
    <div class="rounded-2xl border border-neutral-200/80 bg-white shadow-card transition-all duration-300 hover:border-purple-200 hover:shadow-glow">
        <div v-if="title || $slots.actions" class="flex items-center justify-between border-b border-neutral-100 px-5 py-4">
            <h3 class="inline-flex items-center gap-1.5 text-sm font-semibold text-neutral-900">
                <component :is="icon" v-if="icon" :size="15" class="text-purple-600" />
                {{ title }}
            </h3>
            <div v-if="$slots.actions"><slot name="actions" /></div>
        </div>
        <div :class="padded ? 'p-5' : ''">
            <slot />
        </div>
    </div>
</template>
