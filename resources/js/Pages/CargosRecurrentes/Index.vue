<script setup>
import { computed, ref } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Modal from '@/Components/Modal.vue';
import ConfirmDialog from '@/Components/ConfirmDialog.vue';
import Badge from '@/Components/Badge.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import SelectInput from '@/Components/SelectInput.vue';
import Textarea from '@/Components/Textarea.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { formatCurrency, formatFecha, hoyIso } from '@/utils';
import { Plus, Pencil, Trash2, CheckCircle2, Repeat, Home, Dumbbell, Tv, Wifi, ShoppingBag } from '@lucide/vue';

const props = defineProps({
    cargos: Array,
    frecuencias: Object,
    cuentas: Array,
    categorias: Array,
});

const ICONOS_SUGERIDOS = { Renta: Home, Gimnasio: Dumbbell, Streaming: Tv, Internet: Wifi };

function iconoPara(nombre) {
    const clave = Object.keys(ICONOS_SUGERIDOS).find((k) => nombre?.toLowerCase().includes(k.toLowerCase()));
    return clave ? ICONOS_SUGERIDOS[clave] : Repeat;
}

function estadoFecha(cargo) {
    if (!cargo.activo) return { texto: 'Pausado', color: '#94a3b8' };
    if (cargo.vencido) return { texto: `Venció hace ${Math.abs(cargo.dias_para_vencer)} día(s)`, color: '#dc2626' };
    if (cargo.dias_para_vencer === 0) return { texto: 'Vence hoy', color: '#dc2626' };
    if (cargo.dias_para_vencer <= 3) return { texto: `En ${cargo.dias_para_vencer} día(s)`, color: '#d97706' };
    return { texto: `En ${cargo.dias_para_vencer} día(s)`, color: '#0ea5e9' };
}

const totalMensualEstimado = computed(() => {
    const factor = { semanal: 4.33, quincenal: 2, mensual: 1, bimestral: 0.5, trimestral: 1 / 3, semestral: 1 / 6, anual: 1 / 12 };
    return props.cargos.filter((c) => c.activo).reduce((acc, c) => acc + Number(c.monto) * (factor[c.frecuencia] || 1), 0);
});

// --- crear / editar ---
const showModal = ref(false);
const editando = ref(null);
const sinFin = ref(true);
const form = useForm({
    nombre: '',
    cuenta_id: '',
    categoria_id: '',
    monto: '',
    frecuencia: 'mensual',
    proxima_fecha: hoyIso(),
    fecha_fin: '',
    activo: true,
    notas: '',
});

function abrirCrear() {
    editando.value = null;
    form.nombre = '';
    form.cuenta_id = props.cuentas[0]?.id || '';
    form.categoria_id = '';
    form.monto = '';
    form.frecuencia = 'mensual';
    form.proxima_fecha = hoyIso();
    form.fecha_fin = '';
    form.activo = true;
    form.notas = '';
    sinFin.value = true;
    form.clearErrors();
    showModal.value = true;
}

function abrirEditar(cargo) {
    editando.value = cargo;
    form.nombre = cargo.nombre;
    form.cuenta_id = cargo.cuenta_id;
    form.categoria_id = cargo.categoria_id || '';
    form.monto = cargo.monto;
    form.frecuencia = cargo.frecuencia;
    form.proxima_fecha = cargo.proxima_fecha.slice(0, 10);
    form.fecha_fin = cargo.fecha_fin ? cargo.fecha_fin.slice(0, 10) : '';
    form.activo = cargo.activo;
    form.notas = cargo.notas || '';
    sinFin.value = !cargo.fecha_fin;
    form.clearErrors();
    showModal.value = true;
}

function guardar() {
    if (sinFin.value) form.fecha_fin = '';
    if (editando.value) {
        form.put(route('recurrentes.update', editando.value.id), { onSuccess: () => (showModal.value = false) });
    } else {
        form.post(route('recurrentes.store'), { onSuccess: () => (showModal.value = false) });
    }
}

const confirmando = ref(null);
function eliminar() {
    router.delete(route('recurrentes.destroy', confirmando.value.id), { onFinish: () => (confirmando.value = null) });
}

function pagarAhora(cargo) {
    router.post(route('recurrentes.pagar', cargo.id), { fecha: hoyIso() }, { preserveScroll: true });
}
</script>

