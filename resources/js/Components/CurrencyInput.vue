<script setup>
import { ref, watch } from 'vue';

const model = defineModel({ type: [Number, String], default: '' });
defineOptions({ inheritAttrs: false });
defineProps({ placeholder: { type: String, default: '0.00' } });

function formatearParaMostrar(valor) {
    if (valor === '' || valor === null || valor === undefined) return '';
    const numero = Number(valor);
    if (Number.isNaN(numero)) return '';
    return numero.toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

const texto = ref(formatearParaMostrar(model.value));
let enfocado = false;

watch(model, (nuevo) => {
    if (!enfocado) texto.value = formatearParaMostrar(nuevo);
});

function alEnfocar(e) {
    enfocado = true;
    // Al entrar, muestra el número "crudo" para que sea fácil de editar.
    texto.value = model.value === '' || model.value === null ? '' : String(model.value);
    requestAnimationFrame(() => e.target.select());
}

function alEscribir(e) {
    const limpio = e.target.value.replace(/[^0-9.]/g, '');
    texto.value = limpio;
    model.value = limpio === '' ? '' : limpio;
}

function alSalir() {
    enfocado = false;
    const numero = Number(model.value);
    model.value = Number.isNaN(numero) ? '' : numero;
    texto.value = formatearParaMostrar(model.value);
}
</script>

<template>
    <div class="relative">
        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-sm font-medium text-neutral-400">$</span>
        <input
            v-bind="$attrs"
            type="text"
            inputmode="decimal"
            :value="texto"
            :placeholder="placeholder"
            class="h-10 w-full rounded-lg border-neutral-200 pl-7 pr-14 text-sm tabular-nums text-neutral-800 shadow-sm transition placeholder:text-neutral-400 hover:border-neutral-300 focus:border-brand-500 focus:ring-2 focus:ring-brand-200"
            @focus="alEnfocar"
            @input="alEscribir"
            @blur="alSalir"
        />
        <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3 text-[11px] font-medium text-neutral-400">MXN</span>
    </div>
</template>
