<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import {
    ChevronLeft, ChevronRight, ChevronsLeft, ChevronsRight,
} from '@lucide/vue';

const props = defineProps({
    paginator: { type: Object, required: true },
});

// Número de páginas visibles alrededor de la actual
const VENTANA = 2;

// Etiquetas de los botones de navegación (para detectar los extremos)
const LABELS_EXTREMO = ['&laquo; Previous', 'Previous', '&raquo; Next', 'Next'];

/**
 * Construye una lista de "slots" de paginación: números + elipsis.
 * Ej: 1 … 4 5 [6] 7 8 … 20
 */
const paginasVisibles = computed(() => {
    const total = props.paginator.last_page;
    const actual = props.paginator.current_page;

    if (total <= 7) {
        return Array.from({ length: total }, (_, i) => i + 1);
    }

    const paginas = new Set([1, total, actual]);
    for (let i = 1; i <= VENTANA; i++) {
        if (actual - i > 1) paginas.add(actual - i);
        if (actual + i < total) paginas.add(actual + i);
    }

    const ordenadas = [...paginas].sort((a, b) => a - b);
    const resultado = [];
    let anterior = 0;

    for (const p of ordenadas) {
        if (anterior && p - anterior > 1) resultado.push('...');
        resultado.push(p);
        anterior = p;
    }
    return resultado;
});

// URL directa a una página específica (usando el link existente del paginator)
function urlDePagina(pagina) {
    return props.paginator.links.find((l) => !LABELS_EXTREMO.includes(l.label) && Number(l.label) === pagina)?.url || null;
}

const linkPrev = computed(() => props.paginator.links[0]);
const linkNext = computed(() => props.paginator.links[props.paginator.links.length - 1]);
const linkPrimera = computed(() => urlDePagina(1));
const linkUltima = computed(() => urlDePagina(props.paginator.last_page));

const hayDatos = computed(() => props.paginator.total > 0);

// Info contextual
const info = computed(() => ({
    desde: props.paginator.from ?? 0,
    hasta: props.paginator.to ?? 0,
    total: props.paginator.total ?? 0,
    pagina: props.paginator.current_page ?? 1,
    totalPaginas: props.paginator.last_page ?? 1,
}));
</script>

