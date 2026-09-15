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

const props = defineProps({
    deuda: Object,
    abonos: Array,
    cuentas: Array,
});

const showModal = ref(false);
const form = useForm({
    cuenta_id: '',
    monto: '',
    fecha: hoyIso(),
    notas: '',
});

function abrirAbono() {
    form.cuenta_id = '';
    form.fecha = hoyIso();
    form.monto = props.deuda.restante > 0 ? props.deuda.restante : '';
    form.notas = '';
    form.clearErrors();
    showModal.value = true;
}

function guardar() {
    form.post(route('abonos.store', props.deuda.id), { onSuccess: () => (showModal.value = false) });
}

const confirmando = ref(null);
function eliminarAbono() {
    router.delete(route('abonos.destroy', confirmando.value.id), { onFinish: () => (confirmando.value = null) });
}
</script>

<template>
    <Head :title="deuda.nombre" />

    <AuthenticatedLayout>
        <Link :href="route('deudas.index')" class="mb-4 inline-flex items-center gap-1 text-sm font-semibold text-gray-500 hover:text-ink-900">
            ← Volver a deudas
        </Link>

        <Card>
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <h1 class="text-xl font-extrabold text-ink-900">{{ deuda.nombre }}</h1>
                    <p class="mt-1 text-sm text-gray-500">
                        <span v-if="deuda.acreedor">Acreedor: {{ deuda.acreedor }} · </span>
                        <span v-if="deuda.fecha_limite">Fecha límite: {{ formatFecha(deuda.fecha_limite) }} · </span>
                        <span v-if="deuda.tasa_interes">Tasa: {{ deuda.tasa_interes }}% anual · </span>
                        Estado: {{ deuda.estado === 'pagada' ? 'Pagada ✅' : 'Activa' }}
                    </p>
                </div>
                <PrimaryButton @click="abrirAbono">+ Registrar abono</PrimaryButton>
            </div>

            <ProgressBar :value="deuda.progreso" class="mt-5" :color="deuda.estado === 'pagada' ? '#16a34a' : '#0ea5e9'" />
            <div class="mt-2 text-sm text-gray-600">
                Total: <strong>{{ formatCurrency(deuda.monto_total) }}</strong> ·
                Pagado: <strong class="text-brand-700">{{ formatCurrency(deuda.pagado) }}</strong> ·
                Restante: <strong class="text-rose-600">{{ formatCurrency(deuda.restante) }}</strong>
            </div>
        </Card>

        <Card title="Historial de abonos" class="mt-6" :padded="false">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 text-left text-xs font-semibold uppercase tracking-wide text-gray-400">
                            <th class="px-5 py-3">Fecha</th>
                            <th class="px-5 py-3">Cuenta</th>
                            <th class="px-5 py-3">Notas</th>
                            <th class="px-5 py-3 text-right">Monto</th>
                            <th class="px-5 py-3"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="abonos.length === 0">
                            <td colspan="5" class="px-5 py-10 text-center text-gray-400">Aún no has registrado abonos.</td>
                        </tr>
                        <tr v-for="a in abonos" :key="a.id" class="border-b border-gray-50 last:border-0 hover:bg-gray-50/60">
                            <td class="px-5 py-3 text-gray-500">{{ formatFecha(a.fecha) }}</td>
                            <td class="px-5 py-3 text-ink-800">{{ a.cuenta ? a.cuenta.nombre : '-' }}</td>
                            <td class="px-5 py-3 text-gray-500">{{ a.notas || '-' }}</td>
                            <td class="px-5 py-3 text-right font-semibold text-brand-700">{{ formatCurrency(a.monto) }}</td>
                            <td class="px-5 py-3 text-right">
                                <button class="text-xs font-semibold text-rose-500 hover:text-rose-700" @click="confirmando = a">Eliminar</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </Card>

        <Modal :show="showModal" max-width="sm" @close="showModal = false">
            <form class="p-6" @submit.prevent="guardar">
                <h2 class="text-base font-bold text-ink-900">Registrar abono</h2>

                <div class="mt-4 space-y-4">
                    <div>
                        <InputLabel value="Pagar desde" />
                        <SelectInput v-model="form.cuenta_id" class="mt-1">
                            <option value="">(No descontar de ninguna cuenta)</option>
                            <option v-for="c in cuentas" :key="c.id" :value="c.id">{{ c.nombre }}</option>
                        </SelectInput>
                    </div>
                    <div>
                        <InputLabel :value="`Monto del abono (restan ${formatCurrency(deuda.restante)})`" />
                        <TextInput v-model="form.monto" type="number" step="0.01" class="mt-1" />
                        <InputError :message="form.errors.monto" />
                    </div>
                    <div>
                        <InputLabel value="Fecha" />
                        <TextInput v-model="form.fecha" type="date" class="mt-1" />
                    </div>
                    <div>
                        <InputLabel value="Notas (opcional)" />
                        <TextInput v-model="form.notas" class="mt-1" />
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
            title="Eliminar abono"
            message="¿Eliminar este abono? Se revertirá el monto en la cuenta asociada."
            @confirm="eliminarAbono"
            @cancel="confirmando = null"
        />
    </AuthenticatedLayout>
</template>
