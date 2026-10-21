<script setup>
import { computed } from 'vue';
import { ChevronDown } from '@lucide/vue';

const model = defineModel({ required: true });

const props = defineProps({
    // Tamaños: sm | md | lg
    size: {
        type: String,
        default: 'md',
        validator: (v) => ['sm', 'md', 'lg'].includes(v),
    },
    // Deshabilita el select
    disabled: {
        type: Boolean,
        default: false,
    },
    // Marca el campo como inválido (borde rojo)
    error: {
        type: Boolean,
        default: false,
    },
});

// Clases dinámicas según el tamaño
const sizeClasses = computed(() => {
    switch (props.size) {
        case 'sm':
            return 'h-9 pl-3 pr-9 text-xs';
        case 'lg':
            return 'h-12 pl-4 pr-11 text-base';
        default:
            return 'h-10 pl-3.5 pr-10 text-sm';
    }
});

// Clases del chevron según el tamaño
const chevronClasses = computed(() => {
    switch (props.size) {
        case 'sm':
            return 'right-2.5 h-3.5 w-3.5';
        case 'lg':
            return 'right-3.5 h-4 w-4';
        default:
            return 'right-3 h-4 w-4';
    }
});
</script>

<template>
    <div class="relative">
        <select
            v-model="model"
            :disabled="disabled"
            :aria-invalid="error || undefined"
            class="peer w-full cursor-pointer appearance-none rounded-lg border bg-white text-neutral-800 shadow-sm outline-none transition-all duration-200 focus:ring-2 disabled:cursor-not-allowed disabled:bg-neutral-50 disabled:text-neutral-400"
            :class="[
                sizeClasses,
                error
                    ? 'border-rose-300 focus:border-rose-500 focus:ring-rose-200'
                    : 'border-neutral-200 hover:border-neutral-300 focus:border-purple-500 focus:ring-purple-200',
            ]"
        >
            <slot />
        </select>

        <!-- Chevron personalizado -->
        <span
            class="pointer-events-none absolute top-1/2 -translate-y-1/2 text-neutral-400 transition-all duration-200"
            :class="[
                chevronClasses,
                disabled ? 'text-neutral-300' : 'peer-hover:text-neutral-600 peer-focus:text-purple-600',
            ]"
        >
            <ChevronDown :size="16" class="h-full w-full" :stroke-width="2.5" />
        </span>
    </div>
</template>

<style scoped>
/* Estilo base para las opciones (algunos navegadores lo respetan) */
select option {
    padding: 0.5rem;
    color: #1f2937;
    background-color: white;
}
</style>