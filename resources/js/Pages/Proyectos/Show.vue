<script setup>
import { ref } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Card from '@/Components/Card.vue';
import Modal from '@/Components/Modal.vue';
import ProgressBar from '@/Components/ProgressBar.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import CurrencyInput from '@/Components/CurrencyInput.vue';
import SelectInput from '@/Components/SelectInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { formatCurrency, formatFecha, hoyIso } from '@/utils';
import { confirmar } from '@/lib/alertas';
import { Plus, Trash2, ArrowLeft, ArrowUpCircle, Wallet, StickyNote, CalendarDays, History } from '@lucide/vue';

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

function eliminarPago(pago) {
    confirmar({
        title: 'Eliminar pago',
        content: '¿Eliminar este pago? Se revertirá el monto en la cuenta asociada.',
        onOk: () => router.delete(route('pagos.destroy', pago.id)),
    });
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

        <Card title="Historial de pagos" :icon="History" class="mt-6" :padded="false">
            <div v-if="pagos.length === 0" class="px-5 py-10 text-center text-sm text-ink-400">Aún no has registrado pagos.</div>
            <div v-else class="divide-y divide-ink-50">
                <div
                    v-for="(p, i) in pagos"
                    :key="p.id"
                    class="group flex animate-fade-in items-center gap-4 px-5 py-4 transition-colors hover:bg-ink-50/60"
                    :style="{ animationDelay: `${i * 30}ms` }"
                >
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-green-50 text-green-600">
                        <ArrowUpCircle :size="17" />
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-x-2 gap-y-0.5">
                            <span class="inline-flex items-center gap-1 text-sm font-bold text-ink-900"><CalendarDays :size="12" class="text-ink-300" /> {{ formatFecha(p.fecha) }}</span>
                            <span v-if="p.cuenta" class="inline-flex items-center gap-1 text-xs font-medium text-ink-400"><Wallet :size="11" /> {{ p.cuenta.nombre }}</span>
                        </div>
                        <div v-if="p.notas" class="mt-0.5 inline-flex items-center gap-1 text-xs text-ink-400"><StickyNote :size="11" /> {{ p.notas }}</div>
                    </div>
                    <div class="shrink-0 text-right text-base font-extrabold text-green-600">{{ formatCurrency(p.monto) }}</div>
                    <button
                        class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg text-ink-300 opacity-0 transition hover:bg-rose-50 hover:text-rose-600 group-hover:opacity-100"
                        title="Eliminar"
                        @click="eliminarPago(p)"
                    >
                        <Trash2 :size="15" />
                    </button>
                </div>
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
                        <CurrencyInput v-model="form.monto" class="mt-1" />
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
    </AuthenticatedLayout>
</template>
