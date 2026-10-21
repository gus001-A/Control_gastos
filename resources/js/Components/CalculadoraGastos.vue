<script setup>
import { computed, nextTick, ref } from 'vue';
import { formatCurrency } from '@/utils';
import { Calculator, Plus, Trash2, Check, RotateCcw } from '@lucide/vue';

// Junta varias compras (concepto × cantidad × precio) y regresa el total
// para registrarlas como un solo movimiento.
const emit = defineEmits(['usar']);

const conceptos = ref([]);
const nuevo = ref({ concepto: '', cantidad: 1, precio: '' });
const conceptoInput = ref(null);

const subtotal = (c) => Number(c.cantidad || 0) * Number(c.precio || 0);

const total = computed(() =>
    Math.round(conceptos.value.reduce((acc, c) => acc + subtotal(c), 0) * 100) / 100
);

const piezas = computed(() =>
    conceptos.value.reduce((acc, c) => acc + Number(c.cantidad || 0), 0)
);

const puedeAgregar = computed(() => Number(nuevo.value.precio) > 0 && Number(nuevo.value.cantidad) > 0);

function agregar() {
    if (!puedeAgregar.value) return;
    conceptos.value.push({
        id: Date.now() + Math.random(),
        concepto: nuevo.value.concepto.trim(),
        cantidad: Number(nuevo.value.cantidad),
        precio: Number(nuevo.value.precio),
    });
    nuevo.value = { concepto: '', cantidad: 1, precio: '' };
    nextTick(() => conceptoInput.value?.focus());
}

function quitar(id) {
    conceptos.value = conceptos.value.filter((c) => c.id !== id);
}

function limpiar() {
    conceptos.value = [];
}

function usarTotal() {
    if (total.value <= 0) return;
    const descripcion = conceptos.value
        .map((c) => {
            const nombre = c.concepto || 'Artículo';
            return c.cantidad > 1 ? `${nombre} x${c.cantidad}` : nombre;
        })
        .join(', ')
        .slice(0, 255);
    emit('usar', { total: total.value, descripcion });
}
</script>

<template>
    <div class="rounded-xl border border-purple-200 bg-white p-4 shadow-sm">
        <div class="mb-3 flex items-center justify-between gap-2">
            <div class="flex items-center gap-2">
                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-purple-600 text-white shadow-sm">
                    <Calculator :size="15" />
                </span>
                <div class="leading-tight">
                    <p class="text-sm font-bold text-neutral-900">Calculadora de compras</p>
                    <p class="text-[11px] text-neutral-500">Suma varias cosas y guárdalas como un solo movimiento</p>
                </div>
            </div>
            <button
                v-if="conceptos.length"
                type="button"
                class="inline-flex items-center gap-1 rounded-lg px-2 py-1 text-[11px] font-semibold text-neutral-500 transition hover:bg-neutral-100 hover:text-neutral-800"
                @click="limpiar"
            >
                <RotateCcw :size="12" /> Limpiar
            </button>
        </div>

        <!-- Captura rápida: Enter agrega el concepto -->
        <div class="grid grid-cols-[1fr_4.5rem_6.5rem_auto] gap-2" @keydown.enter.prevent="agregar">
            <input
                ref="conceptoInput"
                v-model="nuevo.concepto"
                type="text"
                placeholder="Concepto (ej. Camisa)"
                class="min-w-0 rounded-lg border-neutral-200 text-sm shadow-sm focus:border-purple-500 focus:ring-purple-500"
            />
            <input
                v-model="nuevo.cantidad"
                type="number"
                min="1"
                step="1"
                title="Cantidad"
                class="rounded-lg border-neutral-200 text-center text-sm shadow-sm focus:border-purple-500 focus:ring-purple-500"
            />
            <div class="relative">
                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-2.5 text-xs font-semibold text-neutral-400">$</span>
                <input
                    v-model="nuevo.precio"
                    type="number"
                    min="0"
                    step="0.01"
                    placeholder="Precio"
                    class="w-full rounded-lg border-neutral-200 pl-6 text-sm tabular-nums shadow-sm focus:border-purple-500 focus:ring-purple-500"
                />
            </div>
            <button
                type="button"
                :disabled="!puedeAgregar"
                class="flex h-[38px] w-[38px] items-center justify-center rounded-lg bg-purple-600 text-white shadow-sm transition-all duration-200 disabled:cursor-not-allowed disabled:opacity-40 disabled:hover:scale-100"
                title="Agregar (Enter)"
                @click="agregar"
            >
                <Plus :size="16" />
            </button>
        </div>

        <!-- Lista -->
        <TransitionGroup
            tag="ul"
            class="mt-3 max-h-48 space-y-1.5 overflow-y-auto"
            enter-active-class="transition ease-out duration-200"
            enter-from-class="opacity-0 -translate-y-1"
            leave-active-class="transition ease-in duration-150"
            leave-to-class="opacity-0 translate-x-2"
        >
            <li
                v-for="c in conceptos"
                :key="c.id"
                class="group flex items-center gap-3 rounded-lg border border-neutral-100 bg-white px-3 py-2 text-sm shadow-sm"
            >
                <span class="min-w-0 flex-1 truncate font-medium text-neutral-800">{{ c.concepto || 'Artículo' }}</span>
                <span class="whitespace-nowrap text-xs tabular-nums text-neutral-500">
                    {{ c.cantidad }} × {{ formatCurrency(c.precio) }}
                </span>
                <span class="w-24 text-right font-bold tabular-nums text-neutral-900">{{ formatCurrency(subtotal(c)) }}</span>
                <button
                    type="button"
                    class="rounded-md p-1 text-neutral-300 transition hover:bg-rose-50 hover:text-rose-600"
                    title="Quitar"
                    @click="quitar(c.id)"
                >
                    <Trash2 :size="14" />
                </button>
            </li>
        </TransitionGroup>

        <p v-if="!conceptos.length" class="mt-3 rounded-lg border border-dashed border-neutral-200 py-4 text-center text-xs text-neutral-400">
            Agrega lo que compraste y te doy el total.
        </p>

        <!-- Total -->
        <div class="mt-3 flex flex-col gap-3 border-t border-purple-100 pt-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-[10px] font-semibold uppercase tracking-wider text-purple-700">
                    Total · {{ piezas }} pieza(s)
                </p>
                <p class="text-xl font-bold tabular-nums text-neutral-900">{{ formatCurrency(total) }}</p>
            </div>
            <button
                type="button"
                :disabled="total <= 0"
                class="inline-flex items-center justify-center gap-1.5 rounded-lg bg-purple-700 px-4 py-2 text-sm font-semibold text-white shadow-sm transition-all duration-200 hover:bg-purple-600 disabled:cursor-not-allowed disabled:opacity-40 disabled:hover:scale-100"
                @click="usarTotal"
            >
                <Check :size="14" /> Usar este total
            </button>
        </div>
    </div>
</template>