<template>
    <div
        v-if="paginator.last_page > 1"
        class="flex flex-col gap-3 border-t border-neutral-100 bg-white px-4 py-3.5 sm:flex-row sm:items-center sm:justify-between sm:px-6"
    >
        <!-- Info -->
        <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-xs">
            <span class="inline-flex items-center gap-1.5 rounded-lg border border-purple-100 bg-purple-50/70 px-2.5 py-1 font-semibold text-purple-700 shadow-sm">
                <span class="h-1.5 w-1.5 rounded-full bg-purple-500"></span>
                Página {{ info.pagina }} de {{ info.totalPaginas }}
            </span>
            <span class="text-neutral-500">
                Mostrando
                <strong class="font-semibold text-neutral-700">{{ info.desde }}–{{ info.hasta }}</strong>
                de
                <strong class="font-semibold text-neutral-700">{{ info.total }}</strong>
            </span>
        </div>

        <!-- Paginación -->
        <nav
            class="flex items-center gap-1 overflow-x-auto pb-1 sm:pb-0"
            aria-label="Paginación"
        >
            <!-- Primera página -->
            <Link
                v-if="linkPrimera"
                :href="linkPrimera"
                preserve-scroll
                preserve-state
                class="group flex h-8 w-8 shrink-0 items-center justify-center rounded-lg border border-neutral-200 bg-white text-neutral-500 shadow-sm transition-all duration-200 hover:border-purple-300 hover:bg-purple-50 hover:text-purple-700 hover:shadow-md"
                title="Primera página"
                aria-label="Primera página"
                rel="first"
            >
                <ChevronsLeft :size="14" class="transition-transform duration-200" />
            </Link>
            <span
                v-else
                class="flex h-8 w-8 shrink-0 cursor-not-allowed items-center justify-center rounded-lg border border-neutral-100 bg-neutral-50 text-neutral-300"
                aria-disabled="true"
            >
                <ChevronsLeft :size="14" />
            </span>

            <!-- Anterior -->
            <Link
                v-if="linkPrev?.url"
                :href="linkPrev.url"
                preserve-scroll
                preserve-state
                class="group flex h-8 w-8 shrink-0 items-center justify-center rounded-lg border border-neutral-200 bg-white text-neutral-500 shadow-sm transition-all duration-200 hover:border-purple-300 hover:bg-purple-50 hover:text-purple-700 hover:shadow-md"
                title="Anterior"
                aria-label="Página anterior"
                rel="prev"
            >
                <ChevronLeft :size="15" class="transition-transform duration-200" />
            </Link>
            <span
                v-else
                class="flex h-8 w-8 shrink-0 cursor-not-allowed items-center justify-center rounded-lg border border-neutral-100 bg-neutral-50 text-neutral-300"
                aria-disabled="true"
            >
                <ChevronLeft :size="15" />
            </span>

            <!-- Separador -->
            <span class="mx-1 hidden h-6 w-px bg-neutral-200 sm:block"></span>

            <!-- Números de página -->
            <template v-for="(p, i) in paginasVisibles" :key="i">
                <!-- Elipsis -->
                <span
                    v-if="p === '...'"
                    class="flex h-8 w-8 shrink-0 items-center justify-center text-xs font-bold text-neutral-300"
                    aria-hidden="true"
                >
                    …
                </span>

                <!-- Página normal -->
                <Link
                    v-else
                    :href="urlDePagina(p)"
                    preserve-scroll
                    preserve-state
                    class="group flex h-8 min-w-8 shrink-0 items-center justify-center rounded-lg border px-2 text-xs font-bold tabular-nums shadow-sm transition-all duration-200 hover:shadow-md"
                    :class="p === info.pagina
                        ? 'border-transparent bg-purple-600 text-white shadow-sm hover:bg-purple-500'
                        : 'border-neutral-200 bg-white text-neutral-600 hover:border-purple-300 hover:bg-purple-50 hover:text-purple-700'"
                    :aria-current="p === info.pagina ? 'page' : undefined"
                    :aria-label="`Ir a la página ${p}`"
                >
                    {{ p }}
                </Link>
            </template>

            <!-- Separador -->
            <span class="mx-1 hidden h-6 w-px bg-neutral-200 sm:block"></span>

            <!-- Siguiente -->
            <Link
                v-if="linkNext?.url"
                :href="linkNext.url"
                preserve-scroll
                preserve-state
                class="group flex h-8 w-8 shrink-0 items-center justify-center rounded-lg border border-neutral-200 bg-white text-neutral-500 shadow-sm transition-all duration-200 hover:border-purple-300 hover:bg-purple-50 hover:text-purple-700 hover:shadow-md"
                title="Siguiente"
                aria-label="Página siguiente"
                rel="next"
            >
                <ChevronRight :size="15" class="transition-transform duration-200" />
            </Link>
            <span
                v-else
                class="flex h-8 w-8 shrink-0 cursor-not-allowed items-center justify-center rounded-lg border border-neutral-100 bg-neutral-50 text-neutral-300"
                aria-disabled="true"
            >
                <ChevronRight :size="15" />
            </span>

            <!-- Última página -->
            <Link
                v-if="linkUltima"
                :href="linkUltima"
                preserve-scroll
                preserve-state
                class="group flex h-8 w-8 shrink-0 items-center justify-center rounded-lg border border-neutral-200 bg-white text-neutral-500 shadow-sm transition-all duration-200 hover:border-purple-300 hover:bg-purple-50 hover:text-purple-700 hover:shadow-md"
                title="Última página"
                aria-label="Última página"
                rel="last"
            >
                <ChevronsRight :size="14" class="transition-transform duration-200" />
            </Link>
            <span
                v-else
                class="flex h-8 w-8 shrink-0 cursor-not-allowed items-center justify-center rounded-lg border border-neutral-100 bg-neutral-50 text-neutral-300"
                aria-disabled="true"
            >
                <ChevronsRight :size="14" />
            </span>
        </nav>
    </div>
</template>