<template>
    <Head title="Cargos recurrentes" />

    <AuthenticatedLayout title="Cargos recurrentes" subtitle="Renta, streaming, gimnasio y todo lo que pagas seguido">
        <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
            <div class="text-sm text-ink-500">
                Estimado mensual: <strong class="text-ink-900">{{ formatCurrency(totalMensualEstimado) }}</strong>
            </div>
            <PrimaryButton @click="abrirCrear"><Plus :size="16" /> Nuevo cargo recurrente</PrimaryButton>
        </div>

        <div v-if="cargos.length === 0" class="rounded-2xl border border-dashed border-ink-300 bg-white py-16 text-center text-ink-400">
            Aún no tienes cargos recurrentes. Agrega tu renta, streaming, gimnasio, etc.
        </div>

        <div v-else class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
            <div
                v-for="(cargo, i) in cargos"
                :key="cargo.id"
                class="group animate-fade-in rounded-2xl border border-ink-100 bg-white p-5 shadow-card transition-all duration-200 hover:-translate-y-0.5 hover:shadow-glow"
                :class="{ 'opacity-60': !cargo.activo }"
                :style="{ animationDelay: `${i * 40}ms` }"
            >
                <div class="flex items-start justify-between gap-2">
                    <div class="flex items-center gap-2.5">
                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-brand-50 text-brand-600 transition-transform duration-200 group-hover:scale-110">
                            <component :is="iconoPara(cargo.nombre)" :size="17" stroke-width="2.2" />
                        </div>
                        <div>
                            <div class="font-bold text-ink-900">{{ cargo.nombre }}</div>
                            <div class="text-xs text-ink-400">{{ cargo.cuenta.nombre }}</div>
                        </div>
                    </div>
                    <Badge v-if="cargo.categoria" :color="cargo.categoria.color">{{ cargo.categoria.nombre }}</Badge>
                </div>

                <div class="mt-3 flex items-end justify-between">
                    <div class="text-2xl font-extrabold tracking-tight text-ink-900">{{ formatCurrency(cargo.monto) }}</div>
                    <span class="text-xs font-semibold text-ink-400">{{ frecuencias[cargo.frecuencia] }}</span>
                </div>

                <div class="mt-2 flex items-center justify-between text-xs">
                    <span class="text-ink-400">Próximo: {{ formatFecha(cargo.proxima_fecha) }}</span>
                    <span class="font-bold" :style="{ color: estadoFecha(cargo).color }">{{ estadoFecha(cargo).texto }}</span>
                </div>

                <div class="mt-4 flex gap-2">
                    <button
                        class="inline-flex items-center gap-1.5 rounded-lg bg-brand-600 px-3 py-1.5 text-xs font-semibold text-white transition hover:-translate-y-px hover:bg-brand-700 hover:shadow-glow disabled:pointer-events-none disabled:opacity-40"
                        :disabled="!cargo.activo"
                        @click="pagarAhora(cargo)"
                    >
                        <CheckCircle2 :size="13" /> Pagar ahora
                    </button>
                    <button class="inline-flex items-center gap-1.5 rounded-lg border border-ink-200 px-3 py-1.5 text-xs font-semibold text-ink-800 transition hover:border-brand-200 hover:bg-brand-50" @click="abrirEditar(cargo)">
                        <Pencil :size="13" />
                    </button>
                    <button class="inline-flex items-center gap-1.5 rounded-lg border border-rose-200 px-3 py-1.5 text-xs font-semibold text-rose-600 transition hover:bg-rose-50" @click="confirmando = cargo">
                        <Trash2 :size="13" />
                    </button>
                </div>
            </div>
        </div>

        <Modal :show="showModal" max-width="md" @close="showModal = false">
            <form class="p-6" @submit.prevent="guardar">
                <h2 class="text-base font-bold text-ink-900">{{ editando ? 'Editar cargo recurrente' : 'Nuevo cargo recurrente' }}</h2>

                <div class="mt-4 space-y-4">
                    <div>
                        <InputLabel value="Nombre" />
                        <TextInput v-model="form.nombre" class="mt-1" placeholder="Ej. Renta, Netflix, Gimnasio..." />
                        <InputError :message="form.errors.nombre" />
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <InputLabel value="Cuenta con la que pagas" />
                            <SelectInput v-model="form.cuenta_id" class="mt-1">
                                <option v-for="c in cuentas" :key="c.id" :value="c.id">{{ c.nombre }}</option>
                            </SelectInput>
                            <InputError :message="form.errors.cuenta_id" />
                        </div>
                        <div>
                            <InputLabel value="Categoría (opcional)" />
                            <SelectInput v-model="form.categoria_id" class="mt-1">
                                <option value="">(Sin categoría)</option>
                                <option v-for="c in categorias" :key="c.id" :value="c.id">{{ c.nombre }}</option>
                            </SelectInput>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <InputLabel value="Monto" />
                            <TextInput v-model="form.monto" type="number" step="0.01" class="mt-1" />
                            <InputError :message="form.errors.monto" />
                        </div>
                        <div>
                            <InputLabel value="Frecuencia" />
                            <SelectInput v-model="form.frecuencia" class="mt-1">
                                <option v-for="(label, value) in frecuencias" :key="value" :value="value">{{ label }}</option>
                            </SelectInput>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <InputLabel value="Próximo cobro" />
                            <TextInput v-model="form.proxima_fecha" type="date" class="mt-1" />
                            <InputError :message="form.errors.proxima_fecha" />
                        </div>
                        <div>
                            <InputLabel value="Fecha fin" />
                            <TextInput v-model="form.fecha_fin" type="date" class="mt-1" :disabled="sinFin" />
                            <label class="mt-1 flex items-center gap-1.5 text-xs text-ink-500">
                                <input type="checkbox" v-model="sinFin" class="rounded border-ink-300 text-brand-600 focus:ring-brand-500" />
                                Indefinido
                            </label>
                        </div>
                    </div>

                    <label v-if="editando" class="flex items-center gap-2 text-sm text-ink-700">
                        <input type="checkbox" v-model="form.activo" class="rounded border-ink-300 text-brand-600 focus:ring-brand-500" />
                        Activo (se muestra en la lista y se puede pagar)
                    </label>

                    <div>
                        <InputLabel value="Notas (opcional)" />
                        <Textarea v-model="form.notas" class="mt-1" :rows="2" />
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <SecondaryButton type="button" @click="showModal = false">Cancelar</SecondaryButton>
                    <PrimaryButton :disabled="form.processing">Guardar</PrimaryButton>
                </div>
            </form>
        </Modal>

        <ConfirmDialog
            :show="!!confirmando"
            title="Eliminar cargo recurrente"
            :message="confirmando ? `¿Eliminar '${confirmando.nombre}'? No se borrarán los movimientos ya registrados.` : ''"
            @confirm="eliminar"
            @cancel="confirmando = null"
        />
    </AuthenticatedLayout>
</template>
