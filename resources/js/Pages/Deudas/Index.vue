<script setup>
import { ref } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Modal from '@/Components/Modal.vue';
import ConfirmDialog from '@/Components/ConfirmDialog.vue';
import ProgressBar from '@/Components/ProgressBar.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import Textarea from '@/Components/Textarea.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { formatCurrency, formatFecha, hoyIso } from '@/utils';

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
    tasa_interes: '',
    fecha_inicio: hoyIso(),
    fecha_limite: '',
    notas: '',
});

function abrirCrear() {
    editando.value = null;
    form.nombre = '';
    form.acreedor = '';
    form.monto_total = '';
    form.tasa_interes = '';
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
    form.tasa_interes = deuda.tasa_interes || '';
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

const confirmando = ref(null);
function eliminar() {
    router.delete(route('deudas.destroy', confirmando.value.id), { onFinish: () => (confirmando.value = null) });
}
</script>

<template>
    <Head title="Deudas" />

    <AuthenticatedLayout title="Deudas" subtitle="Lo que debes y cuánto llevas pagado">
        <div class="mb-5 flex justify-end">
            <PrimaryButton @click="abrirCrear">+ Nueva deuda</PrimaryButton>
        </div>

        <div v-if="deudas.length === 0" class="rounded-2xl border border-dashed border-gray-300 bg-white py-16 text-center text-gray-400">
            No tienes deudas registradas. ¡Que se mantenga así, o agrega una para llevar el control!
        </div>

        <div v-else class="space-y-4">
            <div v-for="deuda in deudas" :key="deuda.id" class="rounded-2xl border border-gray-200/80 bg-white p-5 shadow-sm shadow-gray-200/50">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <div class="font-bold text-ink-900">{{ deuda.nombre }}</div>
                        <div v-if="deuda.acreedor" class="text-xs text-gray-400">{{ deuda.acreedor }}</div>
                    </div>
                    <span class="text-xs font-bold" :class="deuda.estado === 'pagada' ? 'text-brand-600' : 'text-rose-600'">
                        {{ deuda.estado === 'pagada' ? 'Pagada ✅' : 'Activa' }}
                    </span>
                </div>

                <ProgressBar :value="deuda.progreso" class="mt-3" :color="deuda.estado === 'pagada' ? '#16a34a' : '#0ea5e9'" />

                <div class="mt-2 flex flex-wrap justify-between gap-2 text-xs text-gray-500">
                    <span>Pagado {{ formatCurrency(deuda.pagado) }} de {{ formatCurrency(deuda.monto_total) }} · Restan {{ formatCurrency(deuda.restante) }}</span>
                    <span v-if="deuda.fecha_limite">Fecha límite: {{ formatFecha(deuda.fecha_limite) }}</span>
                </div>

                <div class="mt-4 flex gap-2">
                    <Link :href="route('deudas.show', deuda.id)" class="rounded-lg bg-brand-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-brand-700">
                        Ver / abonar
                    </Link>
                    <button class="rounded-lg border border-gray-200 px-3 py-1.5 text-xs font-semibold text-ink-800 hover:bg-gray-50" @click="abrirEditar(deuda)">
                        Editar
                    </button>
                    <button class="rounded-lg border border-rose-200 px-3 py-1.5 text-xs font-semibold text-rose-600 hover:bg-rose-50" @click="confirmando = deuda">
                        Eliminar
                    </button>
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
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <InputLabel value="Monto total" />
                            <TextInput v-model="form.monto_total" type="number" step="0.01" class="mt-1" />
                            <InputError :message="form.errors.monto_total" />
                        </div>
                        <div>
                            <InputLabel value="Tasa de interés anual % (opcional)" />
                            <TextInput v-model="form.tasa_interes" type="number" step="0.01" class="mt-1" />
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <InputLabel value="Fecha de inicio" />
                            <TextInput v-model="form.fecha_inicio" type="date" class="mt-1" />
                        </div>
                        <div>
                            <InputLabel value="Fecha límite" />
                            <TextInput v-model="form.fecha_limite" type="date" class="mt-1" :disabled="sinLimite" />
                            <label class="mt-1 flex items-center gap-1.5 text-xs text-gray-500">
                                <input type="checkbox" v-model="sinLimite" class="rounded border-gray-300 text-brand-600 focus:ring-brand-500" />
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

        <ConfirmDialog
            :show="!!confirmando"
            title="Eliminar deuda"
            :message="confirmando ? `¿Eliminar '${confirmando.nombre}' y todo su historial de abonos?` : ''"
            @confirm="eliminar"
            @cancel="confirmando = null"
        />
    </AuthenticatedLayout>
</template>
