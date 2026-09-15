<script setup>
import { computed, ref } from 'vue';
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
import { Plus, Trash2, ArrowLeft, CreditCard, TrendingDown, Wallet, StickyNote, CalendarDays, History } from '@lucide/vue';

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

const cuentaSeleccionada = computed(() => props.cuentas.find((c) => c.id === form.cuenta_id));
const fondosInsuficientes = computed(
    () => cuentaSeleccionada.value && Number(form.monto) > cuentaSeleccionada.value.saldo,
);

function guardar() {
    form.post(route('abonos.store', props.deuda.id), { onSuccess: () => (showModal.value = false) });
}

function eliminarAbono(abono) {
    confirmar({
        title: 'Eliminar abono',
        content: '¿Eliminar este abono? Se revertirá el monto en la cuenta asociada.',
        onOk: () => router.delete(route('abonos.destroy', abono.id)),
    });
}
</script>

<template>
    <Head :title="deuda.nombre" />

    <AuthenticatedLayout>
        <Link :href="route('deudas.index')" class="mb-4 inline-flex items-center gap-1.5 text-sm font-semibold text-ink-500 transition hover:text-ink-900">
            <ArrowLeft :size="15" /> Volver a deudas
        </Link>

        <Card>
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div class="flex items-center gap-3">
                    <div
                        class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl"
                        :class="deuda.estado === 'pagada' ? 'bg-green-50 text-green-600' : 'bg-rose-50 text-rose-600'"
                    >
                        <CreditCard :size="22" />
                    </div>
                    <div>
                        <h1 class="text-xl font-extrabold text-ink-900">{{ deuda.nombre }}</h1>
                        <p class="mt-0.5 text-sm text-ink-500">
                            <span v-if="deuda.acreedor">{{ deuda.acreedor }} · </span>
                            <span v-if="deuda.fecha_limite">Fecha límite: {{ formatFecha(deuda.fecha_limite) }} · </span>
                            {{ deuda.estado === 'pagada' ? 'Pagada' : 'Activa' }}
                        </p>
                    </div>
                </div>
                <PrimaryButton @click="abrirAbono"><Plus :size="16" /> Registrar abono</PrimaryButton>
            </div>

            <ProgressBar :value="deuda.progreso" class="mt-6" :color="deuda.estado === 'pagada' ? '#16a34a' : '#9333ea'" />

            <div class="mt-4 grid grid-cols-3 gap-3">
                <div class="rounded-xl bg-ink-50 p-3 text-center">
                    <div class="text-xs font-semibold uppercase tracking-wide text-ink-400">Total</div>
                    <div class="mt-1 text-lg font-extrabold text-ink-900">{{ formatCurrency(deuda.monto_total) }}</div>
                </div>
                <div class="rounded-xl bg-green-50 p-3 text-center">
                    <div class="text-xs font-semibold uppercase tracking-wide text-green-700">Pagado</div>
                    <div class="mt-1 text-lg font-extrabold text-green-700">{{ formatCurrency(deuda.pagado) }}</div>
                </div>
                <div class="rounded-xl bg-rose-50 p-3 text-center">
                    <div class="text-xs font-semibold uppercase tracking-wide text-rose-700">Restante</div>
                    <div class="mt-1 text-lg font-extrabold text-rose-700">{{ formatCurrency(deuda.restante) }}</div>
                </div>
            </div>
        </Card>

        <Card title="Historial de abonos" :icon="History" class="mt-6" :padded="false">
            <div v-if="abonos.length === 0" class="px-5 py-10 text-center text-sm text-ink-400">Aún no has registrado abonos.</div>
            <div v-else class="divide-y divide-ink-50">
                <div
                    v-for="(a, i) in abonos"
                    :key="a.id"
                    class="group flex animate-fade-in items-center gap-4 px-5 py-4 transition-colors hover:bg-ink-50/60"
                    :style="{ animationDelay: `${i * 30}ms` }"
                >
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-brand-50 text-brand-600">
                        <TrendingDown :size="17" />
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-x-2 gap-y-0.5">
                            <span class="inline-flex items-center gap-1 text-sm font-bold text-ink-900"><CalendarDays :size="12" class="text-ink-300" /> {{ formatFecha(a.fecha) }}</span>
                            <span v-if="a.cuenta" class="inline-flex items-center gap-1 text-xs font-medium text-ink-400"><Wallet :size="11" /> {{ a.cuenta.nombre }}</span>
                        </div>
                        <div v-if="a.notas" class="mt-0.5 inline-flex items-center gap-1 text-xs text-ink-400"><StickyNote :size="11" /> {{ a.notas }}</div>
                    </div>
                    <div class="shrink-0 text-right text-base font-extrabold text-brand-700">{{ formatCurrency(a.monto) }}</div>
                    <button
                        class="shrink-0 text-ink-300 opacity-0 transition hover:text-rose-600 group-hover:opacity-100"
                        title="Eliminar"
                        @click="eliminarAbono(a)"
                    >
                        <Trash2 :size="15" />
                    </button>
                </div>
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
                            <option v-for="c in cuentas" :key="c.id" :value="c.id">{{ c.nombre }} ({{ formatCurrency(c.saldo) }})</option>
                        </SelectInput>
                        <p v-if="fondosInsuficientes" class="mt-1.5 text-xs font-semibold text-rose-600">
                            Esa cuenta no tiene saldo suficiente para este abono.
                        </p>
                    </div>
                    <div>
                        <InputLabel :value="`Monto del abono (restan ${formatCurrency(deuda.restante)})`" />
                        <CurrencyInput v-model="form.monto" class="mt-1" />
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
    </AuthenticatedLayout>
</template>
