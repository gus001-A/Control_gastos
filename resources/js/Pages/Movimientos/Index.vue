<script setup>
import { computed, ref, watch } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Modal from '@/Components/Modal.vue';
import Card from '@/Components/Card.vue';
import Badge from '@/Components/Badge.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import SelectInput from '@/Components/SelectInput.vue';
import CurrencyInput from '@/Components/CurrencyInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { formatCurrency, formatFecha, hoyIso } from '@/utils';
import { confirmar, advertencia } from '@/lib/alertas';
import { Plus, ArrowLeftRight, Pencil, Trash2, Filter, X, ArrowUpCircle, ArrowDownCircle, Scale } from '@lucide/vue';

const ORIGENES_BLOQUEADOS = ['abono_deuda', 'pago_proyecto'];

const props = defineProps({
    movimientos: Array,
    transferencias: Array,
    cuentas: Array,
    categorias: Array,
    filtros: Object,
    totales: Object,
});

const tab = ref('movimientos');

// --- filtros ---
const filtros = ref({
    cuenta_id: props.filtros.cuenta_id || '',
    categoria_id: props.filtros.categoria_id || '',
    tipo: props.filtros.tipo || '',
    texto: props.filtros.texto || '',
    fecha_inicio: props.filtros.fecha_inicio || '',
    fecha_fin: props.filtros.fecha_fin || '',
});

function aplicarFiltros() {
    router.get(route('movimientos.index'), filtros.value, { preserveState: true, replace: true });
}
function limpiarFiltros() {
    filtros.value = { cuenta_id: '', categoria_id: '', tipo: '', texto: '', fecha_inicio: '', fecha_fin: '' };
    aplicarFiltros();
}

// --- modal movimiento ---
const showModal = ref(false);
const editando = ref(null);
const form = useForm({
    tipo: 'gasto',
    cuenta_id: '',
    categoria_id: '',
    monto: '',
    fecha: hoyIso(),
    descripcion: '',
});

const categoriasFiltradas = computed(() => props.categorias.filter((c) => c.tipo === form.tipo));

function abrirCrear() {
    editando.value = null;
    form.tipo = 'gasto';
    form.cuenta_id = props.cuentas.find((c) => c.activa)?.id || '';
    form.categoria_id = '';
    form.monto = '';
    form.fecha = hoyIso();
    form.descripcion = '';
    form.clearErrors();
    showModal.value = true;
}

function abrirEditar(m) {
    if (ORIGENES_BLOQUEADOS.includes(m.origen)) {
        advertencia('Este movimiento se generó automáticamente desde una deuda o proyecto. Edítalo desde esa sección.');
        return;
    }
    editando.value = m;
    form.tipo = m.tipo;
    form.cuenta_id = m.cuenta_id;
    form.categoria_id = m.categoria_id || '';
    form.monto = m.monto;
    form.fecha = m.fecha.slice(0, 10);
    form.descripcion = m.descripcion || '';
    form.clearErrors();
    showModal.value = true;
}

function guardar() {
    if (editando.value) {
        form.put(route('movimientos.update', editando.value.id), { onSuccess: () => (showModal.value = false) });
    } else {
        form.post(route('movimientos.store'), { onSuccess: () => (showModal.value = false) });
    }
}

function eliminarMovimiento(m) {
    confirmar({
        title: 'Eliminar movimiento',
        content: '¿Eliminar este movimiento? No se puede deshacer.',
        onOk: () => router.delete(route('movimientos.destroy', m.id)),
    });
}

// --- modal transferencia ---
const showTransferModal = ref(false);
const transferForm = useForm({
    cuenta_origen_id: '',
    cuenta_destino_id: '',
    monto: '',
    fecha: hoyIso(),
    descripcion: '',
});

function abrirTransferencia() {
    transferForm.cuenta_origen_id = props.cuentas.find((c) => c.activa)?.id || '';
    transferForm.cuenta_destino_id = props.cuentas.filter((c) => c.activa)[1]?.id || '';
    transferForm.monto = '';
    transferForm.fecha = hoyIso();
    transferForm.descripcion = '';
    transferForm.clearErrors();
    showTransferModal.value = true;
}

function guardarTransferencia() {
    transferForm.post(route('transferencias.store'), { onSuccess: () => (showTransferModal.value = false) });
}

