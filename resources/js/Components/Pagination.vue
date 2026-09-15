<script setup>
import { Link } from '@inertiajs/vue3';
import { ChevronLeft, ChevronRight } from '@lucide/vue';

defineProps({
    paginator: { type: Object, required: true },
});
</script>

<template>
    <div v-if="paginator.last_page > 1" class="flex flex-wrap items-center justify-between gap-3 border-t border-ink-100 px-5 py-3">
        <span class="text-xs text-ink-400">Mostrando <strong class="font-semibold text-ink-600">{{ paginator.from }}–{{ paginator.to }}</strong> de {{ paginator.total }}</span>
        <div class="flex items-center gap-1">
            <template v-for="(link, i) in paginator.links" :key="i">
                <span v-if="!link.url" class="flex h-8 w-8 items-center justify-center rounded-lg text-ink-200">
                    <ChevronLeft v-if="i === 0" :size="15" />
                    <ChevronRight v-else-if="i === paginator.links.length - 1" :size="15" />
                    <span v-else v-html="link.label" />
                </span>
                <Link
                    v-else
                    :href="link.url"
                    preserve-scroll
                    preserve-state
                    class="flex h-8 min-w-8 items-center justify-center rounded-lg px-2 text-xs font-semibold transition"
                    :class="link.active ? 'bg-brand-600 text-white shadow-glow' : 'text-ink-500 hover:bg-brand-50 hover:text-brand-700'"
                >
                    <ChevronLeft v-if="i === 0" :size="15" />
                    <ChevronRight v-else-if="i === paginator.links.length - 1" :size="15" />
                    <span v-else v-html="link.label" />
                </Link>
            </template>
        </div>
    </div>
</template>
