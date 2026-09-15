<script setup>
import { ref } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Modal from '@/Components/Modal.vue';
import ProgressBar from '@/Components/ProgressBar.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import CurrencyInput from '@/Components/CurrencyInput.vue';
import Textarea from '@/Components/Textarea.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { formatCurrency, formatFecha, hoyIso } from '@/utils';
import { confirmar } from '@/lib/alertas';
import { Plus, Pencil, Trash2, Eye, CreditCard, CalendarClock, CheckCircle2 } from '@lucide/vue';

const props = defineProps({
    deudas: Array,
});

const showModal = ref(false);
const editando = ref(null);
const sinLimite = ref(true);
const form = useForm({
    nombre: '',
    acreedor: '',
    monto_total: '',
    fecha_inicio: hoyIso(),
    fecha_limite: '',
    notas: '',
});

function abrirCrear() {
    editando.value = null;
    form.nombre = '';
    form.acreedor = '';
    form.monto_total = '';
    form.fecha_inicio = hoyIso();
    form.fecha_limite = '';
    form.notas = '';
    sinLimite.value = true;
    form.clearErrors();
    showModal.value = true;
}

function abrirEditar(deuda) {
    editando.value = deuda;
    form.nombre = deuda.nombre;
    form.acreedor = deuda.acreedor || '';
    form.monto_total = deuda.monto_total;
    form.fecha_inicio = deuda.fecha_inicio ? deuda.fecha_inicio.slice(0, 10) : '';
    form.fecha_limite = deuda.fecha_limite ? deuda.fecha_limite.slice(0, 10) : '';
    sinLimite.value = !deuda.fecha_limite;
    form.notas = deuda.notas || '';
    form.clearErrors();
    showModal.value = true;
}

function guardar() {
    if (sinLimite.value) form.fecha_limite = '';
    if (editando.value) {
        form.put(route('deudas.update', editando.value.id), { onSuccess: () => (showModal.value = false) });
    } else {
        form.post(route('deudas.store'), { onSuccess: () => (showModal.value = false) });
    }
}

function eliminar(deuda) {
    confirmar({
        title: 'Eliminar deuda',
        content: `¿Eliminar "${deuda.nombre}" y todo su historial de abonos?`,
        onOk: () => router.delete(route('deudas.destroy', deuda.id)),
    });
}
</script>

