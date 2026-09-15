<script setup>
import { computed, ref, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';

const page = usePage();
const visible = ref(false);
let timer = null;

const mensaje = computed(() => page.props.flash?.success || page.props.flash?.error || '');
const esError = computed(() => Boolean(page.props.flash?.error));

watch(
    () => [page.props.flash?.success, page.props.flash?.error],
    () => {
        if (mensaje.value) {
            visible.value = true;
            clearTimeout(timer);
            timer = setTimeout(() => (visible.value = false), 3500);
        }
    },
    { immediate: true },
);
</script>

<template>
    <Transition
        enter-active-class="transition ease-out duration-200"
        enter-from-class="opacity-0 translate-y-2"
        enter-to-class="opacity-100 translate-y-0"
        leave-active-class="transition ease-in duration-150"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
    >
        <div
            v-if="visible && mensaje"
            class="fixed bottom-5 right-5 z-[60] flex items-center gap-2 rounded-xl border px-4 py-3 text-sm font-medium shadow-lg"
            :class="esError ? 'border-rose-200 bg-rose-50 text-rose-700' : 'border-brand-200 bg-white text-ink-800'"
        >
            <span v-if="!esError" class="text-brand-600">✓</span>
            <span v-else>⚠️</span>
            {{ mensaje }}
        </div>
    </Transition>
</template>
