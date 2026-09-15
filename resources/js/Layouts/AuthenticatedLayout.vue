<script setup>
import { computed, onMounted, ref, watch } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { exito, error as mostrarError } from '@/lib/alertas';
import {
    LayoutDashboard, Wallet, Receipt, TrendingDown, Briefcase, PieChart,
    Repeat, LogOut, Menu, X, Wallet as LogoIcon, PanelLeftClose, PanelLeftOpen,
} from '@lucide/vue';

defineProps({
    title: { type: String, default: '' },
    subtitle: { type: String, default: '' },
});

const page = usePage();
const menuAbierto = ref(false);
const colapsado = ref(false);

onMounted(() => {
    try {
        colapsado.value = localStorage.getItem('cg_sidebar_colapsado') === '1';
    } catch {
        // localStorage no disponible (modo privado, etc.) — se queda expandido.
    }
});

function alternarColapso() {
    colapsado.value = !colapsado.value;
    try {
        localStorage.setItem('cg_sidebar_colapsado', colapsado.value ? '1' : '0');
    } catch {
        //
    }
}

watch(
    () => [page.props.flash?.success, page.props.flash?.error],
    ([success, err]) => {
        if (success) exito(success);
        if (err) mostrarError(err);
    },
);

const NAV = [
    { key: 'dashboard', label: 'Inicio', icon: LayoutDashboard, route: 'dashboard' },
    { key: 'cuentas', label: 'Cuentas', icon: Wallet, route: 'cuentas.index' },
    { key: 'movimientos', label: 'Movimientos', icon: Receipt, route: 'movimientos.index' },
    { key: 'recurrentes', label: 'Recurrentes', icon: Repeat, route: 'recurrentes.index' },
    { key: 'deudas', label: 'Deudas', icon: TrendingDown, route: 'deudas.index' },
    { key: 'proyectos', label: 'Proyectos', icon: Briefcase, route: 'proyectos.index' },
    { key: 'reportes', label: 'Reportes', icon: PieChart, route: 'reportes.index' },
];

const esActivo = (nombreRuta) => route().current(nombreRuta) || route().current(nombreRuta.split('.')[0] + '.*');

const iniciales = computed(() => {
    const nombre = page.props.auth.user.name || '';
    return nombre
        .split(' ')
        .slice(0, 2)
        .map((p) => p[0]?.toUpperCase())
        .join('');
});
</script>