function eliminarTransferencia(t) {
    confirmar({
        title: 'Eliminar transferencia',
        content: '¿Eliminar esta transferencia? No se puede deshacer.',
        onOk: () => router.delete(route('transferencias.destroy', t.id)),
    });
}
</script>

<template>
    <Head title="Movimientos" />

    <AuthenticatedLayout title="Movimientos" subtitle="Ingresos, gastos y transferencias entre cuentas">
        <div class="mb-5 flex flex-wrap justify-end gap-2">
            <SecondaryButton @click="abrirTransferencia"><ArrowLeftRight :size="15" /> Transferir</SecondaryButton>
            <PrimaryButton @click="abrirCrear"><Plus :size="16" /> Nuevo movimiento</PrimaryButton>
        </div>

        <Card class="mb-5">
            <div class="flex flex-wrap items-end gap-3">
                <div class="w-40">
                    <InputLabel value="Cuenta" />
                    <SelectInput v-model="filtros.cuenta_id" class="mt-1">
                        <option value="">Todas</option>
                        <option v-for="c in cuentas" :key="c.id" :value="c.id">{{ c.nombre }}</option>
                    </SelectInput>
                </div>
                <div class="w-36">
                    <InputLabel value="Tipo" />
                    <SelectInput v-model="filtros.tipo" class="mt-1">
                        <option value="">Todos</option>
                        <option value="ingreso">Ingresos</option>
                        <option value="gasto">Gastos</option>
                    </SelectInput>
                </div>
                <div class="w-48">
                    <InputLabel value="Categoría" />
                    <SelectInput v-model="filtros.categoria_id" class="mt-1">
                        <option value="">Todas</option>
                        <option v-for="c in categorias" :key="c.id" :value="c.id">{{ c.nombre }}</option>
                    </SelectInput>
                </div>
                <div class="w-36">
                    <InputLabel value="Desde" />
                    <TextInput v-model="filtros.fecha_inicio" type="date" class="mt-1" />
                </div>
                <div class="w-36">
                    <InputLabel value="Hasta" />
                    <TextInput v-model="filtros.fecha_fin" type="date" class="mt-1" />
                </div>
                <div class="min-w-[180px] flex-1">
                    <InputLabel value="Buscar" />
                    <TextInput v-model="filtros.texto" class="mt-1" placeholder="Buscar en descripción..." @keyup.enter="aplicarFiltros" />
                </div>
                <SecondaryButton @click="aplicarFiltros"><Filter :size="14" /> Filtrar</SecondaryButton>
                <button class="inline-flex items-center gap-1 text-xs font-semibold text-ink-400 transition hover:text-ink-600" @click="limpiarFiltros">
                    <X :size="13" /> Limpiar
                </button>
            </div>
        </Card>

        <div class="mb-5 grid grid-cols-1 gap-4 sm:grid-cols-3">
            <div class="flex items-center gap-3 rounded-2xl border border-ink-100 bg-white p-4 shadow-card">
                <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-green-50 text-green-600"><ArrowUpCircle :size="17" /></div>
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-ink-400">Ingresos</div>
                    <div class="text-lg font-extrabold text-green-600">{{ formatCurrency(totales.ingresos) }}</div>
                </div>
            </div>
            <div class="flex items-center gap-3 rounded-2xl border border-ink-100 bg-white p-4 shadow-card">
                <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-rose-50 text-rose-600"><ArrowDownCircle :size="17" /></div>
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-ink-400">Gastos</div>
                    <div class="text-lg font-extrabold text-rose-600">{{ formatCurrency(totales.gastos) }}</div>
                </div>
            </div>
            <div class="flex items-center gap-3 rounded-2xl border border-ink-100 bg-white p-4 shadow-card">
                <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-brand-50 text-brand-600"><Scale :size="17" /></div>
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wide text-ink-400">Balance</div>
                    <div class="text-lg font-extrabold" :class="totales.ingresos - totales.gastos < 0 ? 'text-rose-600' : 'text-ink-900'">
                        {{ formatCurrency(totales.ingresos - totales.gastos) }}
                    </div>
                </div>
            </div>
        </div>

        <div class="mb-3 flex gap-1 border-b border-ink-200">
            <button
                class="border-b-2 px-4 py-2 text-sm font-semibold transition"
                :class="tab === 'movimientos' ? 'border-brand-600 text-brand-700' : 'border-transparent text-ink-400 hover:text-ink-600'"
                @click="tab = 'movimientos'"
            >
                Movimientos
            </button>
            <button
                class="border-b-2 px-4 py-2 text-sm font-semibold transition"
                :class="tab === 'transferencias' ? 'border-brand-600 text-brand-700' : 'border-transparent text-ink-400 hover:text-ink-600'"
                @click="tab = 'transferencias'"
            >
                Transferencias
            </button>
        </div>

        <Card v-if="tab === 'movimientos'" :padded="false">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-ink-100 text-left text-xs font-semibold uppercase tracking-wide text-ink-400">
                            <th class="px-5 py-3">Fecha</th>
                            <th class="px-5 py-3">Tipo</th>
                            <th class="px-5 py-3">Cuenta</th>
                            <th class="px-5 py-3">Categoría</th>
                            <th class="px-5 py-3">Descripción</th>
                            <th class="px-5 py-3 text-right">Monto</th>
                            <th class="px-5 py-3"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="movimientos.length === 0">
                            <td colspan="7" class="px-5 py-10 text-center text-ink-400">No hay movimientos con estos filtros.</td>
                        </tr>
                        <tr v-for="m in movimientos" :key="m.id" class="border-b border-ink-50 last:border-0 hover:bg-ink-50/60">
                            <td class="px-5 py-3 text-ink-500">{{ formatFecha(m.fecha) }}</td>
                            <td class="px-5 py-3">
                                <Badge :color="m.tipo === 'ingreso' ? '#16a34a' : '#e11d48'">{{ m.tipo === 'ingreso' ? 'Ingreso' : 'Gasto' }}</Badge>
                            </td>
                            <td class="px-5 py-3 text-ink-800">{{ m.cuenta.nombre }}</td>
                            <td class="px-5 py-3">
                                <Badge v-if="m.categoria" :color="m.categoria.color">{{ m.categoria.nombre }}</Badge>
                                <span v-else class="text-ink-300">-</span>
                            </td>
                            <td class="px-5 py-3 text-ink-500">{{ m.descripcion || '-' }}</td>
                            <td class="px-5 py-3 text-right font-semibold" :class="m.tipo === 'ingreso' ? 'text-brand-700' : 'text-rose-600'">
                                {{ m.tipo === 'ingreso' ? '+' : '-' }} {{ formatCurrency(m.monto) }}
                            </td>
                            <td class="px-5 py-3 text-right">
                                <div class="flex justify-end gap-3 whitespace-nowrap">
                                    <button class="text-ink-400 transition hover:text-brand-600" title="Editar" @click="abrirEditar(m)"><Pencil :size="15" /></button>
                                    <button
                                        v-if="!ORIGENES_BLOQUEADOS.includes(m.origen)"
                                        class="text-ink-400 transition hover:text-rose-600"
                                        title="Eliminar"
                                        @click="eliminarMovimiento(m)"
                                    >
                                        <Trash2 :size="15" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </Card>

        <Card v-else :padded="false">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-ink-100 text-left text-xs font-semibold uppercase tracking-wide text-ink-400">
                            <th class="px-5 py-3">Fecha</th>
                            <th class="px-5 py-3">De</th>
                            <th class="px-5 py-3">A</th>
                            <th class="px-5 py-3">Descripción</th>
                            <th class="px-5 py-3 text-right">Monto</th>
                            <th class="px-5 py-3"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="transferencias.length === 0">
                            <td colspan="6" class="px-5 py-10 text-center text-ink-400">Aún no has hecho transferencias entre cuentas.</td>
                        </tr>
                        <tr v-for="t in transferencias" :key="t.id" class="border-b border-ink-50 last:border-0 hover:bg-ink-50/60">
                            <td class="px-5 py-3 text-ink-500">{{ formatFecha(t.fecha) }}</td>
                            <td class="px-5 py-3 text-ink-800">{{ t.cuenta_origen.nombre }}</td>
                            <td class="px-5 py-3 text-ink-800">{{ t.cuenta_destino.nombre }}</td>
                            <td class="px-5 py-3 text-ink-500">{{ t.descripcion || '-' }}</td>
                            <td class="px-5 py-3 text-right font-semibold text-ink-900">{{ formatCurrency(t.monto) }}</td>
                            <td class="px-5 py-3 text-right">
                                <button class="text-ink-400 transition hover:text-rose-600" title="Eliminar" @click="eliminarTransferencia(t)"><Trash2 :size="15" /></button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </Card>

        <!-- Modal movimiento -->
        <Modal :show="showModal" max-width="md" @close="showModal = false">
            <form class="p-6" @submit.prevent="guardar">
                <h2 class="text-base font-bold text-ink-900">{{ editando ? 'Editar movimiento' : 'Nuevo movimiento' }}</h2>

                <div class="mt-4 space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <InputLabel value="Tipo" />
                            <SelectInput v-model="form.tipo" class="mt-1" @update:model-value="form.categoria_id = ''">
                                <option value="gasto">Gasto</option>
                                <option value="ingreso">Ingreso</option>
                            </SelectInput>
                        </div>
                        <div>
                            <InputLabel value="Cuenta" />
                            <SelectInput v-model="form.cuenta_id" class="mt-1">
                                <option v-for="c in cuentas.filter((c) => c.activa)" :key="c.id" :value="c.id">{{ c.nombre }}</option>
                            </SelectInput>
                            <InputError :message="form.errors.cuenta_id" />
                        </div>
                    </div>

                    <div>
                        <InputLabel value="Categoría" />
                        <SelectInput v-model="form.categoria_id" class="mt-1">
                            <option value="">(Sin categoría)</option>
                            <option v-for="c in categoriasFiltradas" :key="c.id" :value="c.id">{{ c.nombre }}</option>
                        </SelectInput>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <InputLabel value="Monto" />
                            <CurrencyInput v-model="form.monto" class="mt-1" />
                            <InputError :message="form.errors.monto" />
                        </div>
                        <div>
                            <InputLabel value="Fecha" />
                            <TextInput v-model="form.fecha" type="date" class="mt-1" />
                            <InputError :message="form.errors.fecha" />
                        </div>
                    </div>

                    <div>
                        <InputLabel value="Descripción" />
                        <TextInput v-model="form.descripcion" class="mt-1" placeholder="Ej. Súper, gasolina, pago de Netflix..." />
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <SecondaryButton type="button" @click="showModal = false">Cancelar</SecondaryButton>
                    <PrimaryButton :disabled="form.processing">Guardar</PrimaryButton>
                </div>
            </form>
        </Modal>

        <!-- Modal transferencia -->
        <Modal :show="showTransferModal" max-width="md" @close="showTransferModal = false">
            <form class="p-6" @submit.prevent="guardarTransferencia">
                <h2 class="text-base font-bold text-ink-900">Nueva transferencia</h2>

                <div class="mt-4 space-y-4">
                    <div>
                        <InputLabel value="De la cuenta" />
                        <SelectInput v-model="transferForm.cuenta_origen_id" class="mt-1">
                            <option v-for="c in cuentas.filter((c) => c.activa)" :key="c.id" :value="c.id">{{ c.nombre }}</option>
                        </SelectInput>
                    </div>
                    <div>
                        <InputLabel value="A la cuenta" />
                        <SelectInput v-model="transferForm.cuenta_destino_id" class="mt-1">
                            <option v-for="c in cuentas.filter((c) => c.activa)" :key="c.id" :value="c.id">{{ c.nombre }}</option>
                        </SelectInput>
                        <InputError :message="transferForm.errors.cuenta_origen_id" />
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <InputLabel value="Monto" />
                            <CurrencyInput v-model="transferForm.monto" class="mt-1" />
                            <InputError :message="transferForm.errors.monto" />
                        </div>
                        <div>
                            <InputLabel value="Fecha" />
                            <TextInput v-model="transferForm.fecha" type="date" class="mt-1" />
                        </div>
                    </div>
                    <div>
                        <InputLabel value="Descripción (opcional)" />
                        <TextInput v-model="transferForm.descripcion" class="mt-1" />
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <SecondaryButton type="button" @click="showTransferModal = false">Cancelar</SecondaryButton>
                    <PrimaryButton :disabled="transferForm.processing">Transferir</PrimaryButton>
                </div>
            </form>
        </Modal>
    </AuthenticatedLayout>
</template>
