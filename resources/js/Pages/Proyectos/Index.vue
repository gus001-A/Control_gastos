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
import SelectInput from '@/Components/SelectInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { formatCurrency, formatFecha, hoyIso } from '@/utils';
import { confirmar } from '@/lib/alertas';
import { Plus, Pencil, Trash2, Eye } from '@lucide/vue';

const props = defineProps({
    proyectos: Array,
    estados: Object,
});

const ESTADO_COLOR = { activo: '#0ea5e9', terminado: '#16a34a', cancelado: '#94a3b8' };

const showModal = ref(false);
const editando = ref(null);
const sinFecha = ref(true);
const form = useForm({
    nombre: '',
    cliente: '',
    descripcion: '',
    monto_cobrado: '',
    fecha_inicio: hoyIso(),
    fecha_entrega: '',
    estado: 'activo',
    notas: '',
});

function abrirCrear() {
    editando.value = null;
    form.nombre = '';
    form.cliente = '';
    form.descripcion = '';
    form.monto_cobrado = '';
    form.fecha_inicio = hoyIso();
    form.fecha_entrega = '';
    form.estado = 'activo';
    form.notas = '';
    sinFecha.value = true;
    form.clearErrors();
    showModal.value = true;
}

function abrirEditar(proyecto) {
    editando.value = proyecto;
    form.nombre = proyecto.nombre;
    form.cliente = proyecto.cliente || '';
    form.descripcion = proyecto.descripcion || '';
    form.monto_cobrado = proyecto.monto_cobrado;
    form.fecha_inicio = proyecto.fecha_inicio ? proyecto.fecha_inicio.slice(0, 10) : '';
    form.fecha_entrega = proyecto.fecha_entrega ? proyecto.fecha_entrega.slice(0, 10) : '';
    sinFecha.value = !proyecto.fecha_entrega;
    form.estado = proyecto.estado;
    form.notas = proyecto.notas || '';
    form.clearErrors();
    showModal.value = true;
}

function guardar() {
    if (sinFecha.value) form.fecha_entrega = '';
    if (editando.value) {
        form.put(route('proyectos.update', editando.value.id), { onSuccess: () => (showModal.value = false) });
    } else {
        form.post(route('proyectos.store'), { onSuccess: () => (showModal.value = false) });
    }
}

function eliminar(proyecto) {
    confirmar({
        title: 'Eliminar proyecto',
        content: `¿Eliminar "${proyecto.nombre}" y todo su historial de pagos?`,
        onOk: () => router.delete(route('proyectos.destroy', proyecto.id)),
    });
}
</script>

<template>
    <Head title="Proyectos" />

    <AuthenticatedLayout title="Proyectos" subtitle="Tus trabajos freelance: qué cobras y qué te han pagado">
        <div class="mb-5 flex justify-end">
            <PrimaryButton @click="abrirCrear"><Plus :size="16" /> Nuevo proyecto</PrimaryButton>
        </div>

        <div v-if="proyectos.length === 0" class="rounded-2xl border border-dashed border-ink-300 bg-white py-16 text-center text-ink-400">
            Aún no tienes proyectos freelance registrados. Agrega el primero con el botón de arriba.
        </div>

        <div v-else class="space-y-4">
            <div v-for="proyecto in proyectos" :key="proyecto.id" class="animate-fade-in rounded-2xl border border-ink-100 bg-white p-5 shadow-card transition-shadow duration-200 hover:shadow-glow">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <div class="font-bold text-ink-900">{{ proyecto.nombre }}</div>
                        <div v-if="proyecto.cliente" class="text-xs text-ink-400">{{ proyecto.cliente }}</div>
                    </div>
                    <span class="text-xs font-bold" :style="{ color: ESTADO_COLOR[proyecto.estado] }">{{ estados[proyecto.estado] }}</span>
                </div>

                <ProgressBar :value="proyecto.progreso" class="mt-3" :color="ESTADO_COLOR[proyecto.estado]" />

                <div class="mt-2 flex flex-wrap justify-between gap-2 text-xs text-ink-500">
                    <span>Pagado {{ formatCurrency(proyecto.pagado) }} de {{ formatCurrency(proyecto.monto_cobrado) }} · Pendiente {{ formatCurrency(proyecto.pendiente) }}</span>
                    <span v-if="proyecto.fecha_entrega">Entrega: {{ formatFecha(proyecto.fecha_entrega) }}</span>
                </div>

                <div class="mt-4 flex gap-2">
                    <Link :href="route('proyectos.show', proyecto.id)" class="inline-flex items-center gap-1.5 rounded-lg bg-brand-600 px-3 py-1.5 text-xs font-semibold text-white transition hover:-translate-y-px hover:bg-brand-700 hover:shadow-glow">
                        <Eye :size="13" /> Ver / registrar pago
                    </Link>
                    <button class="inline-flex items-center gap-1.5 rounded-lg border border-ink-200 px-3 py-1.5 text-xs font-semibold text-ink-800 transition hover:border-brand-200 hover:bg-brand-50" @click="abrirEditar(proyecto)">
                        <Pencil :size="13" /> Editar
                    </button>
                    <button class="inline-flex items-center gap-1.5 rounded-lg border border-rose-200 px-3 py-1.5 text-xs font-semibold text-rose-600 transition hover:bg-rose-50" @click="eliminar(proyecto)">
                        <Trash2 :size="13" /> Eliminar
                    </button>
                </div>
            </div>
        </div>

        <Modal :show="showModal" max-width="md" @close="showModal = false">
            <form class="p-6" @submit.prevent="guardar">
                <h2 class="text-base font-bold text-ink-900">{{ editando ? 'Editar proyecto' : 'Nuevo proyecto' }}</h2>

                <div class="mt-4 space-y-4">
                    <div>
                        <InputLabel value="Nombre del proyecto" />
                        <TextInput v-model="form.nombre" class="mt-1" placeholder="Ej. Página web para cafetería X" />
                        <InputError :message="form.errors.nombre" />
                    </div>
                    <div>
                        <InputLabel value="Cliente (opcional)" />
                        <TextInput v-model="form.cliente" class="mt-1" placeholder="¿Para quién es?" />
                    </div>
                    <div>
                        <InputLabel value="Descripción (opcional)" />
                        <Textarea v-model="form.descripcion" class="mt-1" :rows="2" placeholder="¿De qué trata el proyecto?" />
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <InputLabel value="Cuánto vas a cobrar" />
                            <CurrencyInput v-model="form.monto_cobrado" class="mt-1" />
                            <InputError :message="form.errors.monto_cobrado" />
                        </div>
                        <div v-if="editando">
                            <InputLabel value="Estado" />
                            <SelectInput v-model="form.estado" class="mt-1">
                                <option v-for="(label, value) in estados" :key="value" :value="value">{{ label }}</option>
                            </SelectInput>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <InputLabel value="Fecha de inicio" />
                            <TextInput v-model="form.fecha_inicio" type="date" class="mt-1" />
                        </div>
                        <div>
                            <InputLabel value="Fecha de entrega" />
                            <TextInput v-model="form.fecha_entrega" type="date" class="mt-1" :disabled="sinFecha" />
                            <label class="mt-1 flex items-center gap-1.5 text-xs text-ink-500">
                                <input type="checkbox" v-model="sinFecha" class="rounded border-ink-300 text-brand-600 focus:ring-brand-500" />
                                Sin fecha definida
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
