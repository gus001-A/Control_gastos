<script setup>
defineProps({
    label: { type: String, required: true },
    value: { type: String, required: true },
    icon: { type: [Object, Function], default: null },
    subtitle: { type: String, default: '' },
    // Tarjeta negra (para destacar una cifra) en lugar de blanca
    oscuro: { type: Boolean, default: false },
});
</script>

<template>
    <div
        class="card-lift group relative overflow-hidden rounded-2xl border p-5 shadow-card"
        :class="oscuro ? 'border-neutral-800 bg-neutral-950 text-white' : 'border-neutral-200/80 bg-white'"
    >
        <!-- Halo morado que aparece al pasar el mouse -->
        <div
            class="pointer-events-none absolute -right-10 -top-10 h-32 w-32 rounded-full bg-purple-500/20 opacity-0 blur-2xl transition-opacity duration-500 group-hover:opacity-100"
        ></div>

        <div class="relative flex items-start justify-between">
            <span class="text-xs font-medium" :class="oscuro ? 'text-neutral-400' : 'text-neutral-500'">{{ label }}</span>
            <div
                v-if="icon"
                class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-purple-500 to-purple-800 text-white shadow-md shadow-purple-900/30 transition-transform duration-300 group-hover:rotate-6 group-hover:scale-110"
            >
                <component :is="icon" :size="18" stroke-width="2.2" />
            </div>
        </div>
        <div
            class="relative mt-3 text-2xl font-bold tracking-tight tabular-nums"
            :class="oscuro ? 'text-white' : 'text-neutral-950'"
        >
            {{ value }}
        </div>
        <div v-if="subtitle" class="relative mt-1 text-xs" :class="oscuro ? 'text-neutral-400' : 'text-neutral-500'">{{ subtitle }}</div>
    </div>
</template>