<template>
    <Head title="Deudas" />

    <AuthenticatedLayout title="Deudas" subtitle="Lo que debes y cuánto llevas pagado">
        <div class="mb-5 flex justify-end">
            <PrimaryButton @click="abrirCrear"><Plus :size="16" /> Nueva deuda</PrimaryButton>
        </div>

        <div v-if="deudas.length === 0" class="rounded-2xl border border-dashed border-ink-300 bg-white py-16 text-center text-ink-400">
            No tienes deudas registradas. ¡Que se mantenga así, o agrega una para llevar el control!
        </div>

        <div v-else class="grid grid-cols-1 gap-5 lg:grid-cols-2">
            <div
                v-for="(deuda, i) in deudas"
                :key="deuda.id"
                class="group relative animate-fade-in overflow-hidden rounded-2xl border border-ink-100 bg-white shadow-card transition-all duration-200 hover:-translate-y-0.5 hover:shadow-glow"
                :style="{ animationDelay: `${i * 50}ms` }"
            >
                <div class="h-1.5 w-full" :class="deuda.estado === 'pagada' ? 'bg-gradient-to-r from-green-400 to-green-600' : 'bg-gradient-to-r from-rose-400 to-rose-600'" />

                <div class="p-5">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <div
                                class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl transition-transform duration-200 group-hover:scale-110"
                                :class="deuda.estado === 'pagada' ? 'bg-green-50 text-green-600' : 'bg-rose-50 text-rose-600'"
                            >
                                <component :is="deuda.estado === 'pagada' ? CheckCircle2 : CreditCard" :size="20" />
                            </div>
                            <div>
                                <div class="font-bold leading-tight text-ink-900">{{ deuda.nombre }}</div>
                                <div v-if="deuda.acreedor" class="text-xs text-ink-400">{{ deuda.acreedor }}</div>
                            </div>
                        </div>
                        <span
                            class="shrink-0 rounded-full px-2.5 py-1 text-xs font-bold"
                            :class="deuda.estado === 'pagada' ? 'bg-green-50 text-green-700' : 'bg-rose-50 text-rose-700'"
                        >
                            {{ deuda.estado === 'pagada' ? 'Pagada' : 'Activa' }}
                        </span>
                    </div>

                    <div class="mt-5 flex items-baseline justify-between">
                        <span class="text-xs font-semibold uppercase tracking-wide text-ink-400">Restante</span>
                        <span class="text-2xl font-extrabold tracking-tight" :class="deuda.estado === 'pagada' ? 'text-green-600' : 'text-rose-600'">
                            {{ formatCurrency(deuda.restante) }}
                        </span>
                    </div>

                    <div class="mt-2 flex items-center gap-3">
                        <ProgressBar :value="deuda.progreso" class="flex-1" :color="deuda.estado === 'pagada' ? '#16a34a' : '#9333ea'" />
                        <span class="w-10 shrink-0 text-right text-xs font-bold text-ink-500">{{ deuda.progreso }}%</span>
                    </div>
                    <div class="mt-1.5 flex items-center justify-between text-xs text-ink-400">
                        <span>Pagado {{ formatCurrency(deuda.pagado) }} de {{ formatCurrency(deuda.monto_total) }}</span>
                        <span v-if="deuda.fecha_limite" class="inline-flex items-center gap-1">
                            <CalendarClock :size="12" /> {{ formatFecha(deuda.fecha_limite) }}
                        </span>
                    </div>

                    <div class="mt-4 flex gap-2 border-t border-ink-50 pt-4">
                        <Link :href="route('deudas.show', deuda.id)" class="inline-flex items-center gap-1.5 rounded-lg bg-brand-600 px-3 py-1.5 text-xs font-semibold text-white transition hover:-translate-y-px hover:bg-brand-700 hover:shadow-glow">
                            <Eye :size="13" /> Ver / abonar
                        </Link>
                        <button class="inline-flex items-center gap-1.5 rounded-lg border border-ink-200 px-3 py-1.5 text-xs font-semibold text-ink-800 transition hover:border-brand-200 hover:bg-brand-50" @click="abrirEditar(deuda)">
                            <Pencil :size="13" /> Editar
                        </button>
                        <button class="ml-auto inline-flex items-center gap-1.5 rounded-lg border border-rose-200 px-3 py-1.5 text-xs font-semibold text-rose-600 transition hover:bg-rose-50" @click="eliminar(deuda)">
                            <Trash2 :size="13" />
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <Modal :show="showModal" max-width="md" @close="showModal = false">
            <form class="p-6" @submit.prevent="guardar">
                <h2 class="text-base font-bold text-ink-900">{{ editando ? 'Editar deuda' : 'Nueva deuda' }}</h2>

                <div class="mt-4 space-y-4">
                    <div>
                        <InputLabel value="Nombre" />
                        <TextInput v-model="form.nombre" class="mt-1" placeholder="Ej. Tarjeta Santander, Préstamo personal..." />
                        <InputError :message="form.errors.nombre" />
                    </div>
                    <div>
                        <InputLabel value="Acreedor (opcional)" />
                        <TextInput v-model="form.acreedor" class="mt-1" placeholder="¿A quién le debes?" />
                    </div>
                    <div>
                        <InputLabel value="Monto total" />
                        <CurrencyInput v-model="form.monto_total" class="mt-1" />
                        <InputError :message="form.errors.monto_total" />
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <InputLabel value="Fecha de inicio" />
                            <TextInput v-model="form.fecha_inicio" type="date" class="mt-1" />
                        </div>
                        <div>
                            <InputLabel value="Fecha límite" />
                            <TextInput v-model="form.fecha_limite" type="date" class="mt-1" :disabled="sinLimite" />
                            <label class="mt-1 flex items-center gap-1.5 text-xs text-ink-500">
                                <input type="checkbox" v-model="sinLimite" class="rounded border-ink-300 text-brand-600 focus:ring-brand-500" />
                                Sin fecha límite
                            </label>
                        </div>
                    </div>
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
    </AuthenticatedLayout>
</template>
