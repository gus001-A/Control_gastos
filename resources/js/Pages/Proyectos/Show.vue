<script setup>
import { ref } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Card from '@/Components/Card.vue';
import Modal from '@/Components/Modal.vue';
import ConfirmDialog from '@/Components/ConfirmDialog.vue';
import ProgressBar from '@/Components/ProgressBar.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import SelectInput from '@/Components/SelectInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { formatCurrency, formatFecha, hoyIso } from '@/utils';
import { Plus, Trash2, ArrowLeft } from '@lucide/vue';

const props = defineProps({
    proyecto: Object,
    pagos: Array,
    estados: Object,
    cuentas: Array,
});

const showModal = ref(false);
const form = useForm({
    cuenta_id: '',
    monto: '',
    fecha: hoyIso(),
    notas: '',
});

function abrirPago() {
    form.cuenta_id = '';
    form.fecha = hoyIso();
    form.monto = props.proyecto.pendiente > 0 ? props.proyecto.pendiente : '';
    form.notas = '';
    form.clearErrors();
    showModal.value = true;
}

function guardar() {
    form.post(route('pagos.store', props.proyecto.id), { onSuccess: () => (showModal.value = false) });
}

const confirmando = ref(null);
function eliminarPago() {
    router.delete(route('pagos.destroy', confirmando.value.id), { onFinish: () => (confirmando.value = null) });
}
</script>

<template>
    <Head :title="proyecto.nombre" />

    <AuthenticatedLayout>
        <Link :href="route('proyectos.index')" class="mb-4 inline-flex items-center gap-1.5 text-sm font-semibold text-ink-500 transition hover:text-ink-900">
            <ArrowLeft :size="15" /> Volver a proyectos
        </Link>

        <Card>
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <h1 class="text-xl font-extrabold text-ink-900">{{ proyecto.nombre }}</h1>
                    <p class="mt-1 text-sm text-ink-500">
                        <span v-if="proyecto.cliente">Cliente: {{ proyecto.cliente }} · </span>
                        <span v-if="proyecto.fecha_entrega">Entrega: {{ formatFecha(proyecto.fecha_entrega) }} · </span>
                        Estado: {{ estados[proyecto.estado] }}
                    </p>
                    <p v-if="proyecto.descripcion" class="mt-2 max-w-2xl text-sm text-ink-600">{{ proyecto.descripcion }}</p>
                </div>
                <PrimaryButton @click="abrirPago"><Plus :size="16" /> Registrar pago recibido</PrimaryButton>
            </div>

            <ProgressBar :value="proyecto.progreso" class="mt-5" color="#0ea5e9" />
            <div class="mt-2 text-sm text-ink-600">
                Cobrado: <strong>{{ formatCurrency(proyecto.monto_cobrado) }}</strong> ·
                Pagado: <strong class="text-brand-700">{{ formatCurrency(proyecto.pagado) }}</strong> ·
                Pendiente: <strong class="text-amber-600">{{ formatCurrency(proyecto.pendiente) }}</strong>
            </div>
        </Card>

        <Card title="Historial de pagos" class="mt-6" :padded="false">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-ink-100 text-left text-xs font-semibold uppercase tracking-wide text-ink-400">
                            <th class="px-5 py-3">Fecha</th>
                            <th class="px-5 py-3">Cuenta</th>
                            <th class="px-5 py-3">Notas</th>
                            <th class="px-5 py-3 text-right">Monto</th>
                            <th class="px-5 py-3"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="pagos.length === 0">
                            <td colspan="5" class="px-5 py-10 text-center text-ink-400">Aún no has registrado pagos.</td>
                        </tr>
                        <tr v-for="p in pagos" :key="p.id" class="border-b border-ink-50 last:border-0 hover:bg-ink-50/60">
                            <td class="px-5 py-3 text-ink-500">{{ formatFecha(p.fecha) }}</td>
                            <td class="px-5 py-3 text-ink-800">{{ p.cuenta ? p.cuenta.nombre : '-' }}</td>
                            <td class="px-5 py-3 text-ink-500">{{ p.notas || '-' }}</td>
                            <td class="px-5 py-3 text-right font-semibold text-brand-700">{{ formatCurrency(p.monto) }}</td>
                            <td class="px-5 py-3 text-right">
                                <button class="text-ink-400 transition hover:text-rose-600" title="Eliminar" @click="confirmando = p"><Trash2 :size="15" /></button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </Card>

        <Modal :show="showModal" max-width="sm" @close="showModal = false">
            <form class="p-6" @submit.prevent="guardar">
                <h2 class="text-base font-bold text-ink-900">Registrar pago recibido</h2>

                <div class="mt-4 space-y-4">
                    <div>
                        <InputLabel value="Depositar en" />
                        <SelectInput v-model="form.cuenta_id" class="mt-1">
                            <option value="">(No depositar en ninguna cuenta)</option>
                            <option v-for="c in cuentas" :key="c.id" :value="c.id">{{ c.nombre }}</option>
                        </SelectInput>
                    </div>
                    <div>
                        <InputLabel :value="`Monto recibido (faltan ${formatCurrency(proyecto.pendiente)})`" />
                        <TextInput v-model="form.monto" type="number" step="0.01" class="mt-1" />
                        <InputError :message="form.errors.monto" />
                    </div>
                    <div>
                        <InputLabel value="Fecha" />
                        <TextInput v-model="form.fecha" type="date" class="mt-1" />
                    </div>
                    <div>
                        <InputLabel value="Notas (opcional)" />
                        <TextInput v-model="form.notas" class="mt-1" placeholder="Ej. Anticipo, pago final..." />
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
            title="Eliminar pago"
            message="¿Eliminar este pago? Se revertirá el monto en la cuenta asociada."
            @confirm="eliminarPago"
            @cancel="confirmando = null"
        />
    </AuthenticatedLayout>
</template>