<template>
    <div class="min-h-screen bg-ink-50">
        <!-- Sidebar desktop -->
        <aside
            class="fixed inset-y-0 left-0 z-30 hidden flex-col bg-ink-950 transition-all duration-200 lg:flex"
            :class="colapsado ? 'w-20' : 'w-64'"
        >
            <div class="flex items-center px-4 pb-3 pt-7" :class="colapsado ? 'justify-center' : 'justify-between'">
                <div class="flex items-center gap-2.5 overflow-hidden">
                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-brand-500 to-brand-700 shadow-glow">
                        <LogoIcon :size="18" class="text-white" stroke-width="2.25" />
                    </div>
                    <p v-if="!colapsado" class="whitespace-nowrap text-[15px] font-extrabold tracking-tight text-white">Control de Gastos</p>
                </div>
            </div>

            <nav class="mt-4 flex-1 space-y-1 px-3">
                <Link
                    v-for="item in NAV"
                    :key="item.key"
                    :href="route(item.route)"
                    :title="colapsado ? item.label : ''"
                    class="group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition-all duration-150"
                    :class="[
                        colapsado ? 'justify-center' : '',
                        esActivo(item.route)
                            ? 'bg-gradient-to-r from-brand-600/90 to-brand-700/60 text-white shadow-glow'
                            : 'text-ink-300 hover:bg-white/5 hover:text-white',
                    ]"
                >
                    <component
                        :is="item.icon"
                        :size="18"
                        stroke-width="2"
                        class="shrink-0 transition-transform duration-150 group-hover:scale-110"
                        :class="esActivo(item.route) ? 'text-white' : 'text-ink-400 group-hover:text-brand-300'"
                    />
                    <span v-if="!colapsado" class="whitespace-nowrap">{{ item.label }}</span>
                </Link>
            </nav>

            <div class="border-t border-white/10 p-3">
                <button
                    class="mb-1 flex w-full items-center gap-2.5 rounded-xl px-2 py-2 text-left text-xs font-semibold text-ink-400 transition hover:bg-white/5 hover:text-white"
                    :class="colapsado ? 'justify-center' : ''"
                    @click="alternarColapso"
                >
                    <component :is="colapsado ? PanelLeftOpen : PanelLeftClose" :size="16" />
                    <span v-if="!colapsado">Contraer menú</span>
                </button>

                <Link
                    :href="route('profile.edit')"
                    class="flex items-center gap-2.5 rounded-xl px-2 py-2 text-sm text-ink-300 transition hover:bg-white/5 hover:text-white"
                    :class="colapsado ? 'justify-center' : ''"
                >
                    <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-brand-500 to-brand-800 text-xs font-bold text-white">
                        {{ iniciales }}
                    </div>
                    <div v-if="!colapsado" class="min-w-0">
                        <div class="truncate font-semibold text-white">{{ page.props.auth.user.name }}</div>
                        <div class="truncate text-xs text-ink-400">{{ page.props.auth.user.email }}</div>
                    </div>
                </Link>
                <Link
                    :href="route('logout')"
                    method="post"
                    as="button"
                    :title="colapsado ? 'Cerrar sesión' : ''"
                    class="mt-1 flex w-full items-center gap-2.5 rounded-xl px-2 py-2 text-left text-xs font-semibold text-ink-400 transition hover:bg-white/5 hover:text-white"
                    :class="colapsado ? 'justify-center' : ''"
                >
                    <LogOut :size="15" />
                    <span v-if="!colapsado">Cerrar sesión</span>
                </Link>
            </div>
        </aside>

        <!-- Sidebar mobile (drawer) -->
        <Transition
            enter-active-class="transition ease-out duration-200"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition ease-in duration-150"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div v-if="menuAbierto" class="fixed inset-0 z-40 bg-black/60 backdrop-blur-sm lg:hidden" @click="menuAbierto = false" />
        </Transition>
        <Transition
            enter-active-class="transition ease-out duration-200"
            enter-from-class="-translate-x-full"
            enter-to-class="translate-x-0"
            leave-active-class="transition ease-in duration-150"
            leave-from-class="translate-x-0"
            leave-to-class="-translate-x-full"
        >
            <aside v-if="menuAbierto" class="fixed inset-y-0 left-0 z-50 flex w-64 flex-col bg-ink-950 lg:hidden">
                <div class="flex items-center justify-between px-6 pb-3 pt-7">
                    <div class="flex items-center gap-2.5">
                        <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-br from-brand-500 to-brand-700">
                            <LogoIcon :size="18" class="text-white" stroke-width="2.25" />
                        </div>
                        <p class="text-[15px] font-extrabold tracking-tight text-white">Control de Gastos</p>
                    </div>
                    <button class="rounded-lg p-1 text-ink-400 hover:bg-white/5 hover:text-white" @click="menuAbierto = false">
                        <X :size="20" />
                    </button>
                </div>
                <nav class="mt-4 flex-1 space-y-1 px-3">
                    <Link
                        v-for="item in NAV"
                        :key="item.key"
                        :href="route(item.route)"
                        @click="menuAbierto = false"
                        class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition"
                        :class="esActivo(item.route)
                            ? 'bg-gradient-to-r from-brand-600/90 to-brand-700/60 text-white'
                            : 'text-ink-300 hover:bg-white/5 hover:text-white'"
                    >
                        <component :is="item.icon" :size="18" :class="esActivo(item.route) ? 'text-white' : 'text-ink-400'" />
                        {{ item.label }}
                    </Link>
                </nav>
                <div class="border-t border-white/10 p-4">
                    <Link
                        :href="route('logout')"
                        method="post"
                        as="button"
                        class="flex w-full items-center gap-2.5 rounded-xl px-2 py-2 text-left text-xs font-semibold text-ink-400 hover:bg-white/5 hover:text-white"
                    >
                        <LogOut :size="15" />
                        Cerrar sesión
                    </Link>
                </div>
            </aside>
        </Transition>

        <div class="transition-all duration-200" :class="colapsado ? 'lg:pl-20' : 'lg:pl-64'">
            <header class="sticky top-0 z-20 flex items-center gap-3 border-b border-ink-100 bg-white/80 px-4 py-3 backdrop-blur lg:hidden">
                <button class="rounded-lg p-1.5 text-ink-800 hover:bg-ink-50" @click="menuAbierto = true">
                    <Menu :size="22" />
                </button>
                <div class="flex items-center gap-2">
                    <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-gradient-to-br from-brand-500 to-brand-700">
                        <LogoIcon :size="14" class="text-white" />
                    </div>
                    <span class="font-bold text-ink-900">Control de Gastos</span>
                </div>
            </header>

            <main class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8 lg:py-8">
                <div v-if="title" class="mb-6 animate-fade-in">
                    <h1 class="text-2xl font-extrabold tracking-tight text-ink-900">{{ title }}</h1>
                    <p v-if="subtitle" class="mt-0.5 text-sm text-ink-400">{{ subtitle }}</p>
                </div>
                <div class="animate-fade-in">
                    <slot />
                </div>
            </main>
        </div>
    </div>
</template>
