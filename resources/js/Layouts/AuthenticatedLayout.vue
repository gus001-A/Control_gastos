<script setup>
import { computed, onMounted, ref, watch } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { exito, error as mostrarError } from '@/lib/alertas';
import Alertas from '@/Components/Alertas.vue';
import {
    LayoutDashboard, Wallet, Receipt, TrendingDown, Briefcase, PieChart,
    Repeat, LogOut, User as UserIcon, Menu, X, Wallet as LogoIcon, ChevronDown,
} from '@lucide/vue';

defineProps({
    title: { type: String, default: '' },
    subtitle: { type: String, default: '' },
});

const page = usePage();
const menuAbierto = ref(false);
const userMenuAbierto = ref(false);

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

const esActivo = (nombreRuta) =>
    route().current(nombreRuta) || route().current(nombreRuta.split('.')[0] + '.*');

const iniciales = computed(() => {
    const nombre = page.props.auth.user.name || '';
    return nombre
        .split(' ')
        .slice(0, 2)
        .map((p) => p[0]?.toUpperCase())
        .join('');
});

onMounted(() => {
    document.addEventListener('click', (e) => {
        if (!e.target.closest('[data-user-menu]')) {
            userMenuAbierto.value = false;
        }
    });
});
</script>

<template>
    <div class="min-h-screen">

        <!-- NAVBAR DESKTOP -->
        <header class="sticky top-0 z-30 hidden border-b border-neutral-200/80 bg-white/80 backdrop-blur-xl lg:block">
            <div class="mx-auto grid h-16 max-w-[1600px] grid-cols-[1fr_auto_1fr] items-center gap-6 px-6">

                <!-- Logo -->
                <Link :href="route('dashboard')" class="group flex shrink-0 items-center gap-2.5 justify-self-start">
                    <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-br from-purple-500 via-purple-700 to-neutral-950 shadow-md shadow-purple-900/30 transition-transform duration-500 group-hover:rotate-[360deg]">
                        <LogoIcon :size="17" class="text-white" stroke-width="2.25" />
                    </div>
                    <span class="text-gradient hidden whitespace-nowrap text-base font-bold tracking-tight xl:inline">
                        Control de Gastos
                    </span>
                </Link>

                <!-- Navegación -->
                <nav class="flex items-center justify-center gap-1">
                    <Link
                        v-for="item in NAV"
                        :key="item.key"
                        :href="route(item.route)"
                        class="group relative flex items-center gap-2 rounded-xl px-3.5 py-2 text-[13px] font-semibold transition-all duration-300"
                        :class="esActivo(item.route)
                            ? 'bg-neutral-950 text-white shadow-lg shadow-purple-900/30'
                            : 'text-neutral-600 hover:-translate-y-0.5 hover:bg-purple-50 hover:text-purple-800'"
                    >
                        <component
                            :is="item.icon"
                            :size="16"
                            stroke-width="2"
                            class="shrink-0 transition-transform duration-300 group-hover:scale-125 group-hover:-rotate-6"
                            :class="esActivo(item.route) ? 'text-purple-400' : 'text-neutral-400 group-hover:text-purple-600'"
                        />
                        <span class="whitespace-nowrap">{{ item.label }}</span>
                        <!-- Punto morado del activo -->
                        <span
                            v-if="esActivo(item.route)"
                            class="absolute -bottom-1 left-1/2 h-1 w-6 -translate-x-1/2 rounded-full bg-purple-500 shadow-[0_0_8px_2px_rgba(168,85,247,0.6)]"
                        ></span>
                    </Link>
                </nav>

                <!-- User dropdown a la derecha -->
                <div class="relative shrink-0 justify-self-end" data-user-menu>
                    <button
                        class="flex items-center gap-2 rounded-xl border border-neutral-200 px-2 py-1.5 transition-all duration-300 hover:border-purple-300 hover:bg-purple-50 hover:shadow-md"
                        @click="userMenuAbierto = !userMenuAbierto"
                    >
                        <div class="flex h-7 w-7 items-center justify-center rounded-full bg-gradient-to-br from-purple-500 to-neutral-950 text-[11px] font-bold text-white">
                            {{ iniciales }}
                        </div>
                        <span class="hidden max-w-[140px] truncate text-[13px] font-medium text-neutral-700 xl:inline">
                            {{ page.props.auth.user.name }}
                        </span>
                        <ChevronDown
                            :size="14"
                            class="text-neutral-400 transition-transform duration-300"
                            :class="userMenuAbierto ? 'rotate-180 text-purple-600' : ''"
                        />
                    </button>

                    <!-- Dropdown -->
                    <Transition
                        enter-active-class="transition ease-out duration-200"
                        enter-from-class="opacity-0 -translate-y-2 scale-95"
                        enter-to-class="opacity-100 translate-y-0 scale-100"
                        leave-active-class="transition ease-in duration-150"
                        leave-from-class="opacity-100 translate-y-0 scale-100"
                        leave-to-class="opacity-0 -translate-y-2 scale-95"
                    >
                        <div
                            v-if="userMenuAbierto"
                            class="absolute right-0 mt-2 w-64 origin-top-right overflow-hidden rounded-xl border border-neutral-200 bg-white shadow-xl"
                        >
                            <div class="border-b border-neutral-100 px-4 py-3">
                                <p class="truncate text-sm font-semibold text-neutral-900">
                                    {{ page.props.auth.user.name }}
                                </p>
                                <p class="truncate text-xs text-neutral-500">
                                    {{ page.props.auth.user.email }}
                                </p>
                            </div>
                            <div class="p-1.5">
                                <Link
                                    :href="route('profile.edit')"
                                    class="flex items-center gap-2.5 rounded-lg px-3 py-2 text-sm text-neutral-700 transition-all duration-200 hover:translate-x-1 hover:bg-purple-50 hover:text-purple-800"
                                    @click="userMenuAbierto = false"
                                >
                                    <UserIcon :size="15" class="text-purple-600" />
                                    Mi perfil
                                </Link>
                                <Link
                                    v-if="!page.props.auth.autoLogin"
                                    :href="route('logout')"
                                    method="post"
                                    as="button"
                                    class="flex w-full items-center gap-2.5 rounded-lg px-3 py-2 text-left text-sm text-neutral-700 transition-all duration-200 hover:bg-neutral-50 hover:text-neutral-900"
                                    @click="userMenuAbierto = false"
                                >
                                    <LogOut :size="15" class="text-purple-600" />
                                    Cerrar sesión
                                </Link>
                            </div>
                        </div>
                    </Transition>
                </div>
            </div>
        </header>

        <!-- NAVBAR MOBILE (blanco) -->
        <header class="sticky top-0 z-30 flex items-center justify-between border-b border-neutral-200/80 bg-white/90 px-4 py-3 backdrop-blur-md lg:hidden">
            <Link :href="route('dashboard')" class="flex items-center gap-2">
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-gradient-to-br from-purple-500 via-purple-700 to-neutral-950">
                    <LogoIcon :size="15" class="text-white" stroke-width="2.25" />
                </div>
                <span class="text-sm font-semibold tracking-tight text-neutral-900">Control de Gastos</span>
            </Link>

            <button
                class="rounded-lg p-1.5 text-neutral-700 transition-all duration-200 hover:bg-neutral-100"
                @click="menuAbierto = true"
            >
                <Menu :size="22" />
            </button>
        </header>

        <!-- MOBILE DRAWER (blanco) -->
        <Transition
            enter-active-class="transition ease-out duration-200"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition ease-in duration-150"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="menuAbierto"
                class="fixed inset-0 z-40 bg-neutral-950/30 backdrop-blur-[2px] lg:hidden"
                @click="menuAbierto = false"
            />
        </Transition>

        <Transition
            enter-active-class="transition ease-out duration-200"
            enter-from-class="translate-x-full"
            enter-to-class="translate-x-0"
            leave-active-class="transition ease-in duration-150"
            leave-from-class="translate-x-0"
            leave-to-class="translate-x-full"
        >
            <aside
                v-if="menuAbierto"
                class="fixed inset-y-0 right-0 z-50 flex w-72 flex-col bg-white lg:hidden"
            >
                <div class="flex items-center justify-between border-b border-neutral-100 px-5 py-4">
                    <div class="flex items-center gap-2">
                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-gradient-to-br from-purple-500 via-purple-700 to-neutral-950">
                            <LogoIcon :size="15" class="text-white" stroke-width="2.25" />
                        </div>
                        <span class="text-sm font-semibold text-neutral-900">Menú</span>
                    </div>
                    <button
                        class="rounded-lg p-1 text-neutral-500 transition-all duration-200 hover:bg-neutral-100 hover:text-neutral-900"
                        @click="menuAbierto = false"
                    >
                        <X :size="20" />
                    </button>
                </div>

                <nav class="flex-1 space-y-1 overflow-y-auto p-3">
                    <Link
                        v-for="item in NAV"
                        :key="item.key"
                        :href="route(item.route)"
                        class="group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold transition-all duration-300"
                        :class="esActivo(item.route)
                            ? 'bg-neutral-950 text-white shadow-md shadow-purple-900/30'
                            : 'text-neutral-700 hover:translate-x-1 hover:bg-purple-50 hover:text-purple-800'"
                        @click="menuAbierto = false"
                    >
                        <component
                            :is="item.icon"
                            :size="18"
                            class="transition-transform duration-300 group-hover:scale-110"
                            :class="esActivo(item.route) ? 'text-purple-400' : 'text-neutral-400 group-hover:text-purple-600'"
                        />
                        {{ item.label }}
                    </Link>
                </nav>

                <div class="border-t border-neutral-100 p-4">
                    <div class="mb-2 flex items-center gap-3 rounded-lg border border-neutral-200 bg-neutral-50 px-3 py-2.5">
                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-purple-500 to-neutral-950 text-xs font-bold text-white">
                            {{ iniciales }}
                        </div>
                        <div class="min-w-0">
                            <div class="truncate text-sm font-semibold text-neutral-900">
                                {{ page.props.auth.user.name }}
                            </div>
                            <div class="truncate text-[11px] text-neutral-500">
                                {{ page.props.auth.user.email }}
                            </div>
                        </div>
                    </div>
                    <Link
                        v-if="!page.props.auth.autoLogin"
                        :href="route('logout')"
                        method="post"
                        as="button"
                        class="flex w-full items-center gap-2.5 rounded-xl px-3 py-2 text-left text-sm font-semibold text-neutral-700 transition-all duration-200 hover:bg-neutral-50 hover:text-neutral-900"
                    >
                        <LogOut :size="15" />
                        Cerrar sesión
                    </Link>
                </div>
            </aside>
        </Transition>

        <!-- CONTENIDO -->
        <main class="mx-auto w-full max-w-[1600px] px-4 py-6 sm:px-6 lg:px-8 lg:py-8">
            <div v-if="title" class="mb-6 animate-fade-in">
                <h1 class="text-gradient text-2xl font-bold tracking-tight lg:text-3xl">
                    {{ title }}
                </h1>
                <p v-if="subtitle" class="mt-0.5 text-sm text-neutral-500">{{ subtitle }}</p>
            </div>
            <div class="animate-fade-in">
                <slot />
            </div>
        </main>

        <Alertas />
    </div>
</template